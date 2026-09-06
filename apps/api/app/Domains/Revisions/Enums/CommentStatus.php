<?php

namespace App\Domains\Revisions\Enums;

enum CommentStatus: string
{
    case Open = 'OPEN';
    case InProgress = 'IN_PROGRESS';
    case Resolved = 'RESOLVED';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::InProgress => 'In Progress',
            self::Resolved => 'Resolved',
        };
    }
}
