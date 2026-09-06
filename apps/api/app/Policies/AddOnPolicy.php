<?php

namespace App\Policies;

use App\Domains\Catalog\Models\AddOn;
use App\Models\User;

class AddOnPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('add_ons.view');
    }

    public function view(User $user, AddOn $addOn): bool
    {
        return $user->can('add_ons.view');
    }

    public function create(User $user): bool
    {
        return $user->can('add_ons.create');
    }

    public function update(User $user, AddOn $addOn): bool
    {
        return $user->can('add_ons.update');
    }

    public function delete(User $user, AddOn $addOn): bool
    {
        return $user->can('add_ons.delete');
    }
}
