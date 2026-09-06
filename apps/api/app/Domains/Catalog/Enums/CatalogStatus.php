<?php

namespace App\Domains\Catalog\Enums;

enum CatalogStatus: string
{
    case Active = 'ACTIVE';
    case Inactive = 'INACTIVE';
    case Archived = 'ARCHIVED';
}
