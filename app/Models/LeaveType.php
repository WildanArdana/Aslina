<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    /**
     * Kolom yang dapat diisi secara massal (mass assignment).
     */
    protected $fillable = [
        'name', 
        'quota'
    ];

    /**
     * Relasi: Satu jenis cuti bisa dipakai oleh banyak pengajuan cuti.
     * (One-to-Many)
     */
    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }
}