<?php

namespace App\Domains\Orders\Policies;

use App\Domains\Orders\Enums\ServiceOrderStatus;
use App\Domains\Orders\Models\ServiceOrder;
use App\Models\User;

class ServiceOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('OWNER')
            || $user->hasRole('ADMIN')
            || $user->hasRole('CLIENT');
    }

    public function view(User $user, ServiceOrder $order): bool
    {
        if ($user->hasRole('OWNER') || $user->hasRole('ADMIN')) {
            return true;
        }

        if ($user->hasRole('CLIENT')) {
            return $order->client && $order->client->hasUser($user);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('OWNER')
            || $user->hasRole('ADMIN')
            || $user->hasRole('CLIENT');
    }

    public function update(User $user, ServiceOrder $order): bool
    {
        if ($user->hasRole('OWNER') || $user->hasRole('ADMIN')) {
            return true;
        }

        if ($user->hasRole('CLIENT')) {
            return $order->client
                && $order->client->hasUser($user)
                && ! in_array($order->status, [
                    ServiceOrderStatus::Paid,
                    ServiceOrderStatus::ProjectCreated,
                    ServiceOrderStatus::Cancelled,
                ]);
        }

        return false;
    }

    public function delete(User $user, ServiceOrder $order): bool
    {
        if ($user->hasRole('OWNER') || $user->hasRole('ADMIN')) {
            return true;
        }

        if ($user->hasRole('CLIENT')) {
            return $order->client
                && $order->client->hasUser($user)
                && $order->status === ServiceOrderStatus::Draft;
        }

        return false;
    }
}
