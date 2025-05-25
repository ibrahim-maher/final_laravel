<?php

namespace App\Policies;

use App\Models\Registration;
use App\Models\RegistrationField;
use App\Models\Ticket;
use App\Models\User;

class RegistrationPolicy
{
    /**
     * Determine whether the user can view any registrations.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['ADMIN', 'EVENT_MANAGER']);
    }

    /**
     * Determine whether the user can view the registration.
     */
    public function view(User $user, Registration $registration): bool
    {
        return $user->hasRole(['ADMIN', 'EVENT_MANAGER']) || 
               $user->id === $registration->user_id;
    }

    /**
     * Determine whether the user can create registrations.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['ADMIN', 'EVENT_MANAGER', 'VISITOR']);
    }

    /**
     * Determine whether the user can update the registration.
     */
    public function update(User $user, Registration $registration): bool
    {
        return $user->hasRole(['ADMIN', 'EVENT_MANAGER']) || 
               $user->id === $registration->user_id;
    }

    /**
     * Determine whether the user can delete the registration.
     */
    public function delete(User $user, Registration $registration): bool
    {
        return $user->hasRole(['ADMIN', 'EVENT_MANAGER']) || 
               $user->id === $registration->user_id;
    }

    /**
     * Determine whether the user can export registrations.
     */
    public function export(User $user): bool
    {
        return $user->hasRole(['ADMIN', 'EVENT_MANAGER']);
    }
}