<?php

namespace App\Filament\Resources\CaregiverResource\Pages;

use App\Filament\Resources\CaregiverResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCaregiver extends CreateRecord
{
    protected static string $resource = CaregiverResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['patient_id'] = auth()->id(); // Ensure the current user's ID is always set
        return $data;
    }


}
