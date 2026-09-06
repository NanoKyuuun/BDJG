<?php

namespace App\Domains\Media\Enums;

enum FileVisibility: string
{
    case Internal = 'INTERNAL';
    case AssignedWorkers = 'ASSIGNED_WORKERS';
    case ClientShared = 'CLIENT_SHARED';
    case ClientPreview = 'CLIENT_PREVIEW';
    case FinalReleased = 'FINAL_RELEASED';
    case Public = 'PUBLIC';

    public function isClientVisible(): bool
    {
        return in_array($this, [self::ClientShared, self::ClientPreview, self::FinalReleased, self::Public], true);
    }

    public function isWorkerVisible(): bool
    {
        return in_array($this, [self::Internal, self::AssignedWorkers, self::ClientShared, self::ClientPreview, self::FinalReleased, self::Public], true);
    }
}
