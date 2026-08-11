<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\TransportTrip;
use App\Models\TransportRequest;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\User;
use App\Domain\Transport\DriverExecutionEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DriverTerminalController extends Controller
{
    public function __construct(
        protected DriverExecutionEngine $executionEngine
    ) {}

    /**
     * Driver Terminal Workspace (/driver-terminal/{driver_code})
     */
    public function index(Request $request, ?string $driver_code = null): View
    {
        /** @var Driver|null $currentDriver */
        $currentDriver = $request->attributes->get('current_driver');
        $driverId = $currentDriver?->id;

        $assignedCount = $driverId ? TransportRequest::where('driver_id', $driverId)->whereIn('status', ['driver_vehicle_assigned', 'assigned'])->count() : 0;
        $dispatchedCount = $driverId ? TransportRequest::where('driver_id', $driverId)->whereIn('status', ['dispatched', 'in_transit', 'arrived'])->count() : 0;
        $completedCount = $driverId ? TransportRequest::where('driver_id', $driverId)->whereIn('status', ['delivered', 'completed'])->count() : 0;

        $activeDelivery = $driverId ? TransportRequest::with(['salesOrder.customer', 'vehicle'])
            ->where('driver_id', $driverId)
            ->whereIn('status', ['driver_vehicle_assigned', 'assigned', 'dispatched', 'in_transit', 'arrived'])
            ->latest()
            ->first() : null;

        $assignedVehicle = $activeDelivery?->vehicle ?? ($driverId ? Vehicle::find($currentDriver?->current_assignment) : null);

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
     * Driver Terminal Login Page (GET /driver-terminal/login)
     */
    public function login(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();
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

            if ($driver && !in_array(strtolower((string) $driver->status), ['suspended', 'blocked', 'inactive', 'deactivated'])) {
                return redirect()->route('driver-terminal.index', ['driver_code' => strtolower($driver->driver_code)]);
            }
        }

        return view('driver-terminal.auth.login');
    }

    /**
     * Driver Authentication Action (POST /driver-terminal/login)
     */
    public function authenticate(Request $request): RedirectResponse|JsonResponse
    {
        $rawDriverId = $request->input('driver_id') ?? $request->input('driver_code') ?? $request->input('driver_identifier');
        $rawMobile = $request->input('mobile_number') ?? $request->input('phone_number') ?? $request->input('mobile') ?? $request->input('phone');

        if (empty($rawDriverId) && empty($rawMobile)) {
            return $this->loginFailedResponse($request, 'Please enter your Driver ID and 10-digit mobile number.');
        }

        if (empty($rawDriverId)) {
            return $this->loginFailedResponse($request, 'Please enter your Driver ID.');
        }

        if (empty($rawMobile)) {
            return $this->loginFailedResponse($request, 'Please enter your 10-digit mobile number.');
        }

        $inputDriverCode = trim((string) $rawDriverId);
        $inputMobileDigits = preg_replace('/[^0-9]/', '', (string) $rawMobile);
        if (strlen($inputMobileDigits) > 10) {
            $inputMobileDigits = substr($inputMobileDigits, -10);
        }

        if (strlen($inputMobileDigits) < 10) {
            return $this->loginFailedResponse($request, 'Please enter your 10-digit mobile number.');
        }

        // 1. Find Driver Master by driver_code (Exact or case-insensitive match)
        $driver = Driver::whereRaw('LOWER(driver_code) = ?', [strtolower($inputDriverCode)])->first();

        // 2. Error if driver does not exist
        if (!$driver) {
            return $this->loginFailedResponse($request, 'Driver ID not found. Please check your Driver ID.');
        }

        // 3. Verify that the supplied mobile number belongs to that EXACT Driver Master record
        $dbMobileDigits = preg_replace('/[^0-9]/', '', (string) $driver->phone_number);
        if (strlen($dbMobileDigits) > 10) {
            $dbMobileDigits = substr($dbMobileDigits, -10);
        }

        if (empty($inputMobileDigits) || empty($dbMobileDigits) || $inputMobileDigits !== $dbMobileDigits) {
            return $this->loginFailedResponse($request, 'The mobile number does not match this Driver ID.');
        }

        // 4. Driver Status Verification (Reject suspended/inactive/deactivated drivers)
        $status = strtolower((string) $driver->status);
        if (in_array($status, ['suspended', 'blocked', 'terminated'])) {
            return $this->loginFailedResponse($request, 'Your driver account is currently suspended. Please contact the Transport Department.');
        }
        if (in_array($status, ['inactive', 'deactivated']) || !empty($driver->deactivated_at)) {
            return $this->loginFailedResponse($request, 'Your driver account is currently inactive. Please contact the Transport Department.');
        }

        // 5. Match or Link User Record for Laravel Session Authentication
        $user = User::where('driver_id', $driver->id)->first();
        if (!$user && !empty($driver->email)) {
            $user = User::where('email', strtolower($driver->email))->first();
        }
        if (!$user && !empty($driver->phone_number)) {
            $user = User::where('phone', $driver->phone_number)->first();
        }

        if (!$user) {
            $user = User::create([
                'name' => $driver->driver_name,
                'email' => $driver->email ?: (strtolower($driver->driver_code) . '@stockmanager.com'),
                'password' => Hash::make('DriverSecurePass2026!'),
                'driver_id' => $driver->id,
                'status' => 'active',
            ]);
        } elseif ((int) $user->driver_id !== (int) $driver->id) {
            $user->driver_id = $driver->id;
            $user->save();
        }

        // 6. Log in user securely & regenerate session ID (Protects against session fixation)
        Auth::login($user, (bool) $request->input('remember', false));
        $request->session()->regenerate();

        $canonicalDriverCode = strtolower((string) $driver->driver_code);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Welcome back, ' . $driver->driver_name,
                'driver_code' => $driver->driver_code,
                'driver_name' => $driver->driver_name,
                'redirect_url' => route('driver-terminal.index', ['driver_code' => $canonicalDriverCode]),
            ]);
        }

        return redirect()->intended(route('driver-terminal.index', ['driver_code' => $canonicalDriverCode]))
            ->with('success', 'Welcome back, ' . $driver->driver_name);
    }

    /**
     * Secure Driver Logout (POST /driver-terminal/logout)
     */
    public function logout(Request $request): RedirectResponse|JsonResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Logged out successfully.']);
        }

        return redirect()->route('driver-terminal.login')->with('success', 'You have been logged out.');
    }

    /**
     * Driver Delivery Queue (/driver-terminal/{driver_code}/deliveries)
     */
    public function deliveries(Request $request, ?string $driver_code = null): View
    {
        /** @var Driver $currentDriver */
        $currentDriver = $request->attributes->get('current_driver');
        $driverId = $currentDriver->id;
        $search = trim((string) $request->query('search', ''));

        $query = TransportRequest::with(['salesOrder.customer', 'vehicle', 'driver'])
            ->where('driver_id', $driverId)
            ->whereIn('status', ['driver_vehicle_assigned', 'assigned', 'dispatched']);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('request_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('delivery_address', 'like', "%{$search}%")
                    ->orWhereHas('salesOrder', function ($sq) use ($search) {
                        $sq->where('order_number', 'like', "%{$search}%")
                            ->orWhereHas('customer', function ($cq) use ($search) {
                                $cq->where('company_name', 'like', "%{$search}%");
                            });
                    });
            });
        }

        $deliveries = $query->get()->sortBy(function ($item) {
            $statusWeight = in_array(strtolower($item->status), ['driver_vehicle_assigned', 'assigned']) ? 1 : 2;
            return $statusWeight . '_' . (9999999999 - strtotime((string) ($item->updated_at ?? $item->created_at)));
        });

        return view('driver-terminal.deliveries.index', [
            'currentDriver' => $currentDriver,
            'deliveries' => $deliveries,
            'search' => $search,
        ]);
    }

    /**
     * Delivery Details Page (/driver-terminal/{driver_code}/deliveries/{id})
     */
    public function showDelivery(Request $request, string $driver_code, int $id): View|RedirectResponse
    {
        /** @var Driver $currentDriver */
        $currentDriver = $request->attributes->get('current_driver');
        $delivery = TransportRequest::with(['salesOrder.customer', 'vehicle', 'driver'])->findOrFail($id);

        // IDOR Authorization Check: Delivery MUST belong to authenticated driver
        if ((int) $delivery->driver_id !== (int) $currentDriver->id) {
            return redirect()->route('driver-terminal.index', ['driver_code' => strtolower($currentDriver->driver_code)])
                ->with('error', 'Access denied. You do not have permission to view or manage this delivery.');
        }

        return view('driver-terminal.deliveries.show', [
            'currentDriver' => $currentDriver,
            'delivery' => $delivery,
        ]);
    }

    /**
     * Accept Delivery Action (/driver-terminal/{driver_code}/deliveries/{id}/accept)
     */
    public function acceptDelivery(Request $request, string $driver_code, int $id): JsonResponse|RedirectResponse
    {
        /** @var Driver $currentDriver */
        $currentDriver = $request->attributes->get('current_driver');

        return DB::transaction(function () use ($request, $id, $currentDriver) {
            /** @var TransportRequest|null $delivery */
            $delivery = TransportRequest::where('id', $id)->lockForUpdate()->first();

            if (!$delivery) {
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Delivery request not found.'], 404);
                }
                return redirect()->back()->with('error', 'Delivery request not found.');
            }

            // IDOR Authorization Protection Check
            if ((int) $delivery->driver_id !== (int) $currentDriver->id) {
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Access denied. You do not have permission to view or manage this delivery.'], 403);
                }
                return redirect()->route('driver-terminal.index', ['driver_code' => strtolower($currentDriver->driver_code)])
                    ->with('error', 'Access denied. You do not have permission to view or manage this delivery.');
            }

            $currentStatus = strtolower((string) $delivery->status);
            if ($currentStatus === 'dispatched') {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Delivery has already been accepted and dispatched.',
                        'already_dispatched' => true,
                    ], 422);
                }
                return redirect()->back()->with('info', 'Delivery has already been accepted and dispatched.');
            }

            if (!in_array($currentStatus, ['driver_vehicle_assigned', 'assigned'])) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This delivery is no longer available for acceptance.',
                    ], 422);
                }
                return redirect()->back()->with('error', 'This delivery is no longer available for acceptance.');
            }

            $now = now();
            $delivery->status = 'dispatched';
            $delivery->dispatched_at = $now;
            $delivery->accepted_at = $now;
            $delivery->accepted_by = Auth::id();
            $delivery->dispatched_by = Auth::id();
            $delivery->save();

            if ($currentDriver->status !== 'suspended') {
                $currentDriver->status = 'on_delivery';
                $currentDriver->save();
            }

            if (method_exists($delivery, 'logActivity')) {
                $delivery->logActivity('delivery_accepted', 'Delivery accepted by driver ' . $currentDriver->driver_code . ' (' . $currentDriver->driver_name . ')');
            }

            $canonicalDriverCode = strtolower((string) $currentDriver->driver_code);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Delivery accepted successfully! Transport status is now DISPATCHED.',
                    'status' => 'dispatched',
                    'dispatched_at' => $now->format('d M Y, h:i A'),
                    'redirect_url' => route('driver-terminal.deliveries.show', ['driver_code' => $canonicalDriverCode, 'id' => $id]),
                ]);
            }

            return redirect()->route('driver-terminal.deliveries.show', ['driver_code' => $canonicalDriverCode, 'id' => $id])
                ->with('success', 'Delivery accepted successfully! Transport status is now DISPATCHED.');
        });
    }

    /**
     * Driver Updates Operational Delivery Status
     */
    public function updateStatus(Request $request, string $driver_code, int $id): RedirectResponse
    {
        /** @var Driver $currentDriver */
        $currentDriver = $request->attributes->get('current_driver');
        $transportRequest = TransportRequest::findOrFail($id);

        if ((int) $transportRequest->driver_id !== (int) $currentDriver->id) {
            return redirect()->route('driver-terminal.index', ['driver_code' => strtolower($currentDriver->driver_code)])
                ->with('error', 'Access denied. You do not have permission to view or manage this delivery.');
        }

        $validated = $request->validate([
            'status' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $this->executionEngine->updateDeliveryStatus($transportRequest, $validated['status'], $validated['notes'] ?? null, $currentDriver, Auth::id() ?? 1);
            return redirect()->back()->with('success', "Delivery status updated to '" . (DriverExecutionEngine::ALLOWED_STATUSES[$validated['status']] ?? $validated['status']) . "'.");
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Driver Profile Page (Read-Only)
     */
    public function profile(Request $request, ?string $driver_code = null): View
    {
        /** @var Driver $currentDriver */
        $currentDriver = $request->attributes->get('current_driver');
        $driverId = $currentDriver->id;

        $activeDelivery = TransportRequest::with('vehicle')
            ->where('driver_id', $driverId)
            ->whereIn('status', ['driver_vehicle_assigned', 'assigned', 'dispatched', 'in_transit', 'arrived'])
            ->latest()
            ->first();

        $assignedVehicle = $activeDelivery?->vehicle ?? Vehicle::find($currentDriver->current_assignment);

        return view('driver-terminal.profile.index', [
            'currentDriver' => $currentDriver,
            'assignedVehicle' => $assignedVehicle,
        ]);
    }

    /**
     * Notifications Page
     */
    public function notifications(Request $request, ?string $driver_code = null): View
    {
        /** @var Driver $currentDriver */
        $currentDriver = $request->attributes->get('current_driver');

        return view('driver-terminal.notifications.index', [
            'currentDriver' => $currentDriver,
        ]);
    }

    /**
     * Driver Terminal Live Sync API Endpoint
     */
    public function liveSync(Request $request): JsonResponse
    {
        /** @var Driver|null $driver */
        $driver = $request->attributes->get('current_driver');

        if (!$driver) {
            return response()->json(['success' => false, 'message' => 'Driver not authenticated'], 401);
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
            'driver_code' => $driver->driver_code,
            'driver_name' => $driver->driver_name,
            'driver_status' => $driver->status,
            'active_trips_count' => count($activeTrips),
            'trips' => $activeTrips,
        ]);
    }

    private function loginFailedResponse(Request $request, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $message], 422);
        }

        return redirect()->back()
            ->withErrors(['driver_id' => $message])
            ->withInput($request->only('driver_id', 'mobile_number'));
    }
}
