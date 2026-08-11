<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use App\Models\Driver;
use App\Models\TransportRequest;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsDriver
{
    /**
     * Handle an incoming request for Driver Terminal endpoints.
     * Enforces driver-scoped authentication, authorization, and IDOR protection.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // 1. Authentication Check: Ensure user is logged in
        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('driver-terminal.login');
        }

        // 2. Resolve Driver Master relationship from authenticated user session
        $driver = null;
        if (!empty($user->driver_id)) {
            $driver = Driver::find($user->driver_id);
        }
        if (!$driver && !empty($user->email)) {
            $driver = Driver::where('email', strtolower($user->email))->first();
        }
        if (!$driver && !empty($user->phone)) {
            $driver = Driver::where('phone_number', $user->phone)->first();
        }

        // Redirect unlinked user accounts to login page
        if (!$driver) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized driver access. Your account is not linked to a Driver Master profile.'], 403);
            }
            \Illuminate\Support\Facades\Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('driver-terminal.login')->withErrors(['driver_identifier' => 'Your account is not linked to a Driver Master profile.']);
        }

        // 3. Driver Status Verification (Reject suspended/inactive drivers)
        $status = strtolower((string) $driver->status);
        if (in_array($status, ['suspended', 'blocked', 'terminated'])) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Your driver account is currently suspended. Please contact the Transport Department.'], 403);
            }
            \Illuminate\Support\Facades\Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('driver-terminal.login')->withErrors(['driver_identifier' => 'Your driver account is currently suspended. Please contact the Transport Department.']);
        }

        if (in_array($status, ['inactive', 'deactivated']) || !empty($driver->deactivated_at)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Your driver account is currently inactive. Please contact the Transport Department.'], 403);
            }
            \Illuminate\Support\Facades\Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('driver-terminal.login')->withErrors(['driver_identifier' => 'Your driver account is currently inactive. Please contact the Transport Department.']);
        }

        // Attach active driver context to request attributes
        $request->attributes->set('current_driver', $driver);

        // 4. ROUTE AUTHORIZATION (IDOR Protection for Driver Terminal URL)
        // Verify that the route's driver_code matches the authenticated Driver Master profile
        $routeDriverCode = $request->route('driver_code') ?? $request->route('driver_id');
        if ($routeDriverCode !== null) {
            $cleanRouteCode = strtolower(trim((string) $routeDriverCode));
            $cleanDriverCode = strtolower(trim((string) $driver->driver_code));
            $cleanDriverId = (string) $driver->id;

            if ($cleanRouteCode !== $cleanDriverCode && $cleanRouteCode !== $cleanDriverId) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Access denied. This Driver Terminal belongs to another driver.'], 403);
                }
                return redirect()->route('driver-terminal.index', ['driver_code' => $cleanDriverCode])
                    ->with('error', 'Access denied. This Driver Terminal belongs to another driver.');
            }
        }

        // 5. DELIVERY RESOURCE AUTHORIZATION (IDOR Protection for specific delivery IDs)
        $deliveryId = $request->route('id') ?? $request->route('delivery') ?? $request->route('transportRequest');
        if ($deliveryId) {
            $delivery = $deliveryId instanceof TransportRequest
                ? $deliveryId
                : TransportRequest::find($deliveryId);

            if ($delivery && (int) $delivery->driver_id !== (int) $driver->id) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Access denied. You do not have permission to view or manage this delivery.'], 403);
                }
                return redirect()->route('driver-terminal.index', ['driver_code' => strtolower($driver->driver_code)])
                    ->with('error', 'Access denied. You do not have permission to view or manage this delivery.');
            }
        }

        return $next($request);
    }
}
