<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EventManagerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        
        // Check if user is admin OR event manager
        if (!($user->isAdmin() || $user->isEventManager())) {
            abort(403, 'Access denied. Event Manager or Admin privileges required.');
        }

        return $next($request);
    }
}