<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }
            return redirect()->route('login');
        }

        $user = auth()->user();
        $userRole = $user->role ?? 'visitor';
        
        // Check if user has one of the required roles
        if (in_array($userRole, $roles)) {
            return $next($request);
        }
        
        // Access denied
        $allowedRoles = implode(', ', $roles);
        $errorMessage = "Access denied. Required role: {$allowedRoles}. Your role: {$userRole}";
        
        Log::warning('Role access denied', [
            'user_id' => $user->id,
            'email' => $user->email,
            'user_role' => $userRole,
            'required_roles' => $roles,
            'url' => $request->url()
        ]);
        
        if ($request->expectsJson()) {
            return response()->json(['error' => $errorMessage], 403);
        }
        
        abort(403, $errorMessage);
    }
}