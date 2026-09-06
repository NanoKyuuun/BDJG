<?php

namespace App\Policies;

use App\Domains\Schedules\Models\Schedule;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SchedulePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('schedules.view') && ($user->hasRole('OWNER') || $user->hasRole('ADMIN'));
    }

    public function view(User $user, Schedule $schedule): bool
    {
        if ($user->hasRole('OWNER') || $user->hasRole('ADMIN')) {
            return $user->can('schedules.view');
        }

        if ($user->hasRole('CLIENT')) {
            return $schedule->project && $schedule->project->client && $schedule->project->client->hasUser($user);
        }

        if ($user->hasRole('WORKER')) {
            $worker = $user->workerProfile;

            return $worker && $schedule->project && $schedule->project->assignments()
                ->where('worker_id', $worker->id)
                ->where('is_active', true)
                ->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->can('schedules.create') && ($user->hasRole('OWNER') || $user->hasRole('ADMIN'));
    }

    public function update(User $user, Schedule $schedule): bool
    {
        return $user->can('schedules.update') && ($user->hasRole('OWNER') || $user->hasRole('ADMIN'));
    }

    public function delete(User $user, Schedule $schedule): bool
    {
        return $user->can('schedules.delete') && ($user->hasRole('OWNER') || $user->hasRole('ADMIN'));
    }
}
