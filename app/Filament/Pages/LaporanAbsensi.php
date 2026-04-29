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
use Filament\Tables\Enums\FiltersLayout; // Import ini wajib untuk mengeluarkan filter ke atas

class LaporanAbsensi extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'Laporan';
    protected static ?string $title = 'Laporan Absensi';
    protected static string $view = 'filament.pages.laporan-absensi';

    // Menambahkan Sub-judul (Teks abu-abu di bawah judul Laporan Absensi)
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
                // Kolom 1: Nomor Urut (Otomatis)
                TextColumn::make('rowIndex')
                    ->label('No')
                    ->rowIndex(),
                    
                // Kolom 2: Tanggal (Format: Sabtu, 6 Desember 2025)
                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('l, d F Y') 
                    ->sortable(),
                    
                // Kolom 3: Nama Karyawan
                TextColumn::make('employee.name')
                    ->label('Nama')
                    ->searchable(),
                    
                // Kolom 4: Role (Kita gunakan Bagian/Unit agar relevan dengan PKS Adolina)
                TextColumn::make('employee.department')
                    ->label('Role (Bagian)')
                    ->badge(),
                    
                // Kolom 5: Jam Masuk
                TextColumn::make('time_in')
                    ->label('Jam Masuk')
                    ->time('H:i:s'),
                    
                // Kolom 6: Jam Keluar / Pulang
                TextColumn::make('time_out')
                    ->label('Jam Keluar')
                    ->time('H:i:s')
                    ->default('-'), // Menampilkan strip jika belum pulang
                    
                // Kolom 7: Status Kehadiran
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
            
            // ==========================================
            // BAGIAN FILTER YANG DIKELUARKAN KE ATAS
            // ==========================================
            ->filters([
                // Gabungan Tanggal Mulai & Akhir
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
                    ->columns(2), // Membuat kedua tanggal ini bersebelahan

                // Filter Role (Menggunakan Bagian/Unit Karyawan)
                SelectFilter::make('department')
                    ->label('Pilih Role / Bagian')
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
            ], layout: FiltersLayout::AboveContent) // KUNCI UTAMA: Memindahkan filter ke atas tabel
            ->filtersFormColumns(3) // Membagi ruang atas menjadi 3 bagian yang rapi
            
            // ==========================================
            // TOMBOL EXPORT BERWARNA HIJAU DI KANAN ATAS
            // ==========================================
            ->headerActions([
                ExportAction::make()
                    ->label('Export Data')
                    ->color('success')
                    ->icon('heroicon-o-arrow-down-tray'),
            ]);
    }
}