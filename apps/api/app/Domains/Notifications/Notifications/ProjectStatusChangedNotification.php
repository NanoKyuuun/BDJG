<?php

namespace App\Domains\Notifications\Notifications;

use App\Domains\Projects\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ProjectStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Project $project,
        public string $oldStatus,
        public string $newStatus
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'PROJECT_STATUS_CHANGED',
            'title' => 'Project Status Updated',
            'message' => "Project '{$this->project->name}' moved from {$this->oldStatus} to {$this->newStatus}",
            'project_id' => $this->project->id,
            'project_name' => $this->project->name,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'action_url' => "/projects/{$this->project->id}",
        ];
    }
}
