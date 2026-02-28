<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * 
     * Usage: role:admin or role:tech_admin or role:admin,tech_admin
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        // غير مصادق
        if (!$user) {
            // For API requests or JSON requests, return JSON
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }
            // For web requests, redirect to login
            return redirect()->guest(route('login'));
        }

        // Check if user has one of the required roles
        if (!in_array($user->role, $roles)) {
            $requiredRoles = implode(' or ', $roles);
            
            // For API requests or JSON requests, return JSON
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Required role: ' . $requiredRoles,
                ], 403);
            }
            
            // For web requests, abort with 403 (will use custom error view)
            abort(403, 'Unauthorized. Required role: ' . $requiredRoles);
        }

        return $next($request);
    }
}
