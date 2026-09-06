<?php

namespace App\Domains\Revisions\Enums;

enum RevisionRoundStatus: string
{
    case Open = 'OPEN';
    case InProgress = 'IN_PROGRESS';
    case Submitted = 'SUBMITTED';
    case Resolved = 'RESOLVED';
    case Closed = 'CLOSED';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::InProgress => 'In Progress',
            self::Submitted => 'Submitted for Review',
            self::Resolved => 'Resolved',
            self::Closed => 'Closed',
        };
    }
}
