<?php

namespace App\Policies;

use App\Domains\Commercial\Models\Quotation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class QuotationPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('quotations.view') && ($user->hasRole('OWNER') || $user->hasRole('ADMIN'));
    }

    public function view(User $user, Quotation $quotation): bool
    {
        if ($user->hasRole('OWNER') || $user->hasRole('ADMIN')) {
            return $user->can('quotations.view');
        }

        if ($user->hasRole('CLIENT')) {
            return $quotation->client && $quotation->client->hasUser($user);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->can('quotations.create') && ($user->hasRole('OWNER') || $user->hasRole('ADMIN'));
    }

    public function update(User $user, Quotation $quotation): bool
    {
        return $user->can('quotations.update') && ($user->hasRole('OWNER') || $user->hasRole('ADMIN'));
    }

    public function delete(User $user, Quotation $quotation): bool
    {
        return $user->can('quotations.delete') && ($user->hasRole('OWNER') || $user->hasRole('ADMIN'));
    }
}
