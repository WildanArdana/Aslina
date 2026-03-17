<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = [
            [
                'uid' => uniqid('EMP-'),
                'name' => 'Aditya Pradana',
                'position' => 'Staff IT / System Developer',
                'department' => 'Teknologi Informasi',
            ],
            [
                'uid' => uniqid('EMP-'),
                'name' => 'Budi Santoso',
                'position' => 'Mandor Pengolahan',
                'department' => 'Produksi',
            ],
            [
                'uid' => uniqid('EMP-'),
                'name' => 'Siti Aminah',
                'position' => 'Staff Administrasi',
                'department' => 'HRD & Umum',
            ],
            [
                'uid' => uniqid('EMP-'),
                'name' => 'Hendro Siswanto',
                'position' => 'Operator Boiler',
                'department' => 'Teknik / Pabrik',
            ],
            [
                'uid' => uniqid('EMP-'),
                'name' => 'Agus Supriyadi',
                'position' => 'Komandan Regu (Danru)',
                'department' => 'Keamanan / Security',
            ],
        ];

        foreach ($employees as $emp) {
            Employee::create($emp);
        }
    }
}