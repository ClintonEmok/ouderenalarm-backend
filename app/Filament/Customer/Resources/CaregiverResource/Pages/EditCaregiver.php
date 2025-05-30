<?php

namespace App\Filament\Customer\Resources\CaregiverResource\Pages;

use App\Filament\Customer\Resources\CaregiverResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCaregiver extends EditRecord
{
    protected static string $resource = CaregiverResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
