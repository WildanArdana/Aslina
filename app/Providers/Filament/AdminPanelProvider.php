<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\RealtimeClockWidget; // <-- Tambahan import Widget Jam
use App\Filament\Widgets\AttendanceStatsWidget; 
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Support\HtmlString; // <-- Import HtmlString
use Filament\View\PanelsRenderHook; // <-- Import PanelsRenderHook
use Illuminate\Support\Facades\Blade; // <-- Import Blade

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::hex('#2E7D32'),
                'danger'  => Color::Rose,
                // Spektrum warna 'gray' diganti ke skala Emerald
                // untuk memberikan efek background hijau pada tema terang
                'gray'    => [
                    '50' => '#f0fdf4', 
                    '100' => '#dcfce7', 
                    '200' => '#bbf7d0',
                    '300' => '#86efac',
                    '400' => '#4ade80',
                    '500' => '#22c55e', 
                    '600' => '#16a34a',
                    '700' => '#15803d',
                    '800' => '#166534',
                    '900' => '#14532d',
                    '950' => '#052e16',
                ],
                'info'    => Color::Blue,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
            ])
            // KODE LOGO (Teks Berwarna Putih & Logo diberi background putih)
            ->brandName(new HtmlString('
                <div class="flex items-center gap-3 -ml-2">
                    <img src="' . asset('storage/ptpn4-logo.jpg') . '" alt="Logo PTPN IV" class="h-10 w-auto bg-white p-1 rounded-md">
                    <div class="flex flex-col text-left">
                        <span class="text-sm font-bold text-white tracking-wider leading-tight">ABSENSI PTPN IV</span>
                        <span class="text-xs text-green-100 font-normal mt-0.5">Unit PKS Adolina</span>
                    </div>
                </div>
            '))
            
            // KODE CSS PENYEMPURNAAN (Menu Aktif menjadi Putih)
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => Blade::render('
                    <style>
                        /* Mewarnai Sidebar Kiri menjadi Hijau Tua */
                        aside.fi-sidebar {
                            background-color: #15803d !important;
                            border-right: none !important;
                        }
                        
                        /* ========================================== */
                        /* KONDISI NORMAL: Teks & Ikon Putih          */
                        /* ========================================== */
                        aside.fi-sidebar .fi-sidebar-item-label,
                        aside.fi-sidebar .fi-sidebar-item-icon {
                            color: #ffffff !important;
                        }
                        
                        /* Efek Hover (Saat kursor di atas menu) */
                        aside.fi-sidebar .fi-sidebar-item-button:hover {
                            background-color: rgba(255, 255, 255, 0.15) !important;
                        }

                        /* ========================================== */
                        /* KONDISI AKTIF: Latar Putih & Teks Hijau    */
                        /* ========================================== */
                        aside.fi-sidebar .fi-sidebar-item-active > a,
                        aside.fi-sidebar .fi-sidebar-item-active button {
                            background-color: #ffffff !important;
                            border-radius: 0.5rem !important; /* Membuat sudutnya membulat rapi */
                        }
                        
                        aside.fi-sidebar .fi-sidebar-item-active .fi-sidebar-item-label,
                        aside.fi-sidebar .fi-sidebar-item-active .fi-sidebar-item-icon {
                            color: #15803d !important; /* Teks berubah hijau agar kontras */
                            font-weight: 700 !important;
                        }

                        /* ========================================== */
                        /* AREA KANAN: Konten & Topbar Putih Bersih   */
                        /* ========================================== */
                        main.fi-main {
                            background-color: #ffffff !important;
                        }
                        
                        header.fi-topbar {
                            background-color: #ffffff !important;
                            border-bottom: 1px solid #f3f4f6 !important;
                        }
                    </style>
                ')
            )
            ->font('Poppins')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Dimatikan agar kotak Welcome hilang dari dashboard
                // Widgets\AccountWidget::class, 
                
                RealtimeClockWidget::class,   // <-- Widget Jam
                AttendanceStatsWidget::class, // <-- Widget 4 Kotak Statistik
                
                // Baris di bawah ini dimatikan agar logo Filament di Dashboard kanan hilang
                // Widgets\FilamentInfoWidget::class, 
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}