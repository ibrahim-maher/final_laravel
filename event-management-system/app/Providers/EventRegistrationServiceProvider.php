<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Event;
use App\Models\Registration;
use App\Models\RegistrationField;
use App\Models\Ticket;
use App\Observers\EventObserver;
use App\Policies\RegistrationPolicy;
use App\Policies\RegistrationFieldPolicy;
use App\Policies\TicketPolicy;

class EventRegistrationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register model observers
        Event::observe(EventObserver::class);

        // Register policies
        Gate::policy(Registration::class, RegistrationPolicy::class);
        Gate::policy(RegistrationField::class, RegistrationFieldPolicy::class);
        Gate::policy(Ticket::class, TicketPolicy::class);

        // Register custom validation rules
        $this->registerCustomValidationRules();

        // Register view composers
        $this->registerViewComposers();
    }

    /**
     * Register custom validation rules.
     */
    protected function registerCustomValidationRules(): void
    {
        // Custom validation rule for checking ticket availability
        \Validator::extend('ticket_available', function ($attribute, $value, $parameters, $validator) {
            $ticket = Ticket::find($value);
            return $ticket && $ticket->canRegister();
        });

        \Validator::replacer('ticket_available', function ($message, $attribute, $rule, $parameters) {
            return 'The selected ticket is not available for registration.';
        });

        // Custom validation rule for unique event registration
        \Validator::extend('unique_event_registration', function ($attribute, $value, $parameters, $validator) {
            $userId = $parameters[0] ?? null;
            $eventId = $value;
            
            if (!$userId || !$eventId) {
                return true; // Let other validation handle required fields
            }
            
            return !Registration::where('user_id', $userId)
                                ->where('event_id', $eventId)
                                ->exists();
        });

        \Validator::replacer('unique_event_registration', function ($message, $attribute, $rule, $parameters) {
            return 'User is already registered for this event.';
        });
    }

    /**
     * Register view composers.
     */
    protected function registerViewComposers(): void
    {
        // Share registration field types with all views
        view()->composer('*', function ($view) {
            $view->with('registrationFieldTypes', RegistrationField::FIELD_TYPES);
        });

        // Share registration statuses with registration views
        view()->composer('registrations.*', function ($view) {
            $view->with('registrationStatuses', Registration::STATUSES);
        });
    }
}

// Add this to your AppServiceProvider or create a new one
// Don't forget to register it in config/app.php providers array:
// App\Providers\EventRegistrationServiceProvider::class,