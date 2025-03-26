<?php

namespace App\Filament\Resources\DeviceAlarmResource\Pages;

use App\Filament\Resources\DeviceAlarmResource;
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
