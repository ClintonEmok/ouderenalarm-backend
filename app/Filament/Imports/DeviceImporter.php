<?php

namespace App\Filament\Imports;

use App\Models\Device;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class DeviceImporter extends Importer
{
    protected static ?string $model = Device::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('imei')
                ->label('IMEI')
                ->requiredMapping()
                ->rules(['required', 'max:255']),

            ImportColumn::make('phone_number')->label("Telefoonnummer")
                ->rules(['max:255']),

        ];
    }

    public function resolveRecord(): ?Device
    {
         return Device::firstOrNew([
             // Update existing records, matching them by `$this->data['column_name']`
             'imei' => $this->data['imei'],
         ]);

//        return new Device();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Importeren van jouw apparaten is geslaagd en ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' zijn geimporteerd.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' niet gelukt om te importeren';
        }

        return $body;
    }
}
