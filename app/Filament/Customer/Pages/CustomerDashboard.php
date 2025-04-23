<?php

namespace App\Filament\Customer\Pages;

use App\Models\Device;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Pages\Dashboard as BaseDashboard;

class CustomerDashboard extends BaseDashboard
{
    use BaseDashboard\Concerns\HasFiltersForm;

    public function getColumns(): int | string | array
    {
        return 2;
    }
    public function filtersForm(Form $form): Form
    {

        $devices = Device::query()
            ->accessibleTo(auth()->user())
            ->pluck('imei', 'id');

        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('selectedDevice')
                            ->options($devices->toArray()),
                    ])
                    ->columns(3),
            ]);
    }

    public function updatedFilters($value, $key): void
    {

        if ($key === 'selectedDevice') {

            $this->dispatch('deviceSelectedUpdated', deviceId: $value);
        }
    }
}
