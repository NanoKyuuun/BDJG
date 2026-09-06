<?php

namespace App\Domains\CRM\Enums;

enum InquiryStatus: string
{
    case New = 'NEW';
    case Contacted = 'CONTACTED';
    case Qualified = 'QUALIFIED';
    case Quotation = 'QUOTATION';
    case Won = 'WON';
    case Lost = 'LOST';

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::New => [self::Contacted, self::Lost],
            self::Contacted => [self::Qualified, self::Lost],
            self::Qualified => [self::Quotation, self::Lost],
            self::Quotation => [self::Won, self::Lost],
            self::Won => [],
            self::Lost => [self::New, self::Contacted], // Allow reopening if needed
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
