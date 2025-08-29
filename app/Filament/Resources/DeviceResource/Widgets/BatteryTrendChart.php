<?php

namespace App\Filament\Resources\DeviceResource\Widgets;

use Filament\Widgets\ChartWidget;

class BatteryTrendChart extends ChartWidget
{
    protected static ?string $heading = 'Chart';
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        return [
            //
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
