<?php

namespace App\Filament\Resources\DeviceResource\Widgets;

use App\Models\Device;
use App\Models\GeneralStatus;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Carbon;

class BatteryTrendChart extends ChartWidget
{
    protected static ?string $heading = 'Batterijverloop';
    protected int | string | array $columnSpan = "full";
    protected static ?string $pollingInterval = '60s'; // optioneel auto-refresh

    /** Wordt door Filament gezet op de Edit-pagina */
    public ?Device $record = null;

    protected function getType(): string
    {
        return 'line';
    }

    public function getDescription(): ?string
    {
        return 'Gemeten batterijpercentage op basis van statusupdates.';
    }

    protected function getFilters(): ?array
    {
        return [
            'day'   => 'Dag',
            'week'  => 'Week',
            'month' => 'Maand',
        ];
    }

    protected function getFilter(): ?string
    {
        return 'day';
    }

    protected function getData(): array
    {
        if (! $this->record?->id) {
            return [
                'datasets' => [[ 'label' => 'Batterij (%)', 'data' => [] ]],
                'labels' => [],
            ];
        }

        $filter = $this->filter;
        $end = now();

        if ($filter === 'week') {
            // Laatste 7 dagen (incl. vandaag) -> per dag gemiddelde
            $start = now()->subDays(6)->startOfDay();

            $trend = Trend::query(
                GeneralStatus::where('device_id', $this->record->id)
            )
                ->between(start: $start, end: $end)
                ->perDay()
                ->average('battery_level');

            $labels = $trend->map(fn (TrendValue $v) => Carbon::parse($v->date)->timezone('Europe/Amsterdam')->format('d-m'))->all();
        } elseif ($filter === 'month') {
            // Laatste 30 dagen -> per dag gemiddelde
            $start = now()->subDays(29)->startOfDay();

            $trend = Trend::query(
                GeneralStatus::where('device_id', $this->record->id)
            )
                ->between(start: $start, end: $end)
                ->perDay()
                ->average('battery_level');

            $labels = $trend->map(fn (TrendValue $v) => Carbon::parse($v->date)->timezone('Europe/Amsterdam')->format('d-m'))->all();
        } else {
            // Dag: laatste 24 uur -> per uur gemiddelde
            $start = now()->subHours(23)->startOfHour();

            $trend = Trend::query(
                GeneralStatus::where('device_id', $this->record->id)
            )
                ->between(start: $start, end: $end)
                ->perHour()
                ->average('battery_level');

            $labels = $trend->map(fn (TrendValue $v) => Carbon::parse($v->date)->timezone('Europe/Amsterdam')->format('H:i'))->all();
        }

        $values = $trend->map(fn (TrendValue $v) => $v->aggregate)->all();

        return [
            'datasets' => [[
                'label' => 'Batterij (%)',
                'data' => $values,
                'tension' => 0.3,
                'pointRadius' => 2,
                'spanGaps' => true,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'labels' => ['boxWidth' => 12],
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => fn ($ctx) => ($ctx->dataset->label ?? 'Batterij') . ': ' . ($ctx->parsed['y'] ?? '—') . '%',
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'title' => ['display' => true, 'text' => 'Batterij (%)'],
                    'min' => 0,
                    'max' => 100,
                    'ticks' => ['stepSize' => 10],
                ],
                'x' => [
                    'title' => ['display' => true, 'text' => 'Tijd'],
                ],
            ],
        ];
    }
}