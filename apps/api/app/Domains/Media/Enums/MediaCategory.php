<?php

namespace App\Domains\Media\Enums;

enum MediaCategory: string
{
    case RawFootage = 'RAW_FOOTAGE';
    case PhotoSelection = 'PHOTO_SELECTION';
    case InternalDraft = 'INTERNAL_DRAFT';
    case ClientPreview = 'CLIENT_PREVIEW';
    case FinalMaster = 'FINAL_MASTER';
    case Attachment = 'ATTACHMENT';

    public function label(): string
    {
        return match ($this) {
            self::RawFootage => 'Raw Footage',
            self::PhotoSelection => 'Photo Selection',
            self::InternalDraft => 'Internal Draft',
            self::ClientPreview => 'Client Preview',
            self::FinalMaster => 'Final Master',
            self::Attachment => 'Attachment',
        };
    }
}
