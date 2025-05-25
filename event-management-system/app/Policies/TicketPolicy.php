<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Registration;
use App\Models\RegistrationField;
use App\Models\Ticket;

class TicketPolicy
{
    /**
     * Determine whether the user can view any tickets.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['ADMIN', 'EVENT_MANAGER']);
    }

    /**
     * Determine whether the user can view the ticket.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        return $user->hasRole(['admin', 'EVENT_MANAGER']) || 
               ($user->hasRole('visitor') && $ticket->is_active);
    }

    /**
     * Determine whether the user can create tickets.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['ADMIN', 'EVENT_MANAGER']);
    }

    /**
     * Determine whether the user can update the ticket.
     */
    public function update(User $user, Ticket $ticket): bool
    {
        return $user->hasRole(['ADMIN', 'EVENT_MANAGER']) || 
               $user->id === $ticket->created_by;
    }

    /**
     * Determine whether the user can delete the ticket.
     */
    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->hasRole(['ADMIN', 'EVENT_MANAGER']) || 
               $user->id === $ticket->created_by;
    }
}