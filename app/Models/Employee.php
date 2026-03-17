<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    // Mengizinkan mass assignment untuk semua kolom
    protected $guarded = [];

    /**
     * Relasi ke model Attendance (Satu karyawan memiliki banyak absensi)
     */
    public function attendances() 
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Relasi ke model LeaveRequest (Satu karyawan memiliki banyak pengajuan cuti/izin)
     * (Opsional: Saya tambahkan relasi ini karena Anda juga membuat tabel leave_requests sebelumnya)
     */
    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }
}