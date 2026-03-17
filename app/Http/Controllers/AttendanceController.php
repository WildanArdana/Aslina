<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\OfficeSetting; // Ditambahkan untuk mengambil pengaturan kantor
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function scan(Request $request)
    {
        // Menambahkan shift dan photo ke dalam validasi (opsional jika divalidasi ketat)
        $request->validate([
            'uid' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'shift' => 'nullable|string',
            'photo' => 'nullable|string' // Berupa base64 string
        ]);

        // 1. Ambil Pengaturan dari Database (Bukan dari .env lagi)
        $setting = OfficeSetting::first();
        if (!$setting) {
            return response()->json(['status' => 'error', 'message' => 'Admin belum mengatur titik kordinat kantor!']);
        }

        // 2. Pecah UID jika digabung dengan nama
        $qrData = explode(' - ', $request->uid);
        $uidAsli = $qrData[0]; 

        $employee = Employee::where('uid', $uidAsli)->first();
        if (!$employee) {
            return response()->json(['status' => 'error', 'message' => 'QR Code tidak terdaftar!']);
        }

        // 3. Hitung Jarak Geofencing
        $distance = $this->calculateDistance($setting->latitude, $setting->longitude, $request->latitude, $request->longitude);
        if ($distance > $setting->radius) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Di luar jangkauan! Jarak Anda: ' . round($distance) . ' M dari batas ' . $setting->radius . ' M.'
            ]);
        }

        // 4. Logika Masuk atau Pulang
        $today = Carbon::now()->toDateString();
        $time = Carbon::now()->toTimeString();

        // Cek absen hari ini
        $attendance = Attendance::where('employee_id', $employee->id)->where('date', $today)->first();

        if (!$attendance) {
            // JIKA BELUM ABSEN SAMA SEKALI HARI INI -> ABSEN MASUK
            Attendance::create([
                'employee_id' => $employee->id,
                'shift' => $request->shift, // Menyimpan shift dari frontend
                'date' => $today,
                'time_in' => $time,
                'lat_in' => $request->latitude,
                'long_in' => $request->longitude,
                'photo_in' => $request->photo, // Menyimpan foto base64 sementara ke DB
                'status' => 'Hadir'
            ]);
            return response()->json(['status' => 'success', 'message' => 'Absen MASUK Berhasil! Selamat bekerja.']);
        
        } elseif ($attendance && $attendance->time_out == null) {
            // JIKA SUDAH ABSEN MASUK TAPI BELUM PULANG -> ABSEN PULANG
            $attendance->update([
                'time_out' => $time,
                'lat_out' => $request->latitude,
                'long_out' => $request->longitude,
            ]);
            return response()->json(['status' => 'success', 'message' => 'Absen PULANG Berhasil! Hati-hati di jalan.']);
        
        } else {
            // JIKA SUDAH ABSEN MASUK & PULANG
            return response()->json(['status' => 'warning', 'message' => 'Anda sudah melakukan absen masuk dan pulang hari ini.']);
        }
    }

    
    // Fungsi Haversine Formula untuk menghitung jarak antara 2 titik GPS dalam METER
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Radius bumi dalam meter

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c; // Hasil dalam meter
    }
}