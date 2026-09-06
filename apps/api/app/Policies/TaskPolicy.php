<?php

namespace App\Policies;

use App\Domains\Tasks\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaskPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('tasks.view') && ($user->hasRole('OWNER') || $user->hasRole('ADMIN'));
    }

    public function view(User $user, Task $task): bool
    {
        if ($user->hasRole('OWNER') || $user->hasRole('ADMIN')) {
            return $user->can('tasks.view');
        }

        if ($user->hasRole('WORKER')) {
            $worker = $user->workerProfile;

            return $worker
                && $task->assigned_worker_id === $worker->id
                && $task->project->assignments()
                    ->where('worker_id', $worker->id)
                    ->where('is_active', true)
                    ->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->can('tasks.create') && ($user->hasRole('OWNER') || $user->hasRole('ADMIN'));
    }

    public function update(User $user, Task $task): bool
    {
        if ($user->hasRole('OWNER') || $user->hasRole('ADMIN')) {
            return $user->can('tasks.update');
        }

        if ($user->hasRole('WORKER')) {
            $worker = $user->workerProfile;

            return $worker
                && $task->assigned_worker_id === $worker->id
                && $task->project->assignments()
                    ->where('worker_id', $worker->id)
                    ->where('is_active', true)
                    ->exists();
        }

        return false;
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->can('tasks.delete') && ($user->hasRole('OWNER') || $user->hasRole('ADMIN'));
    }
}
