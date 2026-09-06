<?php

namespace App\Policies;

use App\Domains\Clients\Models\Client;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClientPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('clients.view') && ($user->hasRole('OWNER') || $user->hasRole('ADMIN'));
    }

    public function view(User $user, Client $client): bool
    {
        if ($user->can('clients.view')) {
            if ($user->hasRole('OWNER') || $user->hasRole('ADMIN')) {
                return true;
            }

            if ($user->hasRole('CLIENT')) {
                return $client->hasUser($user);
            }
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->can('clients.create') && ($user->hasRole('OWNER') || $user->hasRole('ADMIN'));
    }

    public function update(User $user, Client $client): bool
    {
        if ($user->can('clients.update')) {
            if ($user->hasRole('OWNER') || $user->hasRole('ADMIN')) {
                return true;
            }

            if ($user->hasRole('CLIENT')) {
                return $client->hasUser($user);
            }
        }

        return false;
    }

    public function delete(User $user, Client $client): bool
    {
        if ($user->can('clients.delete')) {
            return $user->hasRole('OWNER') || $user->hasRole('ADMIN');
        }

        return false;
    }
}
