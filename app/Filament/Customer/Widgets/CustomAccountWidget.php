<?php

namespace App\Filament\Customer\Widgets;

use Filament\Widgets\Widget;

class CustomAccountWidget extends Widget
{
    protected static ?int $sort = -3;

    protected static bool $isLazy = false;

    protected int | string | array $columnSpan = "full";
    protected static string $view = 'filament.customer.widgets.custom-account-widget';
}
