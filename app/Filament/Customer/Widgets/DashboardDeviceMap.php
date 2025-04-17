<?php

namespace App\Filament\Customer\Widgets;

use Filament\Widgets\Widget;
use Webbingbrasil\FilamentMaps\Actions\CenterMapAction;
use Webbingbrasil\FilamentMaps\Actions\ZoomAction;
use Webbingbrasil\FilamentMaps\Marker;
use Webbingbrasil\FilamentMaps\Widgets\MapWidget;

class DashboardDeviceMap extends MapWidget
{
//    protected static string $view = 'filament.customer.widgets.dashboard-device-map';

    protected int | string | array $columnSpan = 2;

    protected bool $hasBorder = false;

    public function getMarkers(): array
    {
        return [
            Marker::make('pos2')->lat(-15.7942)->lng(-47.8822)->popup('Hello Brasilia!'),
        ];
    }

    public function getActions(): array
    {
        return [
            ZoomAction::make(),
            CenterMapAction::make()->zoom(2),
        ];
    }
}

