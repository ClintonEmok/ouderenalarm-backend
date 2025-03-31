<?php

namespace App\Filament\Resources\GPSLocationResource\Pages;

use App\Filament\Resources\GPSLocationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGPSLocation extends EditRecord
{
    protected static string $resource = GPSLocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
