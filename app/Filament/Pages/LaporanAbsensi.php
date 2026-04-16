<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use App\Models\Attendance;
use App\Models\Employee; // Tambahan model Employee untuk ambil data jabatan/unit
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter; // Tambahan untuk filter Dropdown
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction; // Tombol Export Cerdas di atas tabel
use Filament\Tables;

class LaporanAbsensi extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'Laporan';
    protected static ?string $title = 'Laporan Rekap Absensi';
    protected static string $view = 'filament.pages.laporan-absensi';

    public static function canAccess(): bool
    {
        return in_array(auth()->user()->role, ['admin', 'manager']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Attendance::query())
            ->columns([
                ImageColumn::make('photo_in')->label('Foto')->circular(),
                TextColumn::make('employee.name')->label('Nama Karyawan')->searchable(),
                TextColumn::make('shift')->label('Shift')->badge(),
                TextColumn::make('date')->label('Tanggal')->date('d M Y')->sortable(),
                TextColumn::make('time_in')->label('Jam Masuk')->time('H:i'),
                TextColumn::make('time_out')->label('Jam Pulang')->time('H:i'),
                TextColumn::make('status')->label('Status')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Hadir' => 'success',
                        'Terlambat' => 'warning',
                        'Di Luar Area' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                
                // 1. FILTER BERDASARKAN SHIFT
                SelectFilter::make('shift')
                    ->label('Filter Shift')
                    ->options([
                        'Shift 1' => 'Shift 1 (Pagi)',
                        'Shift 2' => 'Shift 2 (Malam)',
                    ]),

                // 2. FILTER BERDASARKAN JABATAN (Otomatis ambil dari database Karyawan)
                SelectFilter::make('jabatan')
                    ->label('Filter Jabatan')
                    ->options(fn () => Employee::select('position')->distinct()->pluck('position', 'position')->toArray())
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $value): Builder => $query->whereHas(
                                'employee',
                                fn (Builder $query) => $query->where('position', $value)
                            )
                        );
                    }),

                // 3. FILTER BERDASARKAN BAGIAN / UNIT
                SelectFilter::make('department')
                    ->label('Filter Bagian / Unit')
                    ->options(fn () => Employee::select('department')->distinct()->pluck('department', 'department')->toArray())
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $value): Builder => $query->whereHas(
                                'employee',
                                fn (Builder $query) => $query->where('department', $value)
                            )
                        );
                    }),

                // 4. FILTER RENTANG WAKTU (Bisa untuk Harian, Mingguan, Bulanan, Tahunan)
                Filter::make('rentang_waktu')
                    ->form([
                        DatePicker::make('dari_tanggal')->label('Dari Tanggal'),
                        DatePicker::make('sampai_tanggal')->label('Sampai Tanggal'),
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
                    // Indikator teks di atas tabel agar HRD tahu filter apa yang sedang aktif
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['dari_tanggal'] ?? null) {
                            $indicators['dari'] = 'Mulai: ' . \Carbon\Carbon::parse($data['dari_tanggal'])->format('d M Y');
                        }
                        if ($data['sampai_tanggal'] ?? null) {
                            $indicators['sampai'] = 'Sampai: ' . \Carbon\Carbon::parse($data['sampai_tanggal'])->format('d M Y');
                        }
                        return $indicators;
                    })
            ])
            // TOMBOL EXPORT CERDAS (Otomatis download semua data yang sudah di-filter)
            ->headerActions([
                ExportAction::make()
                    ->label('Download Laporan Excel')
                    ->color('success')
                    ->icon('heroicon-o-document-arrow-down'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make()->label('Export Terpilih (Excel)'),
                ]),
            ]);
    }
}