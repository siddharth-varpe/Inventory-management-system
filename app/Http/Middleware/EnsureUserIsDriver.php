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
     * Enforces driver-scoped access and IDOR protection.
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

        // 1. Resolve Driver Master relationship from authenticated session
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

        // 2. Redirect unlinked accounts to login page instead of rendering 403 page
        if (!$driver) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized driver access. Your account is not linked to a Driver Master profile.'], 403);
            }
            \Illuminate\Support\Facades\Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('driver-terminal.login')->withErrors(['driver_identifier' => 'Your account is not linked to a Driver Master profile.']);
        }

        // 3. Driver status validation (Redirect suspended/inactive drivers to login page)
        if (in_array(strtolower($driver->status), ['suspended', 'inactive', 'deactivated']) || !empty($driver->deactivated_at)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Account access suspended or inactive. Please contact Transport Management.'], 403);
            }
            \Illuminate\Support\Facades\Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('driver-terminal.login')->withErrors(['driver_identifier' => 'Invalid Driver ID or password.']);
        }

        // Attach active driver context to request
        $request->attributes->set('current_driver', $driver);

        // 4. IDOR Protection: Prevent Driver A from accessing Driver B's delivery task
        $deliveryId = $request->route('id') ?? $request->route('delivery') ?? $request->route('transportRequest');
        if ($deliveryId) {
            $delivery = $deliveryId instanceof TransportRequest
                ? $deliveryId
                : TransportRequest::find($deliveryId);

            if ($delivery && (int) $delivery->driver_id !== (int) $driver->id) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Unauthorized access to requested delivery resource.'], 403);
                }
                return redirect()->route('driver-terminal.index')->with('error', 'Unauthorized access to requested delivery resource.');
            }
        }

        return $next($request);
    }
}
