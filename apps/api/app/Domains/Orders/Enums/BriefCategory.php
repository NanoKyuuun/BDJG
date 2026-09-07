<?php

namespace App\Domains\Orders\Enums;

enum BriefCategory: string
{
    case Wedding = 'WEDDING';
    case CoupleSession = 'COUPLE_SESSION';
    case Commercial = 'COMMERCIAL';
    case Custom = 'CUSTOM';
}
