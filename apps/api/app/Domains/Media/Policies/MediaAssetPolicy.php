<?php

namespace App\Domains\Media\Policies;

use App\Domains\Media\Models\MediaAsset;
use App\Domains\Projects\Models\Project;
use App\Models\User;

class MediaAssetPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('media.view');
    }

    public function view(User $user, MediaAsset $mediaAsset): bool
    {
        if (! $user->can('media.view')) {
            return false;
        }

        $project = $mediaAsset->project;
        if (! $project) {
            return false;
        }

        // Owner & Admin
        if ($user->hasRole(['OWNER', 'ADMIN'])) {
            return true;
        }

        // Worker
        if ($user->hasRole('WORKER')) {
            $workerProfile = $user->workerProfile;
            if (! $workerProfile) {
                return false;
            }

            $isAssigned = $project->assignments()
                ->where('worker_id', $workerProfile->id)
                ->where('is_active', true)
                ->exists();

            return $isAssigned && $mediaAsset->visibility->isWorkerVisible();
        }

        // Client
        if ($user->hasRole('CLIENT')) {
            $client = $project->client;
            if (! $client) {
                return false;
            }

            $belongsToClient = $client->users()->where('users.id', $user->id)->exists();

            return $belongsToClient && $mediaAsset->visibility->isClientVisible();
        }

        return false;
    }

    public function createForProject(User $user, Project $project): bool
    {
        if (! $user->can('media.create')) {
            return false;
        }

        if ($user->hasRole(['OWNER', 'ADMIN'])) {
            return true;
        }

        if ($user->hasRole('WORKER')) {
            $workerProfile = $user->workerProfile;
            if (! $workerProfile) {
                return false;
            }

            return $project->assignments()
                ->where('worker_id', $workerProfile->id)
                ->where('is_active', true)
                ->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->can('media.create');
    }

    public function update(User $user, MediaAsset $mediaAsset): bool
    {
        return $user->can('media.update');
    }

    public function delete(User $user, MediaAsset $mediaAsset): bool
    {
        return $user->can('media.delete');
    }

    public function release(User $user, MediaAsset $mediaAsset): bool
    {
        return $user->can('media.release') || $user->hasRole(['OWNER', 'ADMIN']);
    }
}
