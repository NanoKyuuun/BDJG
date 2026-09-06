<?php

namespace App\Domains\Billing\Enums;

enum InvoiceType: string
{
    case Dp = 'DP';
    case Progress = 'PROGRESS';
    case Final = 'FINAL';
    case Additional = 'ADDITIONAL';
}
