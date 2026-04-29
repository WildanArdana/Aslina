<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class RealtimeClockWidget extends Widget
{
    protected static string $view = 'filament.widgets.realtime-clock-widget';
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 1; // Mengunci posisi paling atas
}