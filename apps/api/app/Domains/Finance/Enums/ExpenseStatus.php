<?php

namespace App\Domains\Finance\Enums;

enum ExpenseStatus: string
{
    case Draft = 'DRAFT';
    case Submitted = 'SUBMITTED';
    case Approved = 'APPROVED';
    case Rejected = 'REJECTED';
    case Paid = 'PAID';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft Claim',
            self::Submitted => 'Submitted for Review',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Paid => 'Paid & Reconciled',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Rejected, self::Paid]);
    }
}
