<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeaveRequestResource\Pages;
use App\Filament\Resources\LeaveRequestResource\RelationManagers;
use App\Models\LeaveRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\TextColumn;

class LeaveRequestResource extends Resource
{
    protected static ?string $model = LeaveRequest::class;

    // Ikon kalender untuk merepresentasikan jadwal/cuti
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days'; 

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('employee_id')
                    ->relationship('employee', 'name')
                    ->label('Nama Karyawan')
                    ->required(),
                Select::make('type')
                    ->options([
                        'Sakit' => 'Sakit', 
                        'Izin' => 'Izin', 
                        'Cuti' => 'Cuti Tahunan',
                    ])
                    ->label('Jenis Pengajuan')
                    ->required(),
                DatePicker::make('start_date')
                    ->label('Tanggal Mulai')
                    ->required(),
                DatePicker::make('end_date')
                    ->label('Tanggal Selesai')
                    ->required(),
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
                
                TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Sakit' => 'danger',
                        'Izin' => 'warning',
                        'Cuti' => 'success',
                        default => 'gray',
                    }),

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
                // Bisa tambahkan filter berdasarkan status di sini nanti
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