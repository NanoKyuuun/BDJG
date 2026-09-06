<?php

namespace App\Domains\Clients\Enums;

enum ClientStatus: string
{
    case Active = 'ACTIVE';
    case Inactive = 'INACTIVE';
    case Archived = 'ARCHIVED';
}
