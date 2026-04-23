<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class RealtimeClockWidget extends Widget
{
    protected static string $view = 'filament.widgets.realtime-clock-widget';
    
    // Agar ukurannya full memanjang dari kiri ke kanan
    protected int | string | array $columnSpan = 'full';
    
    // Tambahkan baris ini agar posisinya di atas (diatur ke urutan 1)
    protected static ?int $sort = 1; 
}