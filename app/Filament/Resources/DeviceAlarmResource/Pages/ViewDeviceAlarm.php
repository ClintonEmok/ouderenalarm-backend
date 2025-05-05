<?php

namespace App\Filament\Resources\DeviceAlarmResource\Pages;

use App\Filament\Resources\DeviceAlarmResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDeviceAlarm extends ViewRecord
{
    protected static string $resource = DeviceAlarmResource::class;
    public $location;


    public function mount($record): void
    {
        parent::mount($record);

//        TODO: remove
        // Safely call the refresh method
        if (method_exists($this->record, 'refreshCaregiverStatuses')) {
            $this->record->refreshCaregiverStatuses();
        }
    }
    protected function getHeaderActions(): array
    {
        return [
//            Actions\EditAction::make(),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            DeviceAlarmResource\Widgets\RecentDeviceAlarmsWidget::class,
        ];
    }


}
