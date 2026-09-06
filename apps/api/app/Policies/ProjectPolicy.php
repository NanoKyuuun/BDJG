<?php

namespace App\Policies;

use App\Domains\Projects\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('projects.view') && ($user->hasRole('OWNER') || $user->hasRole('ADMIN'));
    }

    public function view(User $user, Project $project): bool
    {
        if ($user->hasRole('OWNER') || $user->hasRole('ADMIN')) {
            return $user->can('projects.view');
        }

        if ($user->hasRole('CLIENT')) {
            return $project->client && $project->client->hasUser($user);
        }

        if ($user->hasRole('WORKER')) {
            $worker = $user->workerProfile;

            return $worker && $project->assignments()
                ->where('worker_id', $worker->id)
                ->where('is_active', true)
                ->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->can('projects.create') && ($user->hasRole('OWNER') || $user->hasRole('ADMIN'));
    }

    public function update(User $user, Project $project): bool
    {
        return $user->can('projects.update') && ($user->hasRole('OWNER') || $user->hasRole('ADMIN'));
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->can('projects.delete') && ($user->hasRole('OWNER') || $user->hasRole('ADMIN'));
    }
}
