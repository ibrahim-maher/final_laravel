<?php

namespace App\Observers;

use App\Models\Event;
use App\Models\RegistrationField;

class EventObserver
{
    /**
     * Handle the Event "created" event.
     */
    public function created(Event $event): void
    {
        // Automatically create default registration fields for new events
        RegistrationField::createDefaultFields($event->id);
    }

    /**
     * Handle the Event "updated" event.
     */
    public function updated(Event $event): void
    {
        // Handle is_active logic - only one event can be active at a time
        if ($event->is_active && $event->wasChanged('is_active')) {
            Event::where('id', '!=', $event->id)
                 ->update(['is_active' => false]);
        }
    }

    /**
     * Handle the Event "deleted" event.
     */
    public function deleted(Event $event): void
    {
        // Clean up related registration fields (handled by cascade)
        // Clean up uploaded logos if they exist
        if ($event->logo) {
            \Storage::disk('public')->delete($event->logo);
        }
    }

    /**
     * Handle the Event "restored" event.
     */
    public function restored(Event $event): void
    {
        // Recreate default registration fields if they don't exist
        if ($event->registrationFields()->count() === 0) {
            RegistrationField::createDefaultFields($event->id);
        }
    }

    /**
     * Handle the Event "force deleted" event.
     */
    public function forceDeleted(Event $event): void
    {
        // Clean up uploaded logos if they exist
        if ($event->logo) {
            \Storage::disk('public')->delete($event->logo);
        }
    }
}