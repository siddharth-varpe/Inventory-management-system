<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\TransportTrip;
use App\Models\TransportRequest;
use App\Models\Driver;
use App\Domain\Transport\DriverExecutionEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DriverTerminalController extends Controller
{
    public function __construct(
        protected DriverExecutionEngine $executionEngine
    ) {}

    /**
     * Driver Terminal Workspace (/driver)
     */
    public function index(Request $request): View
    {
        // Identify Driver Context from Authenticated Session / Request attributes
        $currentDriver = $request->attributes->get('current_driver');

        if (!$currentDriver && auth()->check()) {
            $user = auth()->user();
            $currentDriver = Driver::where('email', $user->email)
                ->orWhere('phone_number', $user->phone ?? null)
                ->orWhere('id', $user->driver_id ?? null)
                ->first();
        }

        $driverId = $currentDriver?->id;

        $assignedCount = $driverId ? TransportRequest::where('driver_id', $driverId)->whereIn('status', ['driver_vehicle_assigned', 'assigned'])->count() : 0;
        $dispatchedCount = $driverId ? TransportRequest::where('driver_id', $driverId)->whereIn('status', ['dispatched', 'in_transit', 'arrived'])->count() : 0;
        $completedCount = $driverId ? TransportRequest::where('driver_id', $driverId)->whereIn('status', ['delivered', 'completed'])->count() : 0;

        $activeDelivery = $driverId ? TransportRequest::with(['salesOrder.customer', 'vehicle'])
            ->where('driver_id', $driverId)
            ->whereIn('status', ['driver_vehicle_assigned', 'assigned', 'dispatched', 'in_transit', 'arrived'])
            ->latest()
            ->first() : null;

        $assignedVehicle = $activeDelivery?->vehicle ?? ($driverId ? \App\Models\Vehicle::find($currentDriver?->current_assignment) : null);

        return view('driver-terminal.home.index', [
            'currentDriver' => $currentDriver,
            'assignedCount' => $assignedCount,
            'dispatchedCount' => $dispatchedCount,
            'completedCount' => $completedCount,
            'activeDelivery' => $activeDelivery,
            'assignedVehicle' => $assignedVehicle,
        ]);
    }

    /**
     * Driver Accepts Trip
     */
    public function acceptTrip(Request $request, TransportTrip $transportTrip): RedirectResponse
    {
        $driverId = $request->input('driver_id', $transportTrip->driver_id);
        $driver = Driver::findOrFail($driverId);

        try {
            $this->executionEngine->acceptTrip($transportTrip, $driver, auth()->id() ?? 1);
            return redirect()->back()->with('success', "Trip #{$transportTrip->trip_number} Accepted! Safe driving.");
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Driver Updates Operational Delivery Status
     */
    public function updateStatus(Request $request, TransportRequest $transportRequest): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|string',
            'notes' => 'nullable|string',
            'driver_id' => 'nullable|integer|exists:drivers,id',
        ]);

        $driverId = $validated['driver_id'] ?? $transportRequest->driver_id ?? 1;
        $driver = Driver::findOrFail($driverId);

        try {
            $this->executionEngine->updateDeliveryStatus($transportRequest, $validated['status'], $validated['notes'] ?? null, $driver, auth()->id() ?? 1);
            return redirect()->back()->with('success', "Delivery status updated to '" . (DriverExecutionEngine::ALLOWED_STATUSES[$validated['status']] ?? $validated['status']) . "'.");
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Driver Terminal Live Sync API Endpoint
     */
    public function liveSync(Request $request): JsonResponse
    {
        $driverId = $request->input('driver_id', 1);
        $driver = Driver::find($driverId);

        if (!$driver) {
            return response()->json(['success' => false, 'message' => 'Driver not found']);
        }

        $activeTrips = TransportTrip::with(['transportRequests', 'vehicle', 'dispatchManifest'])
            ->where('driver_id', $driver->id)
            ->whereIn('status', ['created', 'ready', 'dispatched'])
            ->get()
            ->map(function ($t) {
                return [
                    'id' => $t->id,
                    'trip_number' => $t->trip_number,
                    'manifest_number' => $t->dispatchManifest->manifest_number ?? 'MAN-Pending',
                    'vehicle_number' => $t->vehicle->vehicle_number ?? 'N/A',
                    'destination_city' => $t->destination_city,
                    'status_label' => $t->status_label,
                    'orders_count' => $t->transportRequests->count(),
                ];
            });

        return response()->json([
            'success' => true,
            'driver_name' => $driver->driver_name,
            'driver_status' => $driver->status,
            'active_trips_count' => count($activeTrips),
            'trips' => $activeTrips,
        ]);
    }

    /**
     * Phase 1 — Driver Login Page (NO OTP)
     */
    public function login(): View
    {
        return view('driver-terminal.auth.login');
    }

    /**
     * Phase 1 — Driver Authentication Action
     */
    public function authenticate(Request $request): RedirectResponse|JsonResponse
    {
        $credentials = $request->validate([
            'driver_identifier' => 'required|string|max:100',
            'password' => 'required|string',
        ]);

        $identifier = trim($credentials['driver_identifier']);
        $password = $credentials['password'];

        // 1. Locate Driver Record in Driver Master by driver_code, email, or phone_number
        $driver = Driver::where('driver_code', $identifier)
            ->orWhere('email', strtolower($identifier))
            ->orWhere('phone_number', $identifier)
            ->first();

        // 2. Generic Error if driver does not exist (Prevents Driver ID enumeration)
        if (!$driver) {
            return $this->loginFailedResponse($request, 'Invalid login credentials.');
        }

        // 3. Driver Status Verification (Reject suspended/inactive/deactivated drivers)
        if (in_array(strtolower($driver->status), ['suspended', 'inactive', 'deactivated']) || !empty($driver->deactivated_at)) {
            return $this->loginFailedResponse($request, 'Account access suspended or inactive. Please contact Transport Management.');
        }

        // 4. Match or Link User Record for Laravel Session Authentication
        $user = \App\Models\User::where('email', $driver->email)->first();
        if (!$user && !empty($driver->phone_number)) {
            $user = \App\Models\User::where('phone', $driver->phone_number)->first();
        }

        if (!$user) {
            $user = \App\Models\User::create([
                'name' => $driver->driver_name,
                'email' => $driver->email ?: (strtolower($driver->driver_code) . '@stockmanager.com'),
                'password' => \Illuminate\Support\Facades\Hash::make($password),
                'status' => 'active',
            ]);
        }

        // 5. Password Verification
        if (!\Illuminate\Support\Facades\Hash::check($password, $user->password)) {
            return $this->loginFailedResponse($request, 'Invalid login credentials.');
        }

        // 6. Log in user securely & regenerate session
        \Illuminate\Support\Facades\Auth::login($user, (bool) $request->input('remember', false));
        $request->session()->regenerate();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Driver authenticated successfully.',
                'driver_code' => $driver->driver_code,
                'driver_name' => $driver->driver_name,
                'redirect_url' => route('driver-terminal.index'),
            ]);
        }

        return redirect()->intended(route('driver-terminal.index'));
    }

    /**
     * Phase 1 — Secure Driver Logout
     */
    public function logout(Request $request): RedirectResponse|JsonResponse
    {
        \Illuminate\Support\Facades\Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Logged out successfully.']);
        }

        return redirect()->route('driver-terminal.login')->with('success', 'You have been logged out.');
    }

    private function loginFailedResponse(Request $request, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $message], 422);
        }

        return redirect()->back()
            ->withErrors(['driver_identifier' => $message])
            ->withInput($request->only('driver_identifier'));
    }

    /**
     * Phase 0 Foundational Stub: Deliveries List
     */
    public function deliveries(Request $request): View
    {
        return view('driver-terminal.deliveries.index');
    }

    /**
     * Phase 0 Foundational Stub: Delivery Details
     */
    public function showDelivery(int $id): View
    {
        return view('driver-terminal.deliveries.show', ['deliveryId' => $id]);
    }

    /**
     * Phase 2 — Driver Profile Page (Read-Only)
     */
    public function profile(Request $request): View
    {
        $currentDriver = $request->attributes->get('current_driver');

        if (!$currentDriver && auth()->check()) {
            $user = auth()->user();
            $currentDriver = Driver::where('email', $user->email)
                ->orWhere('phone_number', $user->phone ?? null)
                ->orWhere('id', $user->driver_id ?? null)
                ->first();
        }

        $driverId = $currentDriver?->id;
        $activeDelivery = $driverId ? TransportRequest::with('vehicle')
            ->where('driver_id', $driverId)
            ->whereIn('status', ['driver_vehicle_assigned', 'assigned', 'dispatched', 'in_transit', 'arrived'])
            ->latest()
            ->first() : null;

        $assignedVehicle = $activeDelivery?->vehicle ?? ($driverId ? \App\Models\Vehicle::find($currentDriver?->current_assignment) : null);

        return view('driver-terminal.profile.index', [
            'currentDriver' => $currentDriver,
            'assignedVehicle' => $assignedVehicle,
        ]);
    }

    /**
     * Phase 0 Foundational Stub: Notifications
     */
    public function notifications(): View
    {
        return view('driver-terminal.notifications.index');
    }
}
