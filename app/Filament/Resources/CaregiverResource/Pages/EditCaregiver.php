<?php

namespace App\Filament\Resources\CaregiverResource\Pages;

use App\Filament\Resources\CaregiverResource;
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

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['patient_id'] = auth()->id(); // Ensure the current user's ID is always set for updates
        return $data;
    }
}
