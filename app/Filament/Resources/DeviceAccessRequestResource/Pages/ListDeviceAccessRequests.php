<?php

namespace App\Filament\Resources\DeviceAccessRequestResource\Pages;

use App\Filament\Resources\DeviceAccessRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDeviceAccessRequests extends ListRecords
{
    protected static string $resource = DeviceAccessRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
