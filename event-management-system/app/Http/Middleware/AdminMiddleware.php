<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class AdminMiddleware
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
        
        // Check if user is admin
        if (!$user->isAdmin()) {
            $errorMessage = 'Access denied. Administrator privileges required.';
            
            Log::warning('Admin access denied', [
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

        return $next($request);
    }
}