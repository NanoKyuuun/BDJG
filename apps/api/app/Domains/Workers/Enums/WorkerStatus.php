<?php

namespace App\Domains\Workers\Enums;

enum WorkerStatus: string
{
    case Active = 'ACTIVE';
    case Inactive = 'INACTIVE';
    case OnLeave = 'ON_LEAVE';
}
