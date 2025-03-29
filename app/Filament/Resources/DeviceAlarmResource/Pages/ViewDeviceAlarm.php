<?php

namespace App\Filament\Resources\DeviceAlarmResource\Pages;

use App\Filament\Resources\DeviceAlarmResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDeviceAlarm extends ViewRecord
{
    protected static string $resource = DeviceAlarmResource::class;

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
