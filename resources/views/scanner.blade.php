<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi PKS Adolina</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">

    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md text-center">
        <h2 class="text-2xl font-bold mb-2">Sistem Absensi</h2>
        <p class="text-gray-500 mb-6">PT Perkebunan Nusantara IV PKS Adolina</p>

        <!-- ========================================== -->
        <!-- BAGIAN PILIHAN SHIFT (SUDAH DIPERBARUI) -->
        <!-- ========================================== -->
        <div class="mb-4 text-left">
            <label class="block text-gray-700 text-sm font-bold mb-2">Pilih Shift Kerja <span class="text-red-500">*</span></label>
            <select id="shift-select" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="" disabled selected>-- Pilih Shift / Bagian --</option>
                <option value="Shift 1">Shift 1 (Pagi)</option>
                <option value="Shift 2">Shift 2 (Malam)</option>
                <option value="Staf Kantor">Staf Kantor (Non-Shift)</option>
            </select>
        </div>

        <div id="reader" width="600px" class="mb-4 rounded-lg overflow-hidden"></div>
        
        <p id="location-status" class="text-sm text-yellow-600 font-semibold mb-4">Mencari lokasi GPS...</p>

        <div class="mt-6 pt-4 border-t border-gray-200">
            <p class="text-sm text-gray-500 mb-3">Tidak bisa hadir hari ini?</p>
            <a href="{{ route('leave.index') }}" class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                Ajukan Izin / Sakit / Cuti
            </a>
        </div>
        
    </div>

    <script>
        let currentLat = null;
        let currentLong = null;
        
        // Gunakan Html5Qrcode biasa (tanpa UI Scanner bawaan)
        const html5QrCode = new Html5Qrcode("reader"); 

        // 1. Dapatkan Lokasi GPS Pengguna
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    currentLat = position.coords.latitude;
                    currentLong = position.coords.longitude;
                    document.getElementById('location-status').innerText = "Lokasi GPS Terkunci. Membuka kamera...";
                    document.getElementById('location-status').className = "text-sm text-green-600 font-semibold mb-4";
                    
                    // Panggil fungsi kamera setelah GPS berhasil didapat
                    mulaiScanner(); 
                },
                function(error) {
                    Swal.fire('Error', 'Gagal mendapatkan lokasi GPS. Pastikan izin lokasi (Location) diaktifkan di browser Anda.', 'error');
                },
                { enableHighAccuracy: true } // Memaksa akurasi GPS tinggi
            );
        } else {
            Swal.fire('Error', 'Browser Anda tidak mendukung Geolocation.', 'error');
        }

        // 2. Fungsi Memulai Kamera Otomatis
        function mulaiScanner() {
            function onScanSuccess(decodedText, decodedResult) {
                
                // 1. Ambil pilihan shift
                let selectedShift = document.getElementById('shift-select').value;

                // Validasi agar karyawan memilih shift terlebih dahulu
                if (!selectedShift) {
                    Swal.fire('Peringatan', 'Silakan pilih Shift / Bagian Anda terlebih dahulu sebelum scan QR!', 'warning');
                    return; // Hentikan proses jika belum memilih
                }

                // 2. Ambil jepretan foto dari kamera (Selfie otomatis)
                let video = document.querySelector('#reader video');
                let canvas = document.createElement('canvas');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                // Menggambar frame video ke dalam canvas
                canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height); 
                // Konversi ke base64 dengan kualitas 50%
                let photoBase64 = canvas.toDataURL('image/jpeg', 0.5); 

                // Hentikan kamera sementara agar tidak scan berulang kali
                html5QrCode.stop().then(() => {
                    
                    // Tampilkan loading SweetAlert
                    Swal.fire({
                        title: 'Memproses Absensi...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading() }
                    });

                    // 3. Kirim data ke Backend Laravel
                    fetch("{{ route('scan.process') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}" 
                        },
                        body: JSON.stringify({
                            uid: decodedText,
                            latitude: currentLat,
                            longitude: currentLong,
                            shift: selectedShift,      // Data shift baru
                            photo: photoBase64         // Data foto baru
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.status === 'success') {
                            Swal.fire('Berhasil!', data.message, 'success').then(() => { location.reload() });
                        } else if(data.status === 'warning') {
                            Swal.fire('Peringatan', data.message, 'warning').then(() => { location.reload() });
                        } else {
                            Swal.fire('Gagal!', data.message, 'error').then(() => { location.reload() });
                        }
                    })
                    .catch(error => {
                        console.error('Error API:', error);
                        Swal.fire('Error', 'Terjadi kesalahan sistem atau server mati.', 'error').then(() => { location.reload() });
                    });
                }).catch(err => {
                    console.error("Gagal menghentikan kamera:", err);
                });
            }

            // Jalankan kamera. 
            // Catatan: "environment" untuk kamera belakang. Jika ingin kamera depan (untuk selfie), ubah menjadi "user".
            html5QrCode.start(
                { facingMode: "environment" }, 
                { fps: 10, qrbox: { width: 250, height: 250 } },
                onScanSuccess
            ).catch((err) => {
                console.error("Gagal memulai kamera:", err);
                document.getElementById('location-status').innerText = "Gagal mengakses kamera. Berikan izin kamera di browser.";
                document.getElementById('location-status').className = "text-sm text-red-600 font-semibold mb-4";
            });
        }
    </script>
</body>
</html>