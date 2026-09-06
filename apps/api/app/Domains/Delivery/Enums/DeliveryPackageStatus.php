<?php

namespace App\Domains\Delivery\Enums;

enum DeliveryPackageStatus: string
{
    case Preparing = 'PREPARING';
    case Ready = 'READY';
    case Downloaded = 'DOWNLOADED';
    case Revoked = 'REVOKED';

    public function label(): string
    {
        return match ($this) {
            self::Preparing => 'Preparing Package',
            self::Ready => 'Ready for Handover',
            self::Downloaded => 'Downloaded',
            self::Revoked => 'Revoked',
        };
    }

    public function isAvailable(): bool
    {
        return in_array($this, [self::Ready, self::Downloaded]);
    }
}
