<?php

namespace App\Domains\Revisions\Actions;

use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Media\Models\MediaAsset;
use App\Domains\Projects\Models\Project;
use App\Domains\Revisions\Enums\RevisionRoundStatus;
use App\Domains\Revisions\Models\Revision;
use App\Domains\Revisions\Policies\RevisionPolicy;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class CreateRevisionRoundAction
{
    public function execute(
        Project $project,
        User $actor,
        ?MediaAsset $mediaAsset = null,
        ?string $title = null,
        ?string $notes = null
    ): Revision {
        $policy = app(RevisionPolicy::class);
        if (! $policy->createForProject($actor, $project)) {
            throw new AuthorizationException('User is not authorized to create a revision round for this project.');
        }

        $latestRound = Revision::where('project_id', $project->id)->max('round_number') ?? 0;
        $roundNumber = $latestRound + 1;

        $revision = Revision::create([
            'project_id' => $project->id,
            'media_asset_id' => $mediaAsset?->id,
            'round_number' => $roundNumber,
            'title' => $title ?? "Revision Round #{$roundNumber}",
            'requested_by_user_id' => $actor->id,
            'status' => RevisionRoundStatus::Open,
            'notes' => $notes,
        ]);

        AuditLogger::log(
            action: 'REVISION_ROUND_CREATED',
            description: "Revision round #{$roundNumber} created for Project '{$project->name}'",
            auditable: $revision,
            newValues: [
                'round_number' => $roundNumber,
                'media_asset_id' => $mediaAsset?->id,
                'title' => $revision->title,
            ]
        );

        return $revision;
    }
}
