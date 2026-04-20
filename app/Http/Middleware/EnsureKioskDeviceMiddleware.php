<?php

namespace App\Http\Middleware;

use App\Models\Kiosk;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureKioskDeviceMiddleware
{
    /**
     * Enforce that the authenticated token belongs to an active kiosk device.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $identity = $request->user();

        if (! $identity instanceof Kiosk) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden. Kiosk device token required.',
                'data' => null,
            ], 403);
        }

        if (! $identity->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Kiosk device is disabled.',
                'data' => null,
            ], 403);
        }

        return $next($request);
    }
}
