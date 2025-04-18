<?php

namespace App\Filament\Customer\Widgets;

use App\Models\Device;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Log;
use Webbingbrasil\FilamentMaps\Actions\CenterMapAction;
use Webbingbrasil\FilamentMaps\Actions\ZoomAction;
use Webbingbrasil\FilamentMaps\Marker;
use Webbingbrasil\FilamentMaps\Widgets\MapWidget;

class DashboardDeviceMap extends MapWidget
{
//    protected static string $view = 'filament.customer.widgets.dashboard-device-map';

    use InteractsWithPageFilters;

    protected int | string | array $columnSpan = 1;
    protected static ?int $sort = 2;

    protected bool $hasBorder = false;

    protected function getDeviceLocation(?string $deviceId): array
    {
        // Default coordinates (e.g. Amsterdam)
        $latitude = 52.3676;
        $longitude = 4.9041;

        if ($deviceId && $device = \App\Models\Device::find($deviceId)) {
            $latestLocation = $device->latestLocation; // assumes relationship exists

            if ($latestLocation) {
                $latitude = $latestLocation->latitude ?? $latitude;
                $longitude = $latestLocation->longitude ?? $longitude;
            }
        }

        return [$latitude, $longitude];
    }
    public function getMarkers(): array
    {
        $deviceId = $this->filters['selectedDevice'] ?? null;
        if ($deviceId && $device = Device::find($deviceId)) {

            $latestLocation = $device->latestLocation;

            if ($latestLocation && $latestLocation->latitude && $latestLocation->longitude) {
                $updatedAt = $latestLocation->updated_at
                    ? $latestLocation->updated_at->locale('nl')->diffForHumans()
                    : 'onbekend';

                return [
                    Marker::make('devicelocation')
                        ->lat($latestLocation->latitude)
                        ->lng($latestLocation->longitude)
                        ->popup("📍 Huidige locatie<br><small>Laatst bijgewerkt: {$updatedAt}</small>"),
                ];
            }
        }

        return [];
    }

    public function getActions(): array
    {
        $deviceId = $this->filters['selectedDevice'] ?? null;
        [$latitude, $longitude] = $this->getDeviceLocation($deviceId);

        return [
            ZoomAction::make(),
            CenterMapAction::make()
                ->centerTo([$latitude, $longitude])
                ->zoom(14), // adjust zoom if needed
        ];
    }
}

