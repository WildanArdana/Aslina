<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Filament\Resources\EmployeeResource\RelationManagers;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model; 

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Actions\Action;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    // ==========================================
    // TAMBAHAN UNTUK TRANSLASI BAHASA INDONESIA
    // ==========================================
    protected static ?string $navigationLabel = 'Data Karyawan';
    protected static ?string $modelLabel = 'Karyawan';
    protected static ?string $pluralModelLabel = 'Data Karyawan';
    // ==========================================

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('uid')
                    ->label('UID (Otomatis untuk QR)')
                    ->default(uniqid('EMP-'))
                    ->readOnly()
                    ->required(),
                TextInput::make('name')
                    ->label('Nama Karyawan')
                    ->required(),
                TextInput::make('position')
                    ->label('Jabatan')
                    ->required(),
                TextInput::make('department')
                    ->label('Bagian / Unit') // Disesuaikan dengan istilah pabrik
                    ->required(),
                
                FileUpload::make('photo')
                    ->label('Foto Karyawan')
                    ->image()
                    ->directory('employee-photos')
                    ->imageEditor()
                    ->circleCropper()
                    ->required()
                    ->preserveFilenames() 
                    ->removeUploadedFileButtonPosition('right') 
                    ->uploadingMessage('Sedang mengunggah...')
                    ->columnSpanFull(), 
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo')
                    ->label('Foto')
                    ->circular(),
                TextColumn::make('uid')
                    ->label('UID')
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Nama Karyawan')
                    ->searchable(),
                TextColumn::make('position')
                    ->label('Jabatan')
                    ->searchable(),
                TextColumn::make('department')
                    ->label('Bagian / Unit') // Disesuaikan dengan istilah pabrik
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                
                Action::make('cetak_id')
                    ->label('Cetak ID Card')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->url(fn (Employee $record): string => url('/employee/print-id/' . $record->id))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}