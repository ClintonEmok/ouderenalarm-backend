<?php

namespace App\Filament\Resources\DeviceAlarmResource\Pages;

use App\Filament\Resources\DeviceAlarmResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDeviceAlarm extends EditRecord
{
    protected static string $resource = DeviceAlarmResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
