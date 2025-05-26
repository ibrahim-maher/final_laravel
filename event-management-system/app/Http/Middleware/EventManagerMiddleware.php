<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class EventManagerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }
            return redirect()->route('login');
        }

        $user = auth()->user();
        
        // Admin users can access EVERYTHING - check admin first
        if ($user->isAdmin()) {
            return $next($request);
        }
        
        // Then check if user is event manager
        if ($user->isEventManager()) {
            return $next($request);
        }
        
        // Access denied
        $errorMessage = 'Access denied. Event Manager or Administrator privileges required.';
        
        Log::warning('Event Manager access denied', [
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
            'url' => $request->url()
        ]);
        
        if ($request->expectsJson()) {
            return response()->json(['error' => $errorMessage], 403);
        }
        
        abort(403, $errorMessage);
    }
}