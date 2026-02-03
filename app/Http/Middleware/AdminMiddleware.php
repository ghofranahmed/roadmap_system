<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if (!$request->user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بالدخول إلى هذه الصفحة',
            ], 403);
        }

        return $next($request);
    }
}
