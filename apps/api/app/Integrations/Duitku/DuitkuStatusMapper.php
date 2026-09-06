<?php

namespace App\Integrations\Duitku;

use App\Domains\Payments\Enums\PaymentStatus;

class DuitkuStatusMapper
{
    public static function mapResultCode(string|int|null $code): PaymentStatus
    {
        $codeStr = (string) $code;

        return match ($codeStr) {
            '00', 'SUCCESS' => PaymentStatus::Paid,
            '01', 'PENDING' => PaymentStatus::Pending,
            '02', 'FAILED', 'CANCELED' => PaymentStatus::Failed,
            '03', 'EXPIRED' => PaymentStatus::Expired,
            default => PaymentStatus::Pending,
        };
    }
}
