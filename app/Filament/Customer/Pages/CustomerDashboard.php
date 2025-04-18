<?php

namespace App\Filament\Pages;

use App\Models\Device;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
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
//        TODO: Filter for selecting current device incase of multiple
        $devices = Device::query()
            ->accessibleTo(auth()->user())
            ->pluck('imei', 'id');

        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('device')
                            ->options($devices->toArray())->default(Device::query()
                                ->accessibleTo(auth()->user())->first()
                                ->pluck('imei', 'id')),
//                        DatePicker::make('startDate')
//                            ->maxDate(fn (Get $get) => $get('endDate') ?: now()),
//                        DatePicker::make('endDate')
//                            ->minDate(fn (Get $get) => $get('startDate') ?: now())
//                            ->maxDate(now()),
                    ])
                    ->columns(3),
            ]);
    }
}
