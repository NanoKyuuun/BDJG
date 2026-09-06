<?php

namespace App\Policies;

use App\Domains\Payments\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentTransactionPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('payments.view') && ($user->hasRole('OWNER') || $user->hasRole('ADMIN'));
    }

    public function view(User $user, PaymentTransaction $transaction): bool
    {
        if ($user->hasRole('OWNER') || $user->hasRole('ADMIN')) {
            return $user->can('payments.view');
        }

        if ($user->hasRole('CLIENT')) {
            return $transaction->invoice && $transaction->invoice->client && $transaction->invoice->client->hasUser($user);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->can('payments.create');
    }

    public function update(User $user, PaymentTransaction $transaction): bool
    {
        return ($user->hasRole('OWNER') || $user->hasRole('ADMIN')) && $user->can('payments.create');
    }

    public function delete(User $user, PaymentTransaction $transaction): bool
    {
        return ($user->hasRole('OWNER') || $user->hasRole('ADMIN'));
    }
}
