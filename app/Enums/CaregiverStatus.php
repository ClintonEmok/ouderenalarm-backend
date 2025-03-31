<?php

namespace App\Enums;

enum CaregiverStatus: string
{
    case Assigned = 'assigned';
    case EnRoute = 'en_route';
    case Arrived = 'arrived';

    public function label(): string
    {
        return match ($this) {
            self::Assigned => __('Assigned'),
            self::EnRoute => __('En Route'),
            self::Arrived => __('Arrived'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Assigned => 'gray',
            self::EnRoute => 'warning',
            self::Arrived => 'success',
        };
    }
}
