<?php

namespace App\Domains\Tasks\Enums;

enum TaskStatus: string
{
    case Todo = 'TODO';
    case InProgress = 'IN_PROGRESS';
    case Review = 'REVIEW';
    case Blocked = 'BLOCKED';
    case Done = 'DONE';
    case Cancelled = 'CANCELLED';

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Todo => [self::InProgress, self::Cancelled],
            self::InProgress => [self::Review, self::Blocked, self::Done, self::Cancelled],
            self::Blocked => [self::InProgress, self::Cancelled],
            self::Review => [self::Done, self::InProgress, self::Cancelled],
            self::Done => [self::InProgress], // Allow reopen if needed
            self::Cancelled => [self::Todo],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        if ($this === $target) {
            return true;
        }

        return in_array($target, $this->allowedTransitions(), true);
    }
}
