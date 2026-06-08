<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeaveTypeResource\Pages;
use App\Filament\Resources\LeaveTypeResource\RelationManagers;
use App\Models\LeaveType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LeaveTypeResource extends Resource
{
    protected static ?string $model = LeaveType::class;

    // Anda bisa mengganti icon ini nanti jika ingin lebih sesuai dengan tema HR/Cuti
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->label('Nama Jenis Cuti / Izin')
                    ->placeholder('Contoh: Cuti Tahunan'),
                
                Forms\Components\TextInput::make('quota')
                    ->numeric()
                    ->label('Jatah Maksimal (Hari)')
                    ->placeholder('Kosongkan jika tidak ada batas/unlimited')
                    ->helperText('Berapa hari jatah maksimal dalam 1 tahun?'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Jenis Cuti')
                    ->searchable() // Tambahan opsional agar admin mudah mencari
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('quota')
                    ->label('Jatah (Hari)')
                    ->suffix(' Hari')
                    ->default('Tanpa Batas') // Jika null, tampilkan teks ini
                    ->badge(),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeaveTypes::route('/'),
            'create' => Pages\CreateLeaveType::route('/create'),
            'edit' => Pages\EditLeaveType::route('/{record}/edit'),
        ];
    }
}