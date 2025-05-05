<?php

namespace App\Filament\Customer\Resources\DeviceAlarmResource\Pages;

use App\Filament\Customer\Resources\DeviceAlarmResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDeviceAlarms extends ListRecords
{
    protected static string $resource = DeviceAlarmResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
