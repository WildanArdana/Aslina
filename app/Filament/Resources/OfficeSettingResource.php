<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OfficeSettingResource\Pages;
use App\Filament\Resources\OfficeSettingResource\RelationManagers;
use App\Models\OfficeSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
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
    // TAMBAHAN UNTUK TRANSLASI BAHASA INDONESIA
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('latitude')
                    ->label('Latitude'),
                TextColumn::make('longitude')
                    ->label('Longitude'),
                TextColumn::make('radius')
                    ->label('Batas Radius')
                    ->suffix(' M')
                    ->badge()
                    ->color('success'),
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