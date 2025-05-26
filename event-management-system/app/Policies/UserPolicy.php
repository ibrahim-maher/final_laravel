<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->canManageUsers() || $user->canManageEvents();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // Users can view their own profile
        if ($user->id === $model->id) {
            return true;
        }

        // Admins can view any user
        if ($user->canManageUsers()) {
            return true;
        }

        // Event managers can view users assigned to their events
        if ($user->canManageEvents()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->canManageUsers();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Users can update their own profile (limited fields)
        if ($user->id === $model->id) {
            return true;
        }

        // Only admins can update other users
        return $user->canManageUsers();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Users cannot delete themselves
        if ($user->id === $model->id) {
            return false;
        }

        // Only admins can delete users
        return $user->canManageUsers();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->canManageUsers();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->canManageUsers();
    }

    /**
     * Determine whether the user can manage events for the model.
     */
    public function manageEvents(User $user, User $model): bool
    {
        return $user->canManageUsers() || $user->canManageEvents();
    }

    /**
     * Determine whether the user can bulk update models.
     */
    public function bulkUpdate(User $user): bool
    {
        return $user->canManageUsers();
    }

    /**
     * Determine whether the user can export models.
     */
    public function export(User $user): bool
    {
        return $user->canManageUsers() || $user->canManageEvents();
    }
}