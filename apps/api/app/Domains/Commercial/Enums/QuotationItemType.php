<?php

namespace App\Domains\Commercial\Enums;

enum QuotationItemType: string
{
    case Package = 'PACKAGE';
    case AddOn = 'ADD_ON';
    case Custom = 'CUSTOM';
    case Discount = 'DISCOUNT';
}
