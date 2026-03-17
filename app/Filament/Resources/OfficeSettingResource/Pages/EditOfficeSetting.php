<?php

namespace App\Filament\Resources\OfficeSettingResource\Pages;

use App\Filament\Resources\OfficeSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOfficeSetting extends EditRecord
{
    protected static string $resource = OfficeSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
