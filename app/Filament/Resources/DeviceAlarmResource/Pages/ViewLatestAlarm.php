<?php

namespace App\Filament\Resources\DeviceAlarmResource\Pages;

use App\Filament\Resources\DeviceAlarmResource;
use App\Models\DeviceAlarm;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewLatestAlarm extends ViewRecord
{
    protected static string $resource = DeviceAlarmResource::class;
    public $location;

    public function mount(int|string $record): void
    {

        $this->record  = DeviceAlarm::whereHas('device', fn ($q) =>
        $q->where('connection_number', $record)
        )
            ->latest()
            ->firstOrFail();

        $this->authorizeAccess();

        if (! $this->hasInfolist()) {
            $this->fillForm();
        }
    }

}
