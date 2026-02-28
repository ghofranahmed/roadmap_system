<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // غير مصادق
        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }
            return redirect()->guest(route('login'));
        }

        // ليس مسؤولاً (check role instead of is_admin)
        // Strict: Only admin and tech_admin can access /admin routes
        if (!in_array($user->role, ['admin', 'tech_admin'], true)) {
            // For API requests or JSON requests, return JSON
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Admin access required.',
                ], 403);
            }
            // For web requests, abort with 403 (will use custom error view)
            abort(403, 'Unauthorized. Admin access required.');
        }

        return $next($request);
    }
}