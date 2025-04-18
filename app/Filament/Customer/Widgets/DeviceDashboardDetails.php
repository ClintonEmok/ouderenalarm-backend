<?php

namespace App\Filament\Customer\Widgets;

use App\Models\Device;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Log;

class DeviceDashboardDetails extends Widget
{
    protected static string $view = 'filament.customer.widgets.device-dashboard-details';
    use InteractsWithPageFilters;

    protected int | string | array $columnSpan = 1;
    protected static ?int $sort = 1;

    protected function getViewData(): array
    {
        $deviceId = $this->filters['device'] ?? null;
        Log::info("device id: $deviceId");

        $device = $deviceId
            ? Device::with('generalStatuses')->find($deviceId)
            : null;

        $latestStatus = $device?->generalStatuses
            ?->sortByDesc('created_at')
            ?->first();

        Log::info("latestStatus: $latestStatus");
        Log::info("device: $device");
        return [
            'device' => $device,
            'batteryLevel' => $latestStatus?->battery_level,
            'signalStrength' => $latestStatus?->signal_strength,
            'lastUpdatedAt' => $latestStatus?->created_at,
        ];
    }
}
