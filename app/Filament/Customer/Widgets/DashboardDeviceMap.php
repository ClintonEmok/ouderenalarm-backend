<?php

namespace App\Filament\Customer\Widgets;

use App\Models\Device;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Webbingbrasil\FilamentMaps\Actions\CenterMapAction;
use Webbingbrasil\FilamentMaps\Actions\ZoomAction;
use Webbingbrasil\FilamentMaps\Marker;
use Webbingbrasil\FilamentMaps\Widgets\MapWidget;

class DashboardDeviceMap extends MapWidget
{
    use InteractsWithPageFilters;

    protected int|string|array $columnSpan = 1;
    protected static ?int $sort = 2;
    protected bool $hasBorder = false;

    #[On('deviceSelectedUpdated')]
    public function handleDeviceSelection(?string $deviceId): void
    {
        $device = $deviceId ? Device::find($deviceId) : null;

        if ($device && $location = $device->latestLocation) {
            $updatedAt = $location->updated_at
                ? $location->updated_at->locale('nl')->diffForHumans()
                : 'onbekend';
            $marker = Marker::make("device-{$device->id}")
                ->lat($location->latitude)
                ->lng($location->longitude)
                ->popup("📍 Huidige locatie<br><small>Laatst bijgewerkt: {$updatedAt}</small>");

            $this->mapMarkers([$marker]);
            $this->centerTo([$location->latitude, $location->longitude], 13);
        } else {
            $this->mapMarkers([]); // clear markers if no device or no location
        }
    }

    protected function getDeviceLocation(?string $deviceId): array
    {
        $latitude = 52.3676;  // Default (Amsterdam)
        $longitude = 4.9041;

        if ($deviceId && $device = Device::find($deviceId)) {
            $latestLocation = $device->latestLocation;

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

        $devices = Device::query()
            ->accessibleTo(auth()->user())
            ->pluck('imei', 'id');

        if (!$deviceId && $devices->isNotEmpty()) {
            $deviceId = $devices->keys()->first();
            Log::info('No device selected in map, using first accessible device as default', ['deviceId' => $deviceId]);
        }

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

        $devices = Device::query()
            ->accessibleTo(auth()->user())
            ->pluck('imei', 'id');

        if (!$deviceId && $devices->isNotEmpty()) {
            $deviceId = $devices->keys()->first();
            Log::info('No device selected in actions, using first accessible device as default', ['deviceId' => $deviceId]);
        }

        [$latitude, $longitude] = $this->getDeviceLocation($deviceId);

        return [
            ZoomAction::make(),
            CenterMapAction::make()
                ->centerTo([$latitude, $longitude])
                ->zoom(14),
        ];
    }
}
