<?php

namespace App\Domains\Media\Enums;

enum ProcessingStatus: string
{
    case Pending = 'PENDING';
    case Processing = 'PROCESSING';
    case Ready = 'READY';
    case Failed = 'FAILED';
}
