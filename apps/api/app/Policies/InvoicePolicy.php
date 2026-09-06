<?php

namespace App\Policies;

use App\Domains\Billing\Models\Invoice;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvoicePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('invoices.view') && ($user->hasRole('OWNER') || $user->hasRole('ADMIN'));
    }

    public function view(User $user, Invoice $invoice): bool
    {
        if ($user->hasRole('OWNER') || $user->hasRole('ADMIN')) {
            return $user->can('invoices.view');
        }

        if ($user->hasRole('CLIENT')) {
            return $invoice->client && $invoice->client->hasUser($user);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->can('invoices.create') && ($user->hasRole('OWNER') || $user->hasRole('ADMIN'));
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $user->can('invoices.update') && ($user->hasRole('OWNER') || $user->hasRole('ADMIN'));
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $user->can('invoices.delete') && ($user->hasRole('OWNER') || $user->hasRole('ADMIN'));
    }
}
