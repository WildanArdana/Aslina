<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Gunakan Faker bahasa Indonesia
        $faker = Faker::create('id_ID'); 
        
        $filePath = storage_path('app/Data absensi.xlsx - Sheet1.csv');
        
        if (!file_exists($filePath)) {
            $this->command->error("File CSV tidak ditemukan!");
            return;
        }

        $file = fopen($filePath, 'r');

        // Lewati baris pertama (Header Excel)
        fgets($file);

        $noUrut = 1; // Kita buat nomor urut otomatis dari sistem mulai dari 1

        while (($line = fgets($file)) !== false) {
            
            $line = trim($line);
            if (empty($line)) continue;

            $delimiter = strpos($line, ';') !== false ? ';' : ',';
            $data = str_getcsv($line, $delimiter);

            if (count($data) < 3) continue;
            
            Employee::create([
                // Membuat ID unik otomatis (EMP-0001, EMP-0002, dst)
                'uid' => 'EMP-' . str_pad($noUrut, 4, '0', STR_PAD_LEFT), 
                
                // Membuat nama palsu secara acak (Misal: Karyawan Budi)
                'name' => 'Karyawan ' . $faker->firstName, 
                
                // Menyesuaikan dengan letak asli di Excel milikmu
                'position' => trim($data[2]),   // Kolom ke-3: Jabatan (KRANI PRODUKSI)
                'department' => trim($data[1]), // Kolom ke-2: Bagian (KANTOR PENGOLAHAN)
            ]);

            $noUrut++; // Tambahkan 1 untuk baris berikutnya (EMP-0002, dst)
        }
        
        fclose($file);
        
        $this->command->info("Hore! Data CSV Karyawan berhasil di-import!");
    }
}