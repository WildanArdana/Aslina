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
        // 1. Validasi Input dari Scanner
        $request->validate([
            'uid' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'shift' => 'nullable|string',
            'photo' => 'nullable|string' // Berupa base64 string
        ]);

        // 2. Ambil Pengaturan dari Database (Geofencing dan Jam Shift)
        $setting = OfficeSetting::first();
        if (!$setting) {
            return response()->json(['status' => 'error', 'message' => 'Admin belum mengatur titik kordinat kantor dan shift!']);
        }

        // 3. Pecah UID (Jika format QR adalah "UID - Nama")
        $qrData = explode(' - ', $request->uid);
        $uidAsli = $qrData[0]; 

        $employee = Employee::where('uid', $uidAsli)->first();
        if (!$employee) {
            return response()->json(['status' => 'error', 'message' => 'QR Code tidak terdaftar!']);
        }

        // 4. Hitung Jarak Geofencing (Radius Pabrik/Kantor)
        $distance = $this->calculateDistance($setting->latitude, $setting->longitude, $request->latitude, $request->longitude);
        if ($distance > $setting->radius) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Di luar jangkauan! Jarak Anda: ' . round($distance) . ' M dari batas ' . $setting->radius . ' M.'
            ]);
        }

        // 5. Inisialisasi Waktu (Zona Waktu WIB)
        $waktuSekarang = Carbon::now('Asia/Jakarta');
        $tanggalHariIni = $waktuSekarang->format('Y-m-d');
        $jamSekarang = $waktuSekarang->format('H:i:s');

        // 6. CEK APAKAH KARYAWAN SUDAH ABSEN MASUK HARI INI?
        $absensiHariIni = Attendance::where('employee_id', $employee->id)
                            ->whereDate('date', $tanggalHariIni)
                            ->first();

        if ($absensiHariIni) {
            // ==========================================
            // LOGIKA ABSEN PULANG
            // ==========================================

            // A. Cek apakah sudah pernah absen pulang? (Mencegah scan berkali-kali)
            if ($absensiHariIni->time_out !== null) {
                return response()->json(['status' => 'warning', 'message' => 'Anda sudah melakukan absen masuk dan pulang hari ini!']);
            }

            // B. ATURAN WAJIB: TIDAK BOLEH PULANG CEPAT (DINAMIS DARI ADMIN)
            if ($setting) {
                if ($absensiHariIni->shift === 'Shift 1' && $setting->shift1_end) {
                    $batasPulangShift1 = Carbon::parse($setting->shift1_end)->format('H:i:s');
                    
                    if ($jamSekarang < $batasPulangShift1) {
                        return response()->json(['status' => 'error', 'message' => 'Gagal Absen! Belum waktunya pulang untuk Shift 1. Jadwal pulang: ' . $batasPulangShift1]);
                    }
                } 
                elseif ($absensiHariIni->shift === 'Shift 2' && $setting->shift2_end) {
                    $batasPulangShift2 = Carbon::parse($setting->shift2_end)->format('H:i:s');
                    
                    if ($jamSekarang < $batasPulangShift2) {
                        return response()->json(['status' => 'error', 'message' => 'Gagal Absen! Belum waktunya pulang untuk Shift 2. Jadwal pulang: ' . $batasPulangShift2]);
                    }
                }
                // --- TAMBAHAN UNTUK STAF KANTOR (NON-SHIFT) ---
                elseif ($absensiHariIni->shift === 'Staf Kantor' && $setting->office_end) {
                    $batasPulangKantor = Carbon::parse($setting->office_end)->format('H:i:s');
                    
                    if ($jamSekarang < $batasPulangKantor) {
                        return response()->json(['status' => 'error', 'message' => 'Gagal Absen! Belum waktunya pulang untuk Staf Kantor. Jadwal pulang: ' . $batasPulangKantor]);
                    }
                }
            }

            // C. Jika lolos pengecekan di atas (sudah waktunya pulang), simpan datanya:
            $absensiHariIni->update([
                'time_out' => $jamSekarang,
                'lat_out' => $request->latitude,
                'long_out' => $request->longitude,
            ]);

            return response()->json(['status' => 'success', 'message' => 'Absen PULANG Berhasil! Hati-hati di jalan.']);

        } else {
            // ==========================================
            // LOGIKA ABSEN MASUK
            // ==========================================
            $statusKehadiran = 'Hadir'; // Default Tepat Waktu

            // Logika Keterlambatan Dinamis dari Database (Toleransi 15 Menit)
            if ($request->shift === 'Shift 1' && $setting->shift1_start) {
                $batasShift1 = Carbon::parse($setting->shift1_start)->addMinutes(15)->format('H:i:s');
                if ($jamSekarang > $batasShift1) {
                    $statusKehadiran = 'Terlambat';
                }
            } elseif ($request->shift === 'Shift 2' && $setting->shift2_start) {
                $batasShift2 = Carbon::parse($setting->shift2_start)->addMinutes(15)->format('H:i:s');
                if ($jamSekarang > $batasShift2) {
                    $statusKehadiran = 'Terlambat';
                }
            } 
            // --- TAMBAHAN UNTUK STAF KANTOR (NON-SHIFT) ---
            elseif ($request->shift === 'Staf Kantor' && $setting->office_start) {
                $batasKantor = Carbon::parse($setting->office_start)->addMinutes(15)->format('H:i:s');
                if ($jamSekarang > $batasKantor) {
                    $statusKehadiran = 'Terlambat';
                }
            }

            // Simpan Data Absen Masuk
            Attendance::create([
                'employee_id' => $employee->id,
                'shift' => $request->shift,
                'date' => $tanggalHariIni,
                'time_in' => $jamSekarang,
                'lat_in' => $request->latitude,
                'long_in' => $request->longitude,
                'photo_in' => $request->photo,
                'status' => $statusKehadiran 
            ]);

            // Pesan respon menyesuaikan dengan status Keterlambatan
            $pesan = $statusKehadiran === 'Terlambat' 
                ? 'Absen MASUK Berhasil, namun Anda Terlambat!' 
                : 'Absen MASUK Berhasil! Selamat bekerja.';

            return response()->json(['status' => 'success', 'message' => $pesan]);
        }
    }

    // ==========================================
    // FUNGSI PENGHITUNG JARAK (HAVERSINE FORMULA)
    // ==========================================
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Radius bumi dalam meter

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c; // Hasil akhir berbentuk meter
    }
}