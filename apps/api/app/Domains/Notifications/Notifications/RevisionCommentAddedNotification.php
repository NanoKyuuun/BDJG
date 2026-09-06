<?php

namespace App\Domains\Notifications\Notifications;

use App\Domains\Revisions\Models\RevisionComment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class RevisionCommentAddedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public RevisionComment $comment
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'REVISION_COMMENT_ADDED',
            'title' => 'New Revision Feedback Comment',
            'message' => "New comment at {$this->comment->timecode_seconds}s on Revision #{$this->comment->revision?->round_number} by {$this->comment->author?->name}: \"{$this->comment->comment}\"",
            'revision_id' => $this->comment->revision_id,
            'comment_id' => $this->comment->id,
            'timecode_seconds' => $this->comment->timecode_seconds,
            'action_url' => "/revisions/{$this->comment->revision_id}",
        ];
    }
}
