<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeaveRequestResource\Pages;
use App\Filament\Resources\LeaveRequestResource\RelationManagers;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model; // Import tambahan untuk Hak Akses

use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\TextColumn;

// Import untuk logika validasi
use Closure;
use Illuminate\Support\Carbon;

class LeaveRequestResource extends Resource
{
    protected static ?string $model = LeaveRequest::class;

    // Ikon kalender untuk merepresentasikan jadwal/cuti
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days'; 

    // ==========================================
    // TAMBAHAN UNTUK TRANSLASI BAHASA INDONESIA
    // ==========================================
    protected static ?string $navigationLabel = 'Pengajuan Cuti / Izin';
    protected static ?string $modelLabel = 'Cuti / Izin';
    protected static ?string $pluralModelLabel = 'Data Pengajuan Cuti';
    // ==========================================

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('employee_id')
                    ->relationship('employee', 'name')
                    ->label('Nama Karyawan')
                    ->required(),
                
                Select::make('leave_type_id')
                    ->relationship('leaveType', 'name')
                    ->required()
                    ->label('Pilih Jenis Cuti / Izin'),
                
                DatePicker::make('start_date')
                    ->label('Tanggal Mulai')
                    ->required(),
                
                DatePicker::make('end_date')
                    ->label('Tanggal Selesai')
                    ->required()
                    // ==========================================
                    // LOGIKA PINTAR CEK SISA KUOTA CUTI
                    // ==========================================
                    ->rules([
                        function (callable $get) {
                            return function (string $attribute, $value, Closure $fail) use ($get) {
                                $employeeId = $get('employee_id'); 
                                $leaveTypeId = $get('leave_type_id');
                                $startDate = $get('start_date');
                                $endDate = $value;

                                // Pastikan semua data sudah diisi sebelum menghitung
                                if (!$employeeId || !$leaveTypeId || !$startDate || !$endDate) return;

                                $leaveType = LeaveType::find($leaveTypeId);
                                
                                // Jika tidak ada batas kuota (Izin Sakit), biarkan lolos
                                if (!$leaveType || $leaveType->quota === null) return; 

                                // Fungsi untuk menghitung total hari pengajuan (Senin-Sabtu masuk, Minggu libur)
                                $calculateWorkingDays = function($start, $end) {
                                    $days = 0;
                                    $current = Carbon::parse($start);
                                    $endDateObj = Carbon::parse($end);
                                    
                                    while ($current->lte($endDateObj)) {
                                        // Lewati hanya hari Minggu
                                        if (!$current->isSunday()) {
                                            $days++;
                                        }
                                        $current->addDay();
                                    }
                                    return $days;
                                };

                                // 1. Hitung berapa hari kerja yang sedang diajukan saat ini
                                $daysRequested = $calculateWorkingDays($startDate, $endDate);

                                // 2. Hitung berapa hari jenis cuti ini sudah dipakai di tahun ini
                                $usedDays = LeaveRequest::where('employee_id', $employeeId)
                                    ->where('leave_type_id', $leaveTypeId)
                                    ->whereYear('start_date', Carbon::now()->year)
                                    // Menggunakan 'Menunggu' sesuai dengan opsi dropdown status form Anda
                                    ->whereIn('status', ['Menunggu', 'Disetujui']) 
                                    ->get()
                                    ->sum(function($req) use ($calculateWorkingDays) {
                                        return $calculateWorkingDays($req->start_date, $req->end_date);
                                    });

                                // 3. Logika Penolakan
                                if (($usedDays + $daysRequested) > $leaveType->quota) {
                                    $sisa = $leaveType->quota - $usedDays;
                                    $sisa = $sisa < 0 ? 0 : $sisa; // Hindari angka minus
                                    
                                    $fail("Gagal! Sisa jatah '{$leaveType->name}' karyawan ini di tahun ini hanya tinggal {$sisa} hari. Anda mencoba mengajukan {$daysRequested} hari kerja.");
                                }
                            };
                        }
                    ]),
                
                Textarea::make('reason')
                    ->label('Alasan')
                    ->required(),
                
                FileUpload::make('document_path')
                    ->directory('leave-documents')
                    ->label('Dokumen Lampiran (Opsional)'),
                
                Select::make('status')
                    ->options([
                        'Menunggu' => 'Menunggu', 
                        'Disetujui' => 'Disetujui', 
                        'Ditolak' => 'Ditolak',
                    ])
                    ->default('Menunggu')
                    ->label('Status')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.name')
                    ->label('Nama Karyawan')
                    ->searchable()
                    ->sortable(),
                
                // Diubah menggunakan data relasi dari leaveType.name
                TextColumn::make('leaveType.name')
                    ->label('Jenis')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('start_date')
                    ->label('Mulai')
                    ->date('d M Y'),

                TextColumn::make('end_date')
                    ->label('Selesai')
                    ->date('d M Y'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Menunggu' => 'warning',
                        'Disetujui' => 'success',
                        'Ditolak' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Action untuk melihat lampiran surat
                Tables\Actions\Action::make('Lihat Surat')
                    ->icon('heroicon-o-document')
                    ->url(fn (LeaveRequest $record) => asset('storage/' . $record->document_path))
                    ->openUrlInNewTab()
                    ->visible(fn (LeaveRequest $record) => $record->document_path !== null),

                // Action untuk menyetujui pengajuan
                Tables\Actions\Action::make('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (LeaveRequest $record) => $record->update(['status' => 'Disetujui']))
                    ->visible(fn (LeaveRequest $record) => $record->status === 'Menunggu'),

                // Action untuk menolak pengajuan
                Tables\Actions\Action::make('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (LeaveRequest $record) => $record->update(['status' => 'Ditolak']))
                    ->visible(fn (LeaveRequest $record) => $record->status === 'Menunggu'),
                    
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    // ==========================================
    // PEMBATASAN HAK AKSES (HANYA UNTUK ADMIN)
    // ==========================================
    public static function canCreate(): bool
    {
        return auth()->user()->role === 'admin';
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->role === 'admin';
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->role === 'admin';
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()->role === 'admin';
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeaveRequests::route('/'),
            'create' => Pages\CreateLeaveRequest::route('/create'),
            'edit' => Pages\EditLeaveRequest::route('/{record}/edit'),
        ];
    }
}