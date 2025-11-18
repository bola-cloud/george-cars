<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user || ! data_get($user, 'is_admin')) {
            return response()->json([
                'message' => 'Forbidden: admin only',
                'status' => false,
                'data' => null,
            ], 403);
        }

        return $next($request);
    }
}
