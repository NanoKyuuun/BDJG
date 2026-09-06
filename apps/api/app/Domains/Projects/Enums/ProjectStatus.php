<?php

namespace App\Domains\Projects\Enums;

enum ProjectStatus: string
{
    case Draft = 'DRAFT';
    case PreProduction = 'PRE_PRODUCTION';
    case Production = 'PRODUCTION';
    case PostProduction = 'POST_PRODUCTION';
    case InternalReview = 'INTERNAL_REVIEW';
    case ClientReview = 'CLIENT_REVIEW';
    case Revision = 'REVISION';
    case FinalApproval = 'FINAL_APPROVAL';
    case FinalDelivery = 'FINAL_DELIVERY';
    case Completed = 'COMPLETED';
    case Archived = 'ARCHIVED';
    case Cancelled = 'CANCELLED';
    case OnHold = 'ON_HOLD';

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::PreProduction, self::Cancelled],
            self::PreProduction => [self::Production, self::OnHold, self::Cancelled],
            self::Production => [self::PostProduction, self::OnHold, self::Cancelled],
            self::PostProduction => [self::InternalReview, self::OnHold, self::Cancelled],
            self::InternalReview => [self::ClientReview, self::PostProduction, self::Cancelled],
            self::ClientReview => [self::Revision, self::FinalApproval, self::Cancelled],
            self::Revision => [self::InternalReview, self::PostProduction, self::Cancelled],
            self::FinalApproval => [self::FinalDelivery, self::Cancelled],
            self::FinalDelivery => [self::Completed, self::Cancelled],
            self::Completed => [self::Archived],
            self::OnHold => [self::PreProduction, self::Production, self::PostProduction, self::Cancelled],
            self::Archived, self::Cancelled => [],
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
