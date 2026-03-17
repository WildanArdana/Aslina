<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ID Card - {{ $employee->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Pengaturan ukuran kertas khusus untuk ID Card (CR80 Standard) */
        @media print {
            body {
                background: white;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            @page {
                size: 54mm 86mm; /* Ukuran ID Card standar */
                margin: 0;
            }
            .no-print {
                display: none !important;
            }
            .card-container {
                box-shadow: none !important;
                border: none !important;
                margin: 0 !important;
            }
        }
        
        /* Font khusus untuk teks kecil agar tetap tajam */
        .text-tiny { font-size: 7px; }
        .text-small { font-size: 9px; }
        .text-medium { font-size: 10px; }
    </style>
</head>
<body class="bg-gray-200 flex items-center justify-center min-h-screen">

    <div class="fixed top-4 left-4 no-print flex gap-2">
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow transition">
            🖨️ Cetak ID Card
        </button>
        <button onclick="window.history.back()" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded shadow transition">
            Kembali
        </button>
    </div>

    <div class="card-container bg-white shadow-xl overflow-hidden relative" style="width: 54mm; height: 86mm; border: 1px solid #ccc;">
        
        <div class="bg-green-700 text-white text-center py-3">
            <h1 class="text-medium font-bold uppercase leading-tight">PT Perkebunan Nusantara IV</h1>
            <h2 class="text-small font-semibold">Regional II Unit PKS Adolina</h2>
        </div>

        <div class="mt-4 flex justify-center">
            @if($employee->photo)
                <img src="{{ asset('storage/' . $employee->photo) }}" 
                     class="w-20 h-24 object-cover border-2 border-green-700 shadow-sm rounded-sm" 
                     alt="Foto Karyawan">
            @else
                <div class="w-20 h-24 bg-gray-200 border-2 border-dashed border-gray-400 flex items-center justify-center text-[8px] text-gray-500">
                    <div class="text-center">
                        <span class="block text-lg">👤</span>
                        Tanpa Foto
                    </div>
                </div>
            @endif
        </div>

        <div class="text-center mt-3 px-2">
            <h3 class="text-sm font-bold text-gray-800 leading-tight">{{ $employee->name }}</h3>
            <p class="text-medium text-green-700 font-bold uppercase mt-1">{{ $employee->position }}</p>
            <p class="text-small text-gray-500 italic">{{ $employee->department }}</p>
        </div>

        <div class="absolute bottom-3 left-0 right-0 flex flex-col items-center">
            <div class="p-1.5 bg-white border border-gray-200 rounded-lg shadow-sm">
                {!! QrCode::size(65)->generate($employee->uid) !!}
            </div>
            <p class="text-tiny text-gray-400 mt-1 font-mono tracking-widest">{{ $employee->uid }}</p>
        </div>

        <div class="absolute bottom-0 w-full h-1 bg-green-700"></div>

    </div>

    <script>
        // Dialog print muncul otomatis 
        window.onload = function() {
            // Memberikan waktu loading aset gambar sebelum memicu print
            setTimeout(function() {
                window.print();
            }, 800);
        }
    </script>
</body>
</html>