<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\OfficeSetting; 
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function scan(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'uid' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'shift' => 'nullable|string',
            'photo' => 'nullable|string' // Berupa base64 string
        ]);

        // 2. Ambil Pengaturan dari Database (Sekaligus untuk Geofencing dan Shift)
        $setting = OfficeSetting::first();
        if (!$setting) {
            return response()->json(['status' => 'error', 'message' => 'Admin belum mengatur titik kordinat kantor dan shift!']);
        }

        // 3. Pecah UID jika digabung dengan nama
        $qrData = explode(' - ', $request->uid);
        $uidAsli = $qrData[0]; 

        $employee = Employee::where('uid', $uidAsli)->first();
        if (!$employee) {
            return response()->json(['status' => 'error', 'message' => 'QR Code tidak terdaftar!']);
        }

        // 4. Hitung Jarak Geofencing
        $distance = $this->calculateDistance($setting->latitude, $setting->longitude, $request->latitude, $request->longitude);
        if ($distance > $setting->radius) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Di luar jangkauan! Jarak Anda: ' . round($distance) . ' M dari batas ' . $setting->radius . ' M.'
            ]);
        }

        // 5. Inisialisasi Waktu (Zona Waktu WIB)
        $waktuSekarang = Carbon::now('Asia/Jakarta');
        $today = $waktuSekarang->toDateString(); // Tanggal hari ini
        $jamSekarang = $waktuSekarang->format('H:i:s'); // Jam saat ini

        // Cek absen hari ini
        $attendance = Attendance::where('employee_id', $employee->id)->where('date', $today)->first();

        if (!$attendance) {
            // ==========================================
            // JIKA BELUM ABSEN SAMA SEKALI -> ABSEN MASUK
            // ==========================================
            $statusKehadiran = 'Hadir'; // Default Tepat Waktu

            // 6. Logika Penentuan Terlambat (DINAMIS DARI DATABASE)
            if ($request->shift === 'Shift 1' && $setting->shift1_start) {
                // Tambah toleransi 15 menit dari jam yang disetting admin
                $batasShift1 = Carbon::parse($setting->shift1_start)->addMinutes(15)->format('H:i');
                
                if ($waktuSekarang->format('H:i') > $batasShift1) {
                    $statusKehadiran = 'Terlambat';
                }
            } elseif ($request->shift === 'Shift 2' && $setting->shift2_start) {
                // Tambah toleransi 15 menit dari jam yang disetting admin
                $batasShift2 = Carbon::parse($setting->shift2_start)->addMinutes(15)->format('H:i');
                
                if ($waktuSekarang->format('H:i') > $batasShift2) {
                    $statusKehadiran = 'Terlambat';
                }
            }

            // Simpan Data Absen Masuk
            Attendance::create([
                'employee_id' => $employee->id,
                'shift' => $request->shift,
                'date' => $today,
                'time_in' => $jamSekarang,
                'lat_in' => $request->latitude,
                'long_in' => $request->longitude,
                'photo_in' => $request->photo,
                'status' => $statusKehadiran // Otomatis terisi Hadir / Terlambat berdasarkan pengecekan dinamis
            ]);

            // Pesan respon menyesuaikan dengan status
            $pesan = $statusKehadiran == 'Terlambat' 
                ? 'Absen MASUK Berhasil, namun Anda Terlambat!' 
                : 'Absen MASUK Berhasil! Selamat bekerja.';

            return response()->json(['status' => 'success', 'message' => $pesan]);
        
        } elseif ($attendance && $attendance->time_out == null) {
            // ==========================================
            // JIKA SUDAH MASUK TAPI BELUM PULANG -> PULANG
            // ==========================================
            $attendance->update([
                'time_out' => $jamSekarang,
                'lat_out' => $request->latitude,
                'long_out' => $request->longitude,
            ]);

            return response()->json(['status' => 'success', 'message' => 'Absen PULANG Berhasil! Hati-hati di jalan.']);
        
        } else {
            // ==========================================
            // JIKA SUDAH ABSEN MASUK & PULANG
            // ==========================================
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