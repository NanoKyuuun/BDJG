<?php

namespace App\Domains\Revisions\Actions;

use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Revisions\Enums\CommentStatus;
use App\Domains\Revisions\Models\Revision;
use App\Domains\Revisions\Models\RevisionComment;
use App\Domains\Revisions\Policies\RevisionPolicy;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class AddRevisionCommentAction
{
    public function execute(
        Revision $revision,
        User $author,
        string $commentText,
        ?float $timecodeSeconds = null,
        ?int $frameNumber = null,
        ?array $coordinates = null
    ): RevisionComment {
        $policy = app(RevisionPolicy::class);
        if (! $policy->view($author, $revision)) {
            throw new AuthorizationException('User is not authorized to comment on this revision round.');
        }

        $comment = RevisionComment::create([
            'revision_id' => $revision->id,
            'media_asset_id' => $revision->media_asset_id,
            'user_id' => $author->id,
            'timecode_seconds' => $timecodeSeconds,
            'frame_number' => $frameNumber,
            'coordinates' => $coordinates,
            'comment' => $commentText,
            'status' => CommentStatus::Open,
        ]);

        AuditLogger::log(
            action: 'REVISION_COMMENT_ADDED',
            description: "Comment added on Revision #{$revision->round_number} at {$timecodeSeconds}s by {$author->name}",
            auditable: $comment,
            newValues: [
                'timecode_seconds' => $timecodeSeconds,
                'comment' => $commentText,
            ]
        );

        return $comment;
    }
}
