<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Registration;
use App\Models\RegistrationField;
use App\Models\Ticket;



class RegistrationFieldPolicy
{
    /**
     * Determine whether the user can view any registration fields.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['ADMIN', 'EVENT_MANAGER']);
    }

    /**
     * Determine whether the user can view the registration field.
     */
    public function view(User $user, RegistrationField $registrationField): bool
    {
        return $user->hasRole(['ADMIN', 'EVENT_MANAGER']);
    }

    /**
     * Determine whether the user can create registration fields.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['ADMIN', 'EVENT_MANAGER']);
    }

    /**
     * Determine whether the user can update the registration field.
     */
    public function update(User $user, RegistrationField $registrationField): bool
    {
        return $user->hasRole(['ADMIN', 'EVENT_MANAGER']);
    }

    /**
     * Determine whether the user can delete the registration field.
     */
    public function delete(User $user, RegistrationField $registrationField): bool
    {
        return $user->hasRole(['ADMIN', 'EVENT_MANAGER']);
    }
}