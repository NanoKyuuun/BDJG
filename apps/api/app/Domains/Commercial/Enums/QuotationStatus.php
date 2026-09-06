<?php

namespace App\Domains\Commercial\Enums;

enum QuotationStatus: string
{
    case Draft = 'DRAFT';
    case Sent = 'SENT';
    case Viewed = 'VIEWED';
    case RevisionRequested = 'REVISION_REQUESTED';
    case Accepted = 'ACCEPTED';
    case Declined = 'DECLINED';
    case Expired = 'EXPIRED';
    case Cancelled = 'CANCELLED';

    public function isFinal(): bool
    {
        return in_array($this, [self::Accepted, self::Declined, self::Cancelled, self::Expired], true);
    }

    public function canBeModified(): bool
    {
        return in_array($this, [self::Draft, self::RevisionRequested], true);
    }

    public function canBeSent(): bool
    {
        return in_array($this, [self::Draft, self::RevisionRequested], true);
    }

    public function canClientRespond(): bool
    {
        return in_array($this, [self::Sent, self::Viewed], true);
    }
}
