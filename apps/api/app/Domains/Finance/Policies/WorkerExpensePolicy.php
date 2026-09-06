<?php

namespace App\Domains\Finance\Policies;

use App\Domains\Finance\Models\WorkerExpense;
use App\Models\User;

class WorkerExpensePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['OWNER', 'ADMIN', 'WORKER']);
    }

    public function view(User $user, WorkerExpense $expense): bool
    {
        if ($user->hasRole(['OWNER', 'ADMIN'])) {
            return true;
        }

        if ($user->hasRole('WORKER')) {
            return $user->workerProfile && $expense->worker_profile_id === $user->workerProfile->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['OWNER', 'ADMIN', 'WORKER']);
    }

    public function update(User $user, WorkerExpense $expense): bool
    {
        if ($user->hasRole(['OWNER', 'ADMIN'])) {
            return true;
        }

        if ($user->hasRole('WORKER')) {
            return $user->workerProfile &&
                $expense->worker_profile_id === $user->workerProfile->id &&
                $expense->status->value === 'SUBMITTED';
        }

        return false;
    }

    public function approve(User $user, WorkerExpense $expense): bool
    {
        return $user->hasRole(['OWNER', 'ADMIN']);
    }

    public function reject(User $user, WorkerExpense $expense): bool
    {
        return $user->hasRole(['OWNER', 'ADMIN']);
    }

    public function delete(User $user, WorkerExpense $expense): bool
    {
        return $user->hasRole(['OWNER', 'ADMIN']);
    }
}
