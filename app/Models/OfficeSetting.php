<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfficeSetting extends Model
{
    // Menggunakan fillable untuk mendaftarkan kolom yang diizinkan
    protected $fillable = [
        'latitude',
        'longitude',
        'radius',
        'shift1_start', // <-- Tambahan untuk jadwal masuk Shift 1
        'shift2_start', // <-- Tambahan untuk jadwal masuk Shift 2
    ];
}