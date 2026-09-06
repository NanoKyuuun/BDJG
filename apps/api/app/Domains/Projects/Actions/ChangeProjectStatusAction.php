<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Projects\Enums\ProjectStatus;
use App\Domains\Projects\Models\Project;
use App\Models\User;
use InvalidArgumentException;

class ChangeProjectStatusAction
{
    public function execute(Project $project, ProjectStatus $newStatus, ?string $notes = null, ?User $actor = null): Project
    {
        if (! $project->status->canTransitionTo($newStatus)) {
            throw new InvalidArgumentException("Cannot transition project from {$project->status->value} to {$newStatus->value}.");
        }

        $oldStatus = $project->status;
        $project->status = $newStatus;

        if ($newStatus === ProjectStatus::Completed && empty($project->completed_at)) {
            $project->completed_at = now();
        }

        if ($notes) {
            $project->notes_internal = ($project->notes_internal ? $project->notes_internal."\n" : '').'['.now()->toDateTimeString()."] Status changed to {$newStatus->value}: {$notes}";
        }

        $project->save();

        AuditLogger::log(
            action: 'PROJECT_STATUS_UPDATED',
            description: "Project #{$project->project_number} status changed from {$oldStatus->value} to {$newStatus->value}",
            auditable: $project,
            oldValues: ['status' => $oldStatus->value],
            newValues: ['status' => $newStatus->value]
        );

        return $project;
    }
}
