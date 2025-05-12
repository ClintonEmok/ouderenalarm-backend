<?php

namespace App\Filament\Customer\Resources\DeviceAlarmResource\Pages;

use App\Filament\Customer\Resources\DeviceAlarmResource;
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
