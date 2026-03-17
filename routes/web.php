<?php

use Illuminate\Support\Facades\Route;

// Wajib tambahkan ini di bagian atas agar Laravel tahu letak Controller dan Modelnya
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LeaveRequestController;
use App\Models\Employee;

Route::get('/', function () {
    return view('scanner');
});

// Route Absensi
Route::post('/api/scan', [AttendanceController::class, 'scan'])->name('scan.process');

// Route Pengajuan Cuti / Izin
Route::get('/pengajuan-izin', [LeaveRequestController::class, 'index'])->name('leave.index');
Route::post('/pengajuan-izin', [LeaveRequestController::class, 'store'])->name('leave.store');

// Route untuk mencetak ID Card Karyawan (Lengkap dengan QR Code)
Route::get('/employee/print-id/{id}', function ($id) {
    // findOrFail akan otomatis menampilkan halaman 404 jika data tidak ditemukan, 
    // sehingga tidak akan memunculkan error "null" lagi
    $employee = Employee::findOrFail($id);
    
    return view('id-card', compact('employee'));
})->name('employee.print');