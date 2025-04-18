<?php

namespace App\Filament\Customer\Widgets;

use App\Models\Device;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;

class DashboardDeviceDetails extends Widget
{
    use InteractsWithPageFilters;
    protected static string $view = 'filament.customer.widgets.dashboard-device-details';
    protected int | string | array $columnSpan = 1;

    public Device|null $device = null;

    public function mount(): void
    {
        $deviceId = $this->filters['device'] ?? null;
        $this->device = $deviceId
            ? Device::with('latestLocation')->find($deviceId)
            : null;
    }

}
