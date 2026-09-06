<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectAssignmentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('assignments.view');
    }

    public function create(User $user): bool
    {
        return $user->can('assignments.create');
    }

    public function delete(User $user): bool
    {
        return $user->can('assignments.delete');
    }
}
