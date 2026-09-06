<?php

namespace App\Domains\Revisions\Policies;

use App\Domains\Projects\Models\Project;
use App\Domains\Revisions\Models\Revision;
use App\Models\User;

class RevisionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Revision $revision): bool
    {
        $project = $revision->project;
        if (! $project) {
            return false;
        }

        if ($user->hasRole(['OWNER', 'ADMIN'])) {
            return true;
        }

        if ($user->hasRole('WORKER')) {
            $worker = $user->workerProfile;
            return $worker && $project->assignments()->where('worker_id', $worker->id)->where('is_active', true)->exists();
        }

        if ($user->hasRole('CLIENT')) {
            $client = $project->client;
            return $client && $client->users()->where('users.id', $user->id)->exists();
        }

        return false;
    }

    public function createForProject(User $user, Project $project): bool
    {
        if ($user->hasRole(['OWNER', 'ADMIN'])) {
            return true;
        }

        if ($user->hasRole('CLIENT')) {
            $client = $project->client;
            return $client && $client->users()->where('users.id', $user->id)->exists();
        }

        if ($user->hasRole('WORKER')) {
            $worker = $user->workerProfile;
            return $worker && $project->assignments()->where('worker_id', $worker->id)->where('is_active', true)->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Revision $revision): bool
    {
        return $this->view($user, $revision);
    }

    public function resolve(User $user, Revision $revision): bool
    {
        if ($user->hasRole(['OWNER', 'ADMIN'])) {
            return true;
        }

        if ($user->hasRole('WORKER')) {
            $worker = $user->workerProfile;
            $project = $revision->project;
            return $worker && $project && $project->assignments()->where('worker_id', $worker->id)->where('is_active', true)->exists();
        }

        return false;
    }

    public function delete(User $user, Revision $revision): bool
    {
        return $user->hasRole(['OWNER', 'ADMIN']);
    }
}
