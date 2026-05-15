<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use App\Models\Attendance;
use App\Models\Employee;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use Filament\Tables\Enums\FiltersLayout;

class LaporanAbsensi extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'Laporan';
    protected static ?string $title = 'Laporan Absensi';
    protected static string $view = 'filament.pages.laporan-absensi';

    public function getSubheading(): ?string
    {
        return 'Lihat dan export laporan absensi berdasarkan periode';
    }

    public static function canAccess(): bool
    {
        return in_array(auth()->user()->role, ['admin', 'manager']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Attendance::query())
            ->columns([
                TextColumn::make('rowIndex')
                    ->label('No')
                    ->rowIndex(),
                    
                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('l, d F Y') 
                    ->sortable(),
                    
                TextColumn::make('employee.name')
                    ->label('Nama')
                    ->searchable(),
                    
                TextColumn::make('employee.department')
                    ->label('Role (Bagian)')
                    ->badge(),
                    
                TextColumn::make('time_in')
                    ->label('Jam Masuk')
                    ->time('H:i:s'),
                    
                TextColumn::make('time_out')
                    ->label('Jam Keluar')
                    ->time('H:i:s')
                    ->default('-'),
                    
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Hadir' => 'success',
                        'Terlambat' => 'warning',
                        'Di Luar Area' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('date', 'desc')
            
            ->filters([
                Filter::make('tanggal')
                    ->form([
                        DatePicker::make('dari_tanggal')->label('Tanggal Mulai'),
                        DatePicker::make('sampai_tanggal')->label('Tanggal Akhir'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    })
                    ->columns(2),

                // ==========================================
                // FILTER ROLE / BAGIAN (DINAMIS DARI DATA KARYAWAN)
                // ==========================================
                SelectFilter::make('department')
                    ->label('Pilih Role / Bagian')
                    ->options(function () {
                        // Mengambil data departemen dari tabel karyawan secara otomatis
                        return Employee::whereNotNull('department')
                            ->where('department', '!=', '')
                            ->distinct()
                            ->pluck('department', 'department')
                            ->toArray();
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $value): Builder => $query->whereHas(
                                'employee',
                                fn (Builder $query) => $query->where('department', $value)
                            )
                        );
                    }),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            
            ->headerActions([
                ExportAction::make()
                    ->label('Export Data')
                    ->color('success')
                    ->icon('heroicon-o-arrow-down-tray'),
            ]);
    }
}