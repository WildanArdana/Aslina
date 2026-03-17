<?php

namespace App\Filament\Resources\OfficeSettingResource\Pages;

use App\Filament\Resources\OfficeSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOfficeSettings extends ListRecords
{
    protected static string $resource = OfficeSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
