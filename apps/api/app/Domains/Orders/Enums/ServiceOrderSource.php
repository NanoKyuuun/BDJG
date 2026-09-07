<?php

namespace App\Domains\Orders\Enums;

enum ServiceOrderSource: string
{
    case Catalog = 'CATALOG';
    case Reorder = 'REORDER';
    case Custom = 'CUSTOM';
}
