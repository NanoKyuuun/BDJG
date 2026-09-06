<?php

namespace App\Domains\Payments\Enums;

enum PaymentProvider: string
{
    case Duitku = 'DUITKU';
    case Manual = 'MANUAL';
}
