<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Izin & Cuti - PKS Adolina</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">

    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-lg">
        <h2 class="text-2xl font-bold mb-2 text-center">Form Pengajuan</h2>
        <p class="text-gray-500 mb-6 text-center text-sm">Cuti / Izin / Sakit Karyawan PKS Adolina</p>

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                <p>{{ session('success') }}</p>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
                <ul class="list-disc pl-5 text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('leave.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">UID Karyawan (Sesuai ID Card)</label>
                <input type="text" name="uid" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="Contoh: EMP-65d1a2" required>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Jenis Pengajuan</label>
                <select name="type" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" required>
                    <option value="" disabled selected>Pilih Jenis...</option>
                    <option value="Sakit">Sakit</option>
                    <option value="Izin">Izin Keperluan Pribadi</option>
                    <option value="Cuti">Cuti Tahunan</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Tanggal Mulai</label>
                    <input type="date" name="start_date" class="w-full px-3 py-2 border rounded-lg focus:outline-none" required>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Tanggal Selesai</label>
                    <input type="date" name="end_date" class="w-full px-3 py-2 border rounded-lg focus:outline-none" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Alasan</label>
                <textarea name="reason" rows="3" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="Tuliskan alasan secara singkat..." required></textarea>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Lampiran Dokumen (Opsional)</label>
                <p class="text-xs text-gray-500 mb-2">Wajib untuk pengajuan Sakit (Surat Dokter). Format: JPG, PNG, PDF max 2MB.</p>
                <input type="file" name="document" accept=".jpg,.jpeg,.png,.pdf" class="w-full px-3 py-2 border rounded-lg bg-gray-50">
            </div>

            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200">
                Kirim Pengajuan
            </button>
        </form>
        
        <div class="mt-4 text-center">
            <a href="/" class="text-sm text-green-600 hover:underline">Kembali ke Halaman Absensi</a>
        </div>
    </div>

</body>
</html>