<?php

namespace App\Domains\Media\Enums;

enum UploadStatus: string
{
    case Pending = 'PENDING';
    case Completed = 'COMPLETED';
    case Expired = 'EXPIRED';
    case Failed = 'FAILED';
}
