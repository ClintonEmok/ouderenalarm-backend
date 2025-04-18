<?php

namespace App\Filament\Customer\Widgets;

use App\Models\Device;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Facades\Log;
// TODO: add device as a passable arg later
class DeviceBatteryWidget extends BaseWidget
{
    use InteractsWithPageFilters;
    protected static ?string $pollingInterval = '10s';
    protected function getStats(): array
    {
        $deviceId = $this->filters['device'] ?? null;
        if (! $deviceId || ! $device = Device::find($deviceId)) {

            return [
                Stat::make('Batterijniveau', 'Onbekend'),
            ];
        }

        // 🔋 Laatste batterijwaarde via relatie
        $batteryLevel = $device->generalStatuses
            ->sortByDesc('created_at')
            ->first()?->battery_level;
        Log::info("BatteryLevel: $batteryLevel");

        // 📈 Trend (voor later)
        $trendData = Trend::query(
            $device->generalStatuses()->getQuery()
        )
            ->between(
                now()->startOfDay(),
                now()->endOfDay(),
            )
            ->perHour()
            ->average('battery_level');


        return [
            Stat::make('Batterijniveau', $batteryLevel !== null ? $batteryLevel . '%' : 'Onbekend')
//                ->description('')
                ->color(
                    $batteryLevel === null ? 'gray' :
                        ($batteryLevel < 20 ? 'danger' :
                            ($batteryLevel < 50 ? 'warning' : 'success'))
                ),
        ];
    }
}
