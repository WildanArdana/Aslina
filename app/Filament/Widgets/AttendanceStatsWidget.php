<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class AttendanceStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '5s';
    protected static ?int $sort = 2; // Mengunci posisi di bawah jam

    protected function getStats(): array
    {
        $hariIni = Carbon::today();

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
                ->color('info'), // Warna Biru
                
            Stat::make('Tepat Waktu', $tepatWaktu)
                ->description('Masuk sesuai jadwal')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'), // Warna Hijau
                
            Stat::make('Terlambat', $terlambat)
                ->description('Melewati jam masuk')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('danger'), // Warna Merah
                
            Stat::make('Sudah Pulang', $sudahPulang)
                ->description('Telah melakukan absen pulang')
                ->descriptionIcon('heroicon-m-arrow-right-on-rectangle')
                ->color('success'), // Warna Hijau
        ];
    }
}