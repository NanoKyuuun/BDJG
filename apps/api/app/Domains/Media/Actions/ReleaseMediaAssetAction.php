<?php

namespace App\Domains\Media\Actions;

use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Media\Enums\FileVisibility;
use App\Domains\Media\Models\MediaAsset;
use App\Models\User;
use InvalidArgumentException;

class ReleaseMediaAssetAction
{
    public function execute(MediaAsset $mediaAsset, User $actor, ?FileVisibility $targetVisibility = null): MediaAsset
    {
        $newVisibility = $targetVisibility ?? FileVisibility::ClientPreview;

        if (! $newVisibility->isClientVisible()) {
            throw new InvalidArgumentException("Cannot release media with visibility {$newVisibility->value}. It must be client visible.");
        }

        $oldVisibility = $mediaAsset->visibility;
        $mediaAsset->visibility = $newVisibility;
        $mediaAsset->released_by_user_id = $actor->id;
        $mediaAsset->released_at = now();
        $mediaAsset->save();

        AuditLogger::log(
            action: 'MEDIA_ASSET_RELEASED',
            description: "Media asset '{$mediaAsset->original_name}' released to client with visibility {$newVisibility->value}",
            auditable: $mediaAsset,
            oldValues: ['visibility' => $oldVisibility->value],
            newValues: ['visibility' => $newVisibility->value, 'released_at' => $mediaAsset->released_at->toDateTimeString()]
        );

        return $mediaAsset;
    }
}
