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
        // Identify Driver Context (Find assigned driver by name/employee ID or default to Rajesh Kumar)
        $driverId = $request->input('driver_id');
        if ($driverId) {
            $currentDriver = Driver::find($driverId);
        } else {
            $currentDriver = Driver::whereIn('status', ['on_trip', 'on_delivery', 'available'])->first()
                             ?: Driver::first();
        }

        $allDrivers = Driver::orderBy('driver_name')->get();

        if (!$currentDriver) {
            return view('driver.index', [
                'activeTrips' => collect(),
                'completedTrips' => collect(),
                'selectedTrip' => null,
                'selectedTask' => null,
                'currentDriver' => null,
                'allDrivers' => $allDrivers,
            ]);
        }

        // Active Trips assigned to Driver (Sorted: Highest Priority -> Oldest Dispatch)
        $activeTrips = TransportTrip::with(['vehicle', 'driver', 'dispatchManifest', 'transportRequests.salesOrder.customer', 'transportRequests.deliveryTimelines'])
            ->where('driver_id', $currentDriver->id)
            ->whereIn('status', ['created', 'ready', 'dispatched'])
            ->get()
            ->sortBy([
                function ($trip) {
                    $hasUrgent = $trip->transportRequests->contains(fn($r) => strtolower($r->priority) === 'urgent');
                    $hasHigh = $trip->transportRequests->contains(fn($r) => strtolower($r->priority) === 'high');
                    return $hasUrgent ? 1 : ($hasHigh ? 2 : 3);
                },
                function ($trip) {
                    return $trip->dispatched_at ? $trip->dispatched_at->timestamp : $trip->created_at->timestamp;
                }
            ]);

        // Completed Trips History
        $completedTrips = TransportTrip::with(['vehicle', 'driver', 'dispatchManifest', 'transportRequests.salesOrder.customer'])
            ->where('driver_id', $currentDriver->id)
            ->whereIn('status', ['completed', 'returned', 'cancelled'])
            ->orderBy('updated_at', 'desc')
            ->take(15)
            ->get();

        $selectedTripId = $request->get('trip_id', $activeTrips->first()?->id);
        $selectedTrip = $selectedTripId ? TransportTrip::with(['vehicle', 'driver', 'dispatchManifest', 'transportRequests.salesOrder.customer', 'transportRequests.deliveryTimelines'])->find($selectedTripId) : null;

        $selectedTaskId = $request->get('task_id', $selectedTrip?->transportRequests->first()?->id);
        $selectedTask = $selectedTaskId ? TransportRequest::with(['salesOrder.customer', 'vehicle', 'driver', 'transportTrip', 'dispatchManifest', 'deliveryTimelines'])->find($selectedTaskId) : null;

        return view('driver.index', compact('activeTrips', 'completedTrips', 'selectedTrip', 'selectedTask', 'currentDriver', 'allDrivers'));
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
     * Phase 0 Foundational Stub: Login Page (NO OTP)
     */
    public function login(): View
    {
        return view('driver-terminal.auth.login');
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
     * Phase 0 Foundational Stub: Driver Profile
     */
    public function profile(): View
    {
        return view('driver-terminal.profile.index');
    }

    /**
     * Phase 0 Foundational Stub: Notifications
     */
    public function notifications(): View
    {
        return view('driver-terminal.notifications.index');
    }
}
