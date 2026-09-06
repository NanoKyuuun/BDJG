<?php

namespace App\Policies;

use App\Domains\Workers\Models\WorkerProfile;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class WorkerPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('workers.view') && ($user->hasRole('OWNER') || $user->hasRole('ADMIN'));
    }

    public function view(User $user, WorkerProfile $worker): bool
    {
        if ($user->hasRole('OWNER') || $user->hasRole('ADMIN')) {
            return $user->can('workers.view');
        }

        if ($user->hasRole('WORKER')) {
            return $worker->user_id === $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->can('workers.create') && ($user->hasRole('OWNER') || $user->hasRole('ADMIN'));
    }

    public function update(User $user, WorkerProfile $worker): bool
    {
        if ($user->hasRole('OWNER') || $user->hasRole('ADMIN')) {
            return $user->can('workers.update');
        }

        if ($user->hasRole('WORKER')) {
            return $worker->user_id === $user->id;
        }

        return false;
    }

    public function delete(User $user, WorkerProfile $worker): bool
    {
        return $user->can('workers.delete') && ($user->hasRole('OWNER') || $user->hasRole('ADMIN'));
    }
}
