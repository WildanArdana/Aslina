<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use App\Models\Employee;

class LeaveRequestController extends Controller
{
    // Menampilkan halaman form pengajuan
    public function index()
    {
        return view('leave-request');
    }

    // Menyimpan data pengajuan dari karyawan
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'uid' => 'required|exists:employees,uid',
            'type' => 'required|in:Sakit,Izin,Cuti',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
            'document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048', // Maksimal 2MB
        ], [
            'uid.exists' => 'UID Karyawan tidak terdaftar di sistem.',
            'end_date.after_or_equal' => 'Tanggal selesai tidak boleh lebih cepat dari tanggal mulai.'
        ]);

        // Cari data karyawan berdasarkan UID (ID dari QR Code)
        $employee = Employee::where('uid', $request->uid)->first();

        // Proses upload dokumen jika ada (misal: Surat Keterangan Dokter)
        $documentPath = null;
        if ($request->hasFile('document')) {
            // Simpan di storage/app/public/leave-documents
            $documentPath = $request->file('document')->store('leave-documents', 'public');
        }

        // Simpan ke database
        LeaveRequest::create([
            'employee_id' => $employee->id,
            'type' => $request->type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'document_path' => $documentPath,
            'status' => 'Menunggu', // Default status saat baru diajukan
        ]);

        // Redirect kembali dengan pesan sukses
        return back()->with('success', 'Pengajuan ' . $request->type . ' berhasil dikirim dan sedang menunggu persetujuan HRD.');
    }
}