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
use Filament\View\PanelsRenderHook; // <-- Tambahan import Render Hook
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade; // <-- Tambahan import Blade
use Illuminate\View\Middleware\ShareErrorsFromSession;

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
                // PERUBAHAN: Spektrum warna 'gray' diganti ke skala Emerald
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
            ->brandLogo(asset('storage/ptpn4-logo.jpg'))
            ->brandLogoHeight('3rem')
            ->brandName('PKS Adolina')
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
            ->renderHook( // <-- TAMBAHAN KODE UNTUK TEKS DI BAWAH LOGO
                PanelsRenderHook::SIDEBAR_NAV_START,
                fn (): string => Blade::render('
                    <div class="text-center pb-4 border-b border-gray-200 dark:border-gray-700 mb-2">
                        <h2 class="text-md font-bold text-green-700 tracking-wider">ABSENSI PTPN IV</h2>
                        <p class="text-xs text-gray-500">Unit PKS Adolina</p>
                    </div>
                ')
            )
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