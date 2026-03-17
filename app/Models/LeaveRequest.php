<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    // Mengizinkan mass assignment untuk semua kolom
    protected $guarded = [];

    /**
     * Relasi ke model Employee (Setiap pengajuan cuti/izin dimiliki oleh satu karyawan)
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}