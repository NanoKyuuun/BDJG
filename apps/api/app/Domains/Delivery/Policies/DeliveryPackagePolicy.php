<?php

namespace App\Domains\Delivery\Policies;

use App\Domains\Delivery\Models\DeliveryPackage;
use App\Models\User;

class DeliveryPackagePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, DeliveryPackage $package): bool
    {
        if ($user->hasRole(['OWNER', 'ADMIN'])) {
            return true;
        }

        if ($user->hasRole('CLIENT')) {
            $client = $package->project->client;
            $belongsToClient = $client && $client->users()->where('users.id', $user->id)->exists();

            return $belongsToClient && $package->status->isAvailable() && ! $package->isExpired();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['OWNER', 'ADMIN']);
    }

    public function download(User $user, DeliveryPackage $package): bool
    {
        return $this->view($user, $package);
    }

    public function delete(User $user, DeliveryPackage $package): bool
    {
        return $user->hasRole(['OWNER', 'ADMIN']);
    }
}
