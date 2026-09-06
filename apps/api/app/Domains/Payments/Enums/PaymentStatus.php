<?php

namespace App\Domains\Payments\Enums;

enum PaymentStatus: string
{
    case Unpaid = 'UNPAID';
    case Pending = 'PENDING';
    case Paid = 'PAID';
    case Failed = 'FAILED';
    case Expired = 'EXPIRED';
    case Refunded = 'REFUNDED';
    case PartiallyRefunded = 'PARTIALLY_REFUNDED';

    public function isSuccess(): bool
    {
        return $this === self::Paid;
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Paid, self::Failed, self::Expired, self::Refunded], true);
    }
}
