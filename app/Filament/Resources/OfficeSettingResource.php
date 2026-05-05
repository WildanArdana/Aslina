<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OfficeSettingResource\Pages;
use App\Filament\Resources\OfficeSettingResource\RelationManagers;
use App\Models\OfficeSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;

class OfficeSettingResource extends Resource
{
    protected static ?string $model = OfficeSetting::class;

    // Icon diganti menjadi map-pin agar sesuai dengan pengaturan Lokasi GPS
    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    // ==========================================
    // TRANSLASI BAHASA INDONESIA
    // ==========================================
    protected static ?string $navigationLabel = 'Pengaturan Kantor';
    protected static ?string $modelLabel = 'Pengaturan Kantor';
    protected static ?string $pluralModelLabel = 'Data Pengaturan Kantor';
    // ==========================================

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('latitude')
                    ->required()
                    ->label('Latitude Kantor'),
                TextInput::make('longitude')
                    ->required()
                    ->label('Longitude Kantor'),
                TextInput::make('radius')
                    ->required()
                    ->numeric()
                    ->label('Batas Radius (Meter)')
                    ->suffix('Meter'),
                    
                // ==========================================
                // PENGATURAN SHIFT MASUK & PULANG
                // ==========================================
                TimePicker::make('shift1_start')
                    ->required()
                    ->label('Jam Masuk Shift 1'),
                TimePicker::make('shift1_end') // <-- Tambahan Jam Pulang
                    ->required()
                    ->label('Jam Pulang Shift 1'),

                TimePicker::make('shift2_start')
                    ->required()
                    ->label('Jam Masuk Shift 2'),
                TimePicker::make('shift2_end') // <-- Tambahan Jam Pulang
                    ->required()
                    ->label('Jam Pulang Shift 2'),

                // ==========================================
                // PENGATURAN JAM KERJA STAF KANTOR (NON-SHIFT)
                // ==========================================
                TimePicker::make('office_start')
                    ->required()
                    ->label('Jam Masuk Staf Kantor (Non-Shift)'),
                TimePicker::make('office_end')
                    ->required()
                    ->label('Jam Pulang Staf Kantor (Non-Shift)'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('latitude')
                    ->label('Latitude')
                    ->toggleable(isToggledHiddenByDefault: true), // Disembunyikan agar tabel tidak terlalu penuh
                TextColumn::make('longitude')
                    ->label('Longitude')
                    ->toggleable(isToggledHiddenByDefault: true), // Disembunyikan agar tabel tidak terlalu penuh
                TextColumn::make('radius')
                    ->label('Batas Radius')
                    ->suffix(' M')
                    ->badge()
                    ->color('success'),
                    
                // ==========================================
                // TAMPILAN SHIFT DI TABEL
                // ==========================================
                TextColumn::make('shift1_start')
                    ->label('Masuk Shift 1')
                    ->time('H:i'), 
                TextColumn::make('shift1_end') // <-- Tambahan Jam Pulang di Tabel
                    ->label('Pulang Shift 1')
                    ->time('H:i')
                    ->color('danger'), // Diberi warna agar beda secara visual dengan jam masuk

                TextColumn::make('shift2_start')
                    ->label('Masuk Shift 2')
                    ->time('H:i'),
                TextColumn::make('shift2_end') // <-- Tambahan Jam Pulang di Tabel
                    ->label('Pulang Shift 2')
                    ->time('H:i')
                    ->color('danger'),

                // ==========================================
                // TAMPILAN JAM KERJA STAF KANTOR DI TABEL
                // ==========================================
                TextColumn::make('office_start')
                    ->label('Masuk Staf (Non-Shift)')
                    ->time('H:i'),
                TextColumn::make('office_end')
                    ->label('Pulang Staf (Non-Shift)')
                    ->time('H:i')
                    ->color('danger'),
            ])
            ->filters([
                //
            ])
            ->actions([
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
            'index' => Pages\ListOfficeSettings::route('/'),
            'create' => Pages\CreateOfficeSetting::route('/create'),
            'edit' => Pages\EditOfficeSetting::route('/{record}/edit'),
        ];
    }
}