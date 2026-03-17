<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    // Mengizinkan mass assignment untuk semua kolom
    protected $guarded = [];

    /**
     * Relasi ke model Employee (Setiap data absensi dimiliki oleh satu karyawan)
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}