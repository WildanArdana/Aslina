<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    /**
     * Mengizinkan mass assignment untuk semua kolom.
     * Dipertahankan menggunakan $guarded agar lebih praktis.
     */
    protected $guarded = [];

    /**
     * Relasi ke model Employee (Setiap pengajuan cuti/izin dimiliki oleh satu karyawan)
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Relasi ke model LeaveType (Setiap pengajuan cuti merujuk pada satu jenis cuti)
     */
    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }
}