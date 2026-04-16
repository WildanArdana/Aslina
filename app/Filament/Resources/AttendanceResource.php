<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Filament\Resources\AttendanceResource\RelationManagers;
use App\Models\Attendance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model; 

use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction; 

use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;

use Filament\Tables\Filters\Filter;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    // ==========================================
    // TAMBAHAN UNTUK TRANSLASI BAHASA INDONESIA
    // ==========================================
    protected static ?string $navigationLabel = 'Data Absensi';
    protected static ?string $modelLabel = 'Absensi';
    protected static ?string $pluralModelLabel = 'Data Absensi';
    // ==========================================

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('employee_id')
                    ->relationship('employee', 'name')
                    ->required()
                    ->label('Karyawan'),

                DatePicker::make('date')
                    ->required()
                    ->label('Tanggal'),

                TimePicker::make('time_in')
                    ->label('Jam Masuk'),

                TimePicker::make('time_out')
                    ->label('Jam Pulang'),

                Select::make('status')
                    ->options([
                        'Hadir' => 'Hadir',
                        'Terlambat' => 'Terlambat',
                        'Di Luar Area' => 'Di Luar Area',
                    ])
                    ->default('Hadir')
                    ->required()
                    ->label('Status'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo_in')
                    ->label('Selfie Masuk')
                    ->circular(),
                    
                TextColumn::make('employee.name')
                    ->label('Karyawan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('shift')
                    ->label('Shift')
                    ->badge(),

                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('time_in')
                    ->label('Masuk')
                    ->time('H:i')
                    ->sortable(),

                TextColumn::make('time_out')
                    ->label('Pulang')
                    ->time('H:i')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status') // Memastikan header tabel juga berbahasa Indonesia
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Hadir' => 'success',
                        'Terlambat' => 'warning',
                        'Di Luar Area' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('lat_in')
                    ->label('Latitude')
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('long_in')
                    ->label('Longitude')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('date')
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
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make()->label('Export ke Excel'), // Menambahkan label bahasa Indonesia untuk tombol export
                ]),
            ]);
    }

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
            'index' => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
            'view' => Pages\ViewAttendance::route('/{record}'),
            'edit' => Pages\EditAttendance::route('/{record}/edit'),
        ];
    }
}