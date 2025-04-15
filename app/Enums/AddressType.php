<?php
namespace App\Enums;

enum AddressType: string
{
    case Billing = 'billing';
    case Shipping = 'shipping';

    public function label(): string
    {
        return match ($this) {
            self::Billing => __('Billing'),
            self::Shipping => __('Shipping'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Billing => 'primary',
            self::Shipping => 'secondary',
        };
    }
}
