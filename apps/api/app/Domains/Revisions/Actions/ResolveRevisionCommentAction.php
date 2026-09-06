<?php

namespace App\Domains\Revisions\Actions;

use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Revisions\Enums\CommentStatus;
use App\Domains\Revisions\Enums\RevisionRoundStatus;
use App\Domains\Revisions\Models\RevisionComment;
use App\Domains\Revisions\Policies\RevisionPolicy;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class ResolveRevisionCommentAction
{
    public function execute(RevisionComment $comment, User $actor): RevisionComment
    {
        $policy = app(RevisionPolicy::class);
        $revision = $comment->revision;

        if (! $policy->resolve($actor, $revision)) {
            throw new AuthorizationException('User is not authorized to resolve feedback on this project.');
        }

        $comment->status = CommentStatus::Resolved;
        $comment->resolved_by_user_id = $actor->id;
        $comment->resolved_at = now();
        $comment->save();

        AuditLogger::log(
            action: 'REVISION_COMMENT_RESOLVED',
            description: "Feedback comment #{$comment->id} marked resolved by {$actor->name}",
            auditable: $comment
        );

        // If all comments in round are resolved, optionally update round status
        $hasOpenComments = RevisionComment::where('revision_id', $revision->id)
            ->where('status', '!=', CommentStatus::Resolved)
            ->exists();

        if (! $hasOpenComments && $revision->status === RevisionRoundStatus::Open) {
            $revision->status = RevisionRoundStatus::Resolved;
            $revision->resolved_at = now();
            $revision->resolved_by_user_id = $actor->id;
            $revision->save();
        }

        return $comment;
    }
}
