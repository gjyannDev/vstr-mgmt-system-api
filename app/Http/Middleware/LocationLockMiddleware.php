<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LocationLockMiddleware
{
    /**
     * Enforce server-side location assignment and ignore client-provided location_id.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'data' => null,
            ], 401);
        }

        if ($user->location_id === null) {
            return response()->json([
                'success' => false,
                'message' => 'No assigned location found for this account.',
                'data' => null,
            ], 403);
        }

        $request->merge([
            'location_id' => (int) $user->location_id,
        ]);

        return $next($request);
    }
}
