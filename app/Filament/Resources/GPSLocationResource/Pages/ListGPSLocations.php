<?php

namespace App\Filament\Resources\GPSLocationResource\Pages;

use App\Filament\Resources\GPSLocationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGPSLocations extends ListRecords
{
    protected static string $resource = GPSLocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
