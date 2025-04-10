<?php

namespace App\Filament\Resources\DeviceAlarmResource\Widgets;

use App\Filament\Resources\DeviceAlarmResource;
use App\Models\DeviceAlarm;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;

class RecentDeviceAlarmsWidget extends BaseWidget
{
    public ?Model $record = null;
    protected int | string | array $columnSpan = 'full';


    public function table(Table $table): Table
    {
        return $table
            ->heading("Historie van meldingen")
            ->query(
                DeviceAlarm::query()->where("device_id", $this->record->device_id)
            )
            ->recordUrl(
                fn (Model $record): string => DeviceAlarmResource::getUrl('view',['record' => $record]),
            )
            ->columns([
                TextColumn::make("created_at")->label("Wanneer"),
            ]);
    }
}
