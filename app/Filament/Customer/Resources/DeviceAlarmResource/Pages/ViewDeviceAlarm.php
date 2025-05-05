<?php

namespace App\Filament\Customer\Resources\DeviceAlarmResource\Pages;

use App\Filament\Customer\Resources\DeviceAlarmResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDeviceAlarm extends ViewRecord
{
    protected static string $resource = DeviceAlarmResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
