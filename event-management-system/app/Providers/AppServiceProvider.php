<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Policies\UserPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Method 2a: Register policies manually if auto-discovery doesn't work
        Gate::policy(User::class, UserPolicy::class);
        
        // Method 2b: Register custom gates if needed
        Gate::define('manage-users', function (User $user) {
            return $user->canManageUsers();
        });
        
        Gate::define('manage-events', function (User $user) {
            return $user->canManageEvents();
        });
        
        Gate::define('export-users', function (User $user) {
            return $user->canManageUsers() || $user->canManageEvents();
        });
    }
}