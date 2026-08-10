<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use App\Models\Driver;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsDriver
{
    /**
     * Handle an incoming request for Driver Terminal endpoints.
     * Enforces driver-scoped access so drivers can only view their own assignments.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('driver-terminal.login');
        }

        // Identify associated driver profile
        $driver = Driver::where('email', $user->email)
            ->orWhere('phone_number', $user->phone)
            ->orWhere('id', $user->driver_id ?? null)
            ->first();

        // Admin/Superusers allowed for debugging and operations override
        if (!$driver && !in_array($user->role ?? '', ['admin', 'super_admin', 'transport_manager'])) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized driver access.'], 403);
            }
            abort(403, 'Unauthorized Driver Access. Your account is not linked to a Driver Master profile.');
        }

        // Attach active driver context to request
        if ($driver) {
            $request->attributes->set('current_driver', $driver);
        }

        return $next($request);
    }
}
