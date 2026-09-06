<?php

namespace App\Domains\Billing\Enums;

enum InvoiceStatus: string
{
    case Draft = 'DRAFT';
    case Issued = 'ISSUED';
    case PartiallyPaid = 'PARTIALLY_PAID';
    case Paid = 'PAID';
    case Overdue = 'OVERDUE';
    case Void = 'VOID';
    case Refunded = 'REFUNDED';

    public function isPayable(): bool
    {
        return in_array($this, [self::Issued, self::PartiallyPaid, self::Overdue], true);
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Paid, self::Void, self::Refunded], true);
    }
}
