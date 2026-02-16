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
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // Check if user has one of the required roles
        if (!in_array($user->role, $roles)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Required role: ' . implode(' or ', $roles),
            ], 403);
        }

        return $next($request);
    }
}
