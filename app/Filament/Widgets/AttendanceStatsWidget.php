<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class AttendanceStatsWidget extends BaseWidget
{
    // Mengatur agar widget otomatis *refresh* setiap 5 detik (Real-Time)
    protected static ?string $pollingInterval = '5s';

    protected function getStats(): array
    {
        $hariIni = Carbon::today();

        // Mengambil data absensi khusus HARI INI
        $totalAbsensi = Attendance::whereDate('date', $hariIni)->count();
        
        $tepatWaktu = Attendance::whereDate('date', $hariIni)
            ->where('status', 'Hadir')->count();
            
        $terlambat = Attendance::whereDate('date', $hariIni)
            ->where('status', 'Terlambat')->count();
            
        $sudahPulang = Attendance::whereDate('date', $hariIni)
            ->whereNotNull('time_out')->count();

        return [
            Stat::make('Total Absensi', $totalAbsensi)
                ->description('Kehadiran hari ini')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
                
            Stat::make('Tepat Waktu', $tepatWaktu)
                ->description('Masuk sesuai jadwal')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
                
            Stat::make('Terlambat', $terlambat)
                ->description('Melewati jam masuk')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('danger'),
                
            Stat::make('Sudah Pulang', $sudahPulang)
                ->description('Telah melakukan absen pulang')
                ->descriptionIcon('heroicon-m-arrow-right-on-rectangle')
                ->color('primary'),
        ];
    }
}