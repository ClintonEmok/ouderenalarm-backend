<?php

namespace App\Filament\Resources\DeviceAccessRequestResource\Pages;

use App\Filament\Resources\DeviceAccessRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDeviceAccessRequest extends EditRecord
{
    protected static string $resource = DeviceAccessRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
