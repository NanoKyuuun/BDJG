<?php

namespace App\Policies;

use App\Domains\CRM\Models\Inquiry;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InquiryPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('inquiries.view') && ($user->hasRole('OWNER') || $user->hasRole('ADMIN'));
    }

    public function view(User $user, Inquiry $inquiry): bool
    {
        return $user->can('inquiries.view') && ($user->hasRole('OWNER') || $user->hasRole('ADMIN'));
    }

    public function create(User $user): bool
    {
        return $user->can('inquiries.create') && ($user->hasRole('OWNER') || $user->hasRole('ADMIN'));
    }

    public function update(User $user, Inquiry $inquiry): bool
    {
        return $user->can('inquiries.update') && ($user->hasRole('OWNER') || $user->hasRole('ADMIN'));
    }

    public function delete(User $user, Inquiry $inquiry): bool
    {
        return $user->can('inquiries.delete') && ($user->hasRole('OWNER') || $user->hasRole('ADMIN'));
    }
}
