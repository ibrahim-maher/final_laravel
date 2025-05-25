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
            return redirect()->route('login');
        }

        $user = auth()->user();
        dd([
        'role_value'  => $user->role,
        'role_length' => mb_strlen($user->role),
    ]);
        // Debug information (remove in production)
        Log::info('AdminMiddleware Check:', [
            'user_id' => $user->id,
            'email' => $user->email,
            'role_stored' => $user->role,
            'role_expected' => \App\Models\User::ROLE_ADMIN,
            'roles_match' => $user->role === \App\Models\User::ROLE_ADMIN,
            'isAdmin_method' => $user->isAdmin(),
            'url_requested' => $request->url(),
        ]);

        // Check if user is admin
        if (!$user->isAdmin()) {
            $errorMessage = sprintf(
                'Access denied. Admin privileges required. Your current role: "%s" (expected: "%s")',
                $user->role ?? 'NULL',
                \App\Models\User::ROLE_ADMIN
            );
            
            abort(403, $errorMessage);
        }

        return $next($request);
    }
}