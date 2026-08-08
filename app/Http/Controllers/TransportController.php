<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\TransportRequest;
use App\Models\TransportTrip;
use App\Models\DispatchManifest;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Domain\Transport\TransportPlanningEngine;
use App\Domain\Transport\DispatchExecutionEngine;
use App\Domain\Transport\TransportMasterManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class TransportController extends Controller
{
    public function __construct(
        protected TransportPlanningEngine $planningEngine,
        protected DispatchExecutionEngine $executionEngine,
        protected TransportMasterManager $masterManager
    ) {}

    /**
     * Transport Department Enterprise Control Tower & Workspaces
     */
    public function index(Request $request): View
    {
        $activeTab = $request->input('tab');
        if (!$activeTab) {
            if ($request->routeIs('transport.drivers.*')) {
                $activeTab = 'drivers';
            } elseif ($request->routeIs('transport.vehicles.*')) {
                $activeTab = 'vehicles';
            } else {
                $activeTab = 'overview';
            }
        } else {
            $activeTab = match(strtolower($activeTab)) {
                'dashboard', 'overview' => 'overview',
                'delivery-orders' => 'delivery-orders',
                'drivers' => 'drivers',
                'vehicles', 'maintenance' => 'vehicles',
                'active', 'trips', 'dispatch' => 'active',
                'history' => 'history',
                default => 'overview',
            };
        }
        $status = $request->input('status', 'all');
        $priority = $request->input('priority', 'all');
        $city = $request->input('city', 'all');
        $search = $request->input('search');

        // Phase 1 Driver Master Query Parameters
        $driverSearch = $request->input('driver_search');
        $driverStatus = $request->input('driver_status', 'all');

        // Phase 2 Vehicle Master Query Parameters
        $vehicleSearch = $request->input('vehicle_search');
        $vehicleStatus = $request->input('vehicle_status', 'all');

        // Driver Master Paginated Collection
        $driverQuery = Driver::withCount('trips')->with('suspendedByUser');

        if ($driverStatus && $driverStatus !== 'all') {
            match($driverStatus) {
                'available' => $driverQuery->where('status', 'available'),
                'on_delivery', 'on_trip' => $driverQuery->whereIn('status', ['on_delivery', 'on_trip']),
                'leave', 'on_leave' => $driverQuery->whereIn('status', ['leave', 'on_leave']),
                'suspended' => $driverQuery->where('status', 'suspended'),
                'inactive' => $driverQuery->where('status', 'inactive'),
                'expiring_soon' => $driverQuery->whereNotNull('license_expiry_date')
                                                ->where('license_expiry_date', '<=', now()->addDays(30)),
                default => null,
            };
        }

        if ($driverSearch) {
            $ds = trim($driverSearch);
            $dsClean = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $ds));
            $driverQuery->where(function ($q) use ($ds, $dsClean) {
                $q->where('driver_code', 'like', "%{$ds}%")
                  ->orWhere('driver_name', 'like', "%{$ds}%")
                  ->orWhere('phone_number', 'like', "%{$ds}%")
                  ->orWhere('phone_number', 'like', "%{$dsClean}%")
                  ->orWhere('driving_license_number', 'like', "%{$ds}%")
                  ->orWhere('driving_license_number', 'like', "%{$dsClean}%")
                  ->orWhere('employee_id', 'like', "%{$ds}%");
            });
        }

        $drivers = $driverQuery->orderBy('id', 'desc')->paginate(15, ['*'], 'driver_page')->withQueryString();
        $allDrivers = Driver::withCount('trips')->orderBy('driver_name')->get();

        // Vehicle Master Paginated Collection (Phase 2)
        $vehicleQuery = Vehicle::withCount('trips');

        if ($vehicleStatus && $vehicleStatus !== 'all') {
            match($vehicleStatus) {
                'available' => $vehicleQuery->where('status', 'available'),
                'reserved' => $vehicleQuery->where('status', 'reserved'),
                'on_trip' => $vehicleQuery->where('status', 'on_trip'),
                'maintenance' => $vehicleQuery->where('status', 'maintenance'),
                'breakdown' => $vehicleQuery->where('status', 'breakdown'),
                'inactive' => $vehicleQuery->where('status', 'inactive'),
                'expiring_documents' => $vehicleQuery->where(function ($q) {
                    $limit30 = now()->addDays(30);
                    $q->where('insurance_expiry_date', '<=', $limit30)
                      ->orWhere('fitness_expiry_date', '<=', $limit30)
                      ->orWhere('puc_expiry_date', '<=', $limit30)
                      ->orWhere('permit_expiry_date', '<=', $limit30);
                }),
                default => null,
            };
        }

        if ($vehicleSearch) {
            $vs = trim($vehicleSearch);
            $vsClean = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $vs));
            $vehicleQuery->where(function ($q) use ($vs, $vsClean) {
                $q->where('vehicle_code', 'like', "%{$vs}%")
                  ->orWhere('vehicle_number', 'like', "%{$vs}%")
                  ->orWhere('vehicle_number', 'like', "%{$vsClean}%")
                  ->orWhere('manufacturer', 'like', "%{$vs}%")
                  ->orWhere('model', 'like', "%{$vs}%")
                  ->orWhere('vehicle_type', 'like', "%{$vs}%");
            });
        }

        $vehicles = $vehicleQuery->orderBy('id', 'desc')->paginate(15, ['*'], 'vehicle_page')->withQueryString();
        $allVehicles = Vehicle::withCount('trips')->orderBy('vehicle_number')->get();

        // 1. Dispatch & Request Queue Query
        $query = TransportRequest::with([
            'salesOrder.customer', 'vehicle', 'driver', 'transportTrip',
            'dispatchManifest', 'dispatchChecklist', 'acceptedByUser', 'creator', 'deliveryTimelines'
        ]);

        $queue = $request->get('queue');

        if ($activeTab === 'active') {
            $query->whereIn('status', ['driver_vehicle_assigned', 'dispatched', 'in_transit', 'out_for_delivery']);
        } elseif ($activeTab === 'history') {
            $query->whereIn('status', ['delivered', 'completed', 'returned_to_warehouse', 'cancelled', 'archived']);
        } elseif ($activeTab === 'delivery-orders' && $queue) {
            match($queue) {
                'awaiting_warehouse' => $query->where('status', 'awaiting_warehouse'),
                'ready_for_assignment' => $query->whereIn('status', ['ready_for_assignment', 'waiting_planning', 'vehicle_assigned_pending', 'driver_assigned_pending', 'planning_in_progress', 'planning_completed']),
                'in_transit' => $query->whereIn('status', ['in_transit', 'dispatched', 'out_for_delivery']),
                'completed' => $query->whereIn('status', ['delivered', 'completed']),
                'cancelled' => $query->where('status', 'cancelled'),
                default => null,
            };
        } else if ($status && $status !== 'all') {
            match($status) {
                'waiting_planning' => $query->whereIn('status', ['waiting_planning', 'pending_packaging']),
                'planning_completed' => $query->where('status', 'planning_completed'),
                'accepted_by_transport' => $query->where('status', 'accepted_by_transport'),
                'ready' => $query->where('status', 'ready_for_dispatch'),
                'in_transit' => $query->whereIn('status', ['in_transit', 'dispatched', 'out_for_delivery']),
                'pending_closure' => $query->whereHas('transportTrip', fn($tq) => $tq->where('status', 'pending_closure')),
                'closed', 'completed', 'archived' => $query->whereIn('status', ['delivered', 'completed', 'returned_to_warehouse', 'archived']),
                default => null,
            };
        }

        if ($priority && $priority !== 'all') {
            $query->where('priority', strtolower($priority));
        }

        if ($city && $city !== 'all') {
            $query->where(function ($q) use ($city) {
                $q->where('delivery_city', 'like', "%{$city}%")
                  ->orWhere('delivery_address', 'like', "%{$city}%");
            });
        }

        if ($search) {
            $s = trim($search);
            $query->where(function ($q) use ($s) {
                $q->where('request_number', 'like', "%{$s}%")
                  ->orWhere('order_reference', 'like', "%{$s}%")
                  ->orWhere('customer_name', 'like', "%{$s}%")
                  ->orWhere('delivery_city', 'like', "%{$s}%")
                  ->orWhere('delivery_address', 'like', "%{$s}%")
                  ->orWhere('vehicle_number', 'like', "%{$s}%")
                  ->orWhere('driver_name', 'like', "%{$s}%")
                  ->orWhere('priority', 'like', "%{$s}%")
                  ->orWhereHas('transportTrip', function ($tq) use ($s) {
                      $tq->where('trip_number', 'like', "%{$s}%");
                  })
                  ->orWhereHas('dispatchManifest', function ($mq) use ($s) {
                      $mq->where('manifest_number', 'like', "%{$s}%");
                  });
            });
        }

        if (config('database.default') === 'sqlite') {
            $requests = $query->orderBy('id', 'desc')
                              ->paginate(15)
                              ->withQueryString();
        } else {
            $requests = $query->orderByRaw("FIELD(priority, 'urgent', 'high', 'normal', 'medium', 'low')")
                              ->orderByRaw("COALESCE(required_dispatch_date, expected_delivery_date, created_at) ASC")
                              ->orderByRaw("COALESCE(warehouse_completed_at, created_at) ASC")
                              ->paginate(15)
                              ->withQueryString();
        }

        $availableVehicles = Vehicle::where('status', 'available')
                                    ->where(function ($q) {
                                        $q->where('maintenance_status', '!=', 'Under Repair')
                                          ->orWhereNull('maintenance_status');
                                    })
                                    ->orderBy('vehicle_number')
                                    ->get();

        $availableDrivers = Driver::where('status', 'available')
                                  ->orderBy('driver_name')
                                  ->get();

        $availableCities = TransportRequest::select('delivery_city')
                                          ->whereNotNull('delivery_city')
                                          ->distinct()
                                          ->pluck('delivery_city')
                                          ->toArray();
        if (empty($availableCities)) {
            $availableCities = ['Mumbai', 'Delhi', 'Bangalore', 'Chennai', 'Pune', 'Hyderabad'];
        }

        // Active Dispatched Trips Collection
        $activeTrips = TransportTrip::with(['vehicle', 'driver', 'transportRequests.salesOrder.customer', 'dispatchManifest'])
                                    ->whereIn('status', ['ready', 'dispatched'])
                                    ->orderBy('updated_at', 'desc')
                                    ->get();

        // Pending Trip Closure Collection
        $pendingClosureTrips = TransportTrip::with(['vehicle', 'driver', 'transportRequests.salesOrder.customer', 'dispatchManifest'])
                                            ->whereIn('status', ['pending_closure'])
                                            ->orWhere(function ($tq) {
                                                $tq->where('status', 'dispatched')
                                                   ->whereHas('transportRequests', fn($req) => $req->whereIn('driver_status', ['delivered', 'returned_to_warehouse']));
                                            })
                                            ->orderBy('updated_at', 'desc')
                                            ->get();

        // Closed Trip History Collection
        $archivedTrips = TransportTrip::with(['vehicle', 'driver', 'transportRequests.salesOrder.customer', 'dispatchManifest', 'closedByUser'])
                                      ->whereIn('status', ['closed', 'completed'])
                                      ->orderBy('closed_at', 'desc')
                                      ->take(30)
                                      ->get();

        // All Trips Collection
        $allTrips = TransportTrip::with(['vehicle', 'driver', 'transportRequests.salesOrder.customer', 'dispatchManifest'])
                                  ->orderBy('updated_at', 'desc')
                                  ->get();

        // Compliance & Maintenance Alerts
        $complianceAlerts = $this->masterManager->getComplianceAlerts();

        // Operational Statistics Analytics
        $analytics = $this->executionEngine->getOperationalAnalytics();

        // Operational Overview Counts (Minimal Operational Metrics)
        $ordersAwaitingWarehouseCount = TransportRequest::where('status', 'awaiting_warehouse')->count();
        $ordersReadyAssignmentCount = TransportRequest::whereIn('status', ['ready_for_assignment', 'waiting_planning', 'vehicle_assigned_pending', 'driver_assigned_pending', 'planning_in_progress', 'planning_completed'])->count();
        $ordersAssignedCount = TransportRequest::whereIn('status', ['driver_vehicle_assigned', 'assigned'])->count();
        $activeDeliveriesCount = TransportRequest::whereIn('status', ['in_transit', 'dispatched', 'out_for_delivery'])->count();

        $selectedId = $request->get('task_id', $requests->first()?->id);
        $selectedTask = $selectedId ? TransportRequest::with(['salesOrder.customer', 'vehicle', 'driver', 'transportTrip', 'dispatchManifest', 'dispatchChecklist', 'acceptedByUser', 'creator', 'deliveryTimelines'])->find($selectedId) : null;

        return view('transport.index', compact(
            'activeTab', 'drivers', 'allDrivers', 'vehicles', 'allVehicles', 'allTrips', 'requests', 'selectedTask',
            'availableVehicles', 'availableDrivers', 'availableCities', 'activeTrips',
            'pendingClosureTrips', 'archivedTrips', 'complianceAlerts', 'analytics',
            'status', 'priority', 'city', 'search', 'driverSearch', 'driverStatus',
            'vehicleSearch', 'vehicleStatus',
            'ordersAwaitingWarehouseCount', 'ordersReadyAssignmentCount', 'ordersAssignedCount', 'activeDeliveriesCount'
        ));
    }

    /**
     * Dedicated Driver Master Workspace Index Endpoint (Phase 1)
     */
    public function indexDrivers(Request $request): View
    {
        $request->query->set('tab', 'drivers');
        $request->request->set('tab', 'drivers');
        return $this->index($request);
    }

    /**
     * Dedicated Vehicle Master Workspace Index Endpoint (Phase 2)
     */
    public function indexVehicles(Request $request): View
    {
        $request->query->set('tab', 'vehicles');
        $request->request->set('tab', 'vehicles');
        return $this->index($request);
    }

    /**
     * Driver Master Registration Endpoint (Phase 1)
     */
    public function storeDriver(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'driver_name' => 'required|string|max:255',
            'employee_id' => 'nullable|string|max:100',
            'phone_number' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'date_of_birth' => 'nullable|date',
            'license_class' => 'required|string|max:100',
            'driving_license_number' => 'required|string|max:100',
            'license_expiry_date' => 'required|date',
            'joining_date' => 'nullable|date',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_number' => 'nullable|string|max:50',
            'emergency_contact' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $driver = $this->masterManager->registerDriver($validated, auth()->id() ?? 1);
            return redirect()->route('transport.drivers.index')
                ->with('success', "Driver {$driver->driver_name} registered successfully with Permanent Driver ID: {$driver->driver_code}");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Fetch Driver Profile (JSON for AJAX or details view)
     */
    public function showDriver(Driver $driver): JsonResponse
    {
        $driver->load(['trips.vehicle', 'suspendedByUser']);
        return response()->json([
            'success' => true,
            'driver' => $driver,
            'status_label' => $driver->status_label,
            'status_badge_class' => $driver->status_badge_class,
            'is_license_expired' => $driver->isLicenseExpired(),
            'is_license_expiring_soon' => $driver->isLicenseExpiringSoon(),
        ]);
    }

    /**
     * Driver Master Edit Endpoint (Phase 1)
     */
    public function updateDriver(Request $request, Driver $driver): RedirectResponse
    {
        $validated = $request->validate([
            'driver_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'date_of_birth' => 'nullable|date',
            'license_class' => 'required|string|max:100',
            'driving_license_number' => 'required|string|max:100',
            'license_expiry_date' => 'required|date',
            'joining_date' => 'nullable|date',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_number' => 'nullable|string|max:50',
            'emergency_contact' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $this->masterManager->updateDriver($driver, $validated, auth()->id() ?? 1);
            return redirect()->route('transport.drivers.index')
                ->with('success', "Driver {$driver->driver_name} ({$driver->driver_code}) profile updated successfully!");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Activate Driver Endpoint (Phase 1)
     */
    public function activateDriver(Driver $driver): RedirectResponse
    {
        try {
            $this->masterManager->activateDriver($driver, auth()->id() ?? 1);
            return redirect()->route('transport.drivers.index')
                ->with('success', "Driver {$driver->driver_name} ({$driver->driver_code}) activated successfully and set to Available!");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Deactivate Driver Endpoint (Phase 1 - Non-Destructive)
     */
    public function deactivateDriver(Driver $driver): RedirectResponse
    {
        try {
            $this->masterManager->deactivateDriver($driver, auth()->id() ?? 1);
            return redirect()->route('transport.drivers.index')
                ->with('success', "Driver {$driver->driver_name} ({$driver->driver_code}) set to Inactive. Historical records remain intact.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Suspend Driver Endpoint (Phase 1)
     */
    public function suspendDriver(Request $request, Driver $driver): RedirectResponse
    {
        $request->validate([
            'suspension_reason' => 'required|string|min:3|max:1000',
        ], [
            'suspension_reason.required' => 'A clear suspension reason is required to suspend a driver.',
        ]);

        try {
            $this->masterManager->suspendDriver($driver, $request->input('suspension_reason'), auth()->id() ?? 1);
            return redirect()->route('transport.drivers.index')
                ->with('success', "Driver {$driver->driver_name} ({$driver->driver_code}) suspended successfully.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Vehicle Master Registration Endpoint (Phase 2)
     */
    public function storeVehicle(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'vehicle_number' => 'required|string|max:50',
            'vehicle_type' => 'required|string|max:100',
            'manufacturer' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'manufacturing_year' => 'nullable|integer|min:1990|max:' . (date('Y') + 1),
            'color' => 'nullable|string|max:50',
            'fuel_type' => 'required|string|max:50',
            'load_capacity_kg' => 'required|numeric|min:1',
            'volume_capacity_m3' => 'nullable|numeric|min:0.1',
            'current_odometer_km' => 'nullable|integer|min:0',
            'insurance_policy_number' => 'nullable|string|max:100',
            'insurance_expiry_date' => 'nullable|date',
            'fitness_certificate_number' => 'nullable|string|max:100',
            'fitness_expiry_date' => 'nullable|date',
            'permit_number' => 'nullable|string|max:100',
            'permit_expiry_date' => 'nullable|date',
            'rc_number' => 'nullable|string|max:100',
            'puc_expiry_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        try {
            $vehicle = $this->masterManager->registerVehicle($validated, auth()->id() ?? 1);
            return redirect()->route('transport.vehicles.index')
                ->with('success', "Vehicle {$vehicle->vehicle_number} registered successfully with Permanent Vehicle ID: {$vehicle->vehicle_code}");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Fetch Vehicle Profile (JSON for AJAX or details view)
     */
    public function showVehicle(Vehicle $vehicle): JsonResponse
    {
        $vehicle->load('trips.driver');
        return response()->json([
            'success' => true,
            'vehicle' => $vehicle,
            'status_label' => $vehicle->status_label,
            'status_badge_class' => $vehicle->status_badge_class,
            'insurance_status' => $vehicle->insurance_status,
            'fitness_status' => $vehicle->fitness_status,
            'puc_status' => $vehicle->puc_status,
            'permit_status' => $vehicle->permit_status,
        ]);
    }

    /**
     * Vehicle Master Edit Endpoint (Phase 2)
     */
    public function updateVehicle(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $validated = $request->validate([
            'vehicle_number' => 'required|string|max:50',
            'vehicle_type' => 'required|string|max:100',
            'manufacturer' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'manufacturing_year' => 'nullable|integer|min:1990|max:' . (date('Y') + 1),
            'color' => 'nullable|string|max:50',
            'fuel_type' => 'required|string|max:50',
            'load_capacity_kg' => 'required|numeric|min:1',
            'volume_capacity_m3' => 'nullable|numeric|min:0.1',
            'current_odometer_km' => 'nullable|integer|min:0',
            'insurance_policy_number' => 'nullable|string|max:100',
            'insurance_expiry_date' => 'nullable|date',
            'fitness_certificate_number' => 'nullable|string|max:100',
            'fitness_expiry_date' => 'nullable|date',
            'permit_number' => 'nullable|string|max:100',
            'permit_expiry_date' => 'nullable|date',
            'rc_number' => 'nullable|string|max:100',
            'puc_expiry_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        try {
            $this->masterManager->updateVehicle($vehicle, $validated, auth()->id() ?? 1);
            return redirect()->route('transport.vehicles.index')
                ->with('success', "Vehicle {$vehicle->vehicle_number} ({$vehicle->vehicle_code}) details updated successfully!");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Activate Vehicle Endpoint (Phase 2)
     */
    public function activateVehicle(Vehicle $vehicle): RedirectResponse
    {
        try {
            $this->masterManager->activateVehicle($vehicle, auth()->id() ?? 1);
            return redirect()->route('transport.vehicles.index')
                ->with('success', "Vehicle {$vehicle->vehicle_number} ({$vehicle->vehicle_code}) activated and returned to Available!");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Deactivate Vehicle Endpoint (Phase 2 - Non-Destructive)
     */
    public function deactivateVehicle(Vehicle $vehicle): RedirectResponse
    {
        try {
            $this->masterManager->deactivateVehicle($vehicle, auth()->id() ?? 1);
            return redirect()->route('transport.vehicles.index')
                ->with('success', "Vehicle {$vehicle->vehicle_number} ({$vehicle->vehicle_code}) set to Inactive. Historical trip records remain intact.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Mark Vehicle Maintenance Endpoint (Phase 2)
     */
    public function markVehicleMaintenance(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $validated = $request->validate([
            'maintenance_reason' => 'required|string|min:3|max:1000',
            'maintenance_start_date' => 'nullable|date',
            'maintenance_expected_completion' => 'nullable|date',
            'maintenance_notes' => 'nullable|string',
        ], [
            'maintenance_reason.required' => 'A clear maintenance reason is mandatory.',
        ]);

        try {
            $this->masterManager->markVehicleMaintenance($vehicle, $validated, auth()->id() ?? 1);
            return redirect()->route('transport.vehicles.index')
                ->with('success', "Vehicle {$vehicle->vehicle_number} ({$vehicle->vehicle_code}) marked under Maintenance. Vehicle removed from dispatch availability.");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Return Vehicle From Maintenance Endpoint (Phase 2)
     */
    public function returnVehicleMaintenance(Vehicle $vehicle): RedirectResponse
    {
        try {
            $this->masterManager->returnVehicleFromMaintenance($vehicle, auth()->id() ?? 1);
            return redirect()->route('transport.vehicles.index')
                ->with('success', "Vehicle {$vehicle->vehicle_number} ({$vehicle->vehicle_code}) returned from maintenance and restored to Available!");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Mark Vehicle Breakdown Endpoint (Phase 2)
     */
    public function markVehicleBreakdown(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $validated = $request->validate([
            'breakdown_reason' => 'required|string|min:3|max:1000',
            'breakdown_notes' => 'nullable|string',
        ], [
            'breakdown_reason.required' => 'A breakdown reason is mandatory.',
        ]);

        try {
            $this->masterManager->markVehicleBreakdown($vehicle, $validated, auth()->id() ?? 1);
            return redirect()->route('transport.vehicles.index')
                ->with('success', "Vehicle {$vehicle->vehicle_number} ({$vehicle->vehicle_code}) marked as Breakdown.");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Recover Vehicle From Breakdown Endpoint (Phase 2)
     */
    public function recoverVehicleBreakdown(Vehicle $vehicle): RedirectResponse
    {
        try {
            $this->masterManager->recoverVehicleFromBreakdown($vehicle, auth()->id() ?? 1);
            return redirect()->route('transport.vehicles.index')
                ->with('success', "Vehicle {$vehicle->vehicle_number} ({$vehicle->vehicle_code}) recovered from breakdown and restored to Available!");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Vehicle Assignment Endpoint
     */
    public function assignVehicle(Request $request, TransportRequest $transportRequest): RedirectResponse
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|integer|exists:vehicles,id',
        ]);

        try {
            $this->planningEngine->assignVehicle($transportRequest, (int)$validated['vehicle_id'], auth()->id() ?? 1);
            return redirect()->back()->with('success', "Vehicle successfully assigned to Transport Task #{$transportRequest->request_number}!");
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Driver Assignment Endpoint
     */
    public function assignDriver(Request $request, TransportRequest $transportRequest): RedirectResponse
    {
        $validated = $request->validate([
            'driver_id' => 'required|integer|exists:drivers,id',
        ]);

        try {
            $this->planningEngine->assignDriver($transportRequest, (int)$validated['driver_id'], auth()->id() ?? 1);
            return redirect()->back()->with('success', "Driver successfully assigned to Transport Task #{$transportRequest->request_number}!");
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Transport Trip Creation Endpoint
     */
    public function createTrip(Request $request, TransportRequest $transportRequest): RedirectResponse
    {
        try {
            $trip = $this->planningEngine->createTrip($transportRequest, [], auth()->id() ?? 1);
            return redirect()->back()->with('success', "Transport Trip #{$trip->trip_number} successfully created & assigned to vehicle/driver!");
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Step 1: Accept Custody Endpoint
     */
    public function acceptCustody(TransportRequest $transportRequest): RedirectResponse
    {
        try {
            $this->executionEngine->acceptCustody($transportRequest, auth()->id() ?? 1);
            return redirect()->back()->with('success', "Transport Department accepted custody of Order #{$transportRequest->order_reference}!");
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Step 2: Update Verification Checklist Endpoint
     */
    public function updateChecklist(Request $request, TransportRequest $transportRequest): RedirectResponse
    {
        try {
            $checklist = $this->executionEngine->updateChecklist($transportRequest, $request->all(), auth()->id() ?? 1);
            $msg = $checklist->is_completed ? "Dispatch Checklist Verified & Completed! Permanent Manifest Issued." : "Checklist progress updated.";
            return redirect()->back()->with('success', $msg);
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Step 4 & 5: Dispatch Trip Endpoint
     */
    public function dispatchTrip(TransportRequest $transportRequest): RedirectResponse
    {
        try {
            $trip = $this->executionEngine->dispatchTrip($transportRequest, auth()->id() ?? 1);
            return redirect()->back()->with('success', "🚀 Trip #{$trip->trip_number} Dispatched Successfully! Shipment is now IN TRANSIT under Manifest #{$transportRequest->dispatchManifest->manifest_number}. Driver Terminal activated.");
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Official Trip Closure Endpoint
     */
    public function closeTrip(TransportTrip $transportTrip): RedirectResponse
    {
        try {
            $closedTrip = $this->executionEngine->closeTrip($transportTrip, auth()->id() ?? 1);
            return redirect()->back()->with('success', "🏁 Trip #{$closedTrip->trip_number} Officially CLOSED & ARCHIVED! Fleet resources released and enterprise modules updated.");
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Live Synchronization API Endpoint
     */
    public function liveQueue(Request $request): JsonResponse
    {
        $vehiclesCount = Vehicle::where('status', 'available')->count();
        $driversCount = Driver::where('status', 'available')->count();
        $tripsCount = TransportTrip::whereIn('status', ['ready', 'dispatched'])->count();
        $pendingClosureCount = TransportTrip::where('status', 'pending_closure')->count();

        $tasks = TransportRequest::with(['salesOrder.customer', 'vehicle', 'driver', 'transportTrip', 'dispatchManifest'])
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'normal' THEN 3 WHEN 'medium' THEN 4 ELSE 5 END")
            ->take(30)
            ->get()
            ->map(function ($t) {
                return [
                    'id' => $t->id,
                    'transport_task_id' => $t->request_number,
                    'enterprise_order_id' => $t->order_reference,
                    'customer_name' => $t->customer_name,
                    'city' => $t->city,
                    'package_count' => $t->package_count,
                    'vehicle_number' => $t->vehicle->vehicle_number ?? $t->vehicle_number ?? 'Unassigned',
                    'driver_name' => $t->driver->driver_name ?? $t->driver_name ?? 'Unassigned',
                    'trip_number' => $t->transportTrip->trip_number ?? 'No Trip',
                    'manifest_number' => $t->dispatchManifest->manifest_number ?? 'No Manifest',
                    'accepted_at' => $t->accepted_at ? $t->accepted_at->format('d M, H:i') : null,
                    'priority' => strtoupper($t->priority),
                    'priority_badge' => $t->priority_badge_class,
                    'status_label' => $t->status_label,
                    'status_badge' => $t->status_badge_class,
                ];
            });

        return response()->json([
            'success' => true,
            'available_vehicles' => $vehiclesCount,
            'available_drivers' => $driversCount,
            'active_trips' => $tripsCount,
            'pending_closure' => $pendingClosureCount,
            'count' => count($tasks),
            'tasks' => $tasks,
        ]);
    }

    /**
     * Dedicated Phase 3 Delivery Orders Workspace
     */
    public function indexDeliveryOrders(Request $request)
    {
        $request->merge(['tab' => 'delivery-orders']);
        return $this->index($request);
    }

    /**
     * Dedicated Phase 3 Delivery Order Profile Endpoint (JSON)
     */
    public function showDeliveryOrder(TransportRequest $deliveryOrder): JsonResponse
    {
        $deliveryOrder->load(['salesOrder.customer', 'vehicle', 'driver', 'activeAssignment.driver', 'activeAssignment.vehicle', 'activeAssignment.assignedByUser']);

        $eligibleDrivers = Driver::where('status', 'available')
            ->whereNull('deactivated_at')
            ->where(function ($q) {
                $q->whereNull('suspended_at')->orWhere('status', '!=', 'suspended');
            })
            ->get()
            ->filter(fn($d) => !$d->isLicenseExpired())
            ->values()
            ->map(fn($d) => [
                'id' => $d->id,
                'driver_code' => $d->driver_code,
                'employee_id' => $d->employee_id,
                'driver_name' => $d->driver_name,
                'phone_number' => $d->phone_number,
                'license_class' => $d->license_class,
                'license_expiry' => $d->license_expiry_date?->format('Y-m-d'),
                'status' => $d->status,
                'status_label' => $d->status_label,
                'status_badge_class' => $d->status_badge_class,
            ]);

        $eligibleVehicles = Vehicle::where('status', 'available')
            ->where(function ($q) {
                $q->where('maintenance_status', '!=', 'Under Repair')
                  ->orWhereNull('maintenance_status');
            })
            ->whereNull('deactivated_at')
            ->get()
            ->map(fn($v) => [
                'id' => $v->id,
                'vehicle_code' => $v->vehicle_code,
                'vehicle_number' => $v->vehicle_number,
                'vehicle_type' => $v->vehicle_type,
                'load_capacity_kg' => (float)$v->load_capacity_kg,
                'volume_capacity_m3' => (float)$v->volume_capacity_m3,
                'status' => $v->status,
                'compliance_status' => $v->getDocumentComplianceStatus(now()),
            ]);

        $activeAssignment = $deliveryOrder->activeAssignment;

        return response()->json([
            'id' => $deliveryOrder->id,
            'request_number' => $deliveryOrder->request_number,
            'order_reference' => $deliveryOrder->order_reference,
            'customer_name' => $deliveryOrder->customer_name,
            'delivery_address' => $deliveryOrder->delivery_address,
            'delivery_city' => $deliveryOrder->city,
            'phone_number' => $deliveryOrder->phone_number,
            'priority' => $deliveryOrder->priority,
            'priority_badge_class' => $deliveryOrder->priority_badge_class,
            'expected_delivery_date' => $deliveryOrder->expected_delivery_date?->format('Y-m-d'),
            'package_count' => $deliveryOrder->package_count,
            'weight_kg' => (float)$deliveryOrder->weight_kg,
            'volume_m3' => (float)$deliveryOrder->volume_m3,
            'source_module' => $deliveryOrder->source_module ?? 'CRM Sales Order',
            'status' => $deliveryOrder->status,
            'status_label' => $deliveryOrder->status_label,
            'status_badge_class' => $deliveryOrder->status_badge_class,
            'warehouse_status_label' => $deliveryOrder->warehouse_status_label,
            'warehouse_status_badge_class' => $deliveryOrder->warehouse_status_badge_class,
            'warehouse_completed_at' => $deliveryOrder->warehouse_completed_at?->format('H:i, d M Y'),
            'driver' => $deliveryOrder->driver ? [
                'id' => $deliveryOrder->driver->id,
                'driver_code' => $deliveryOrder->driver->driver_code,
                'employee_id' => $deliveryOrder->driver->employee_id,
                'driver_name' => $deliveryOrder->driver->driver_name,
                'phone_number' => $deliveryOrder->driver->phone_number,
                'license_class' => $deliveryOrder->driver->license_class,
            ] : null,
            'vehicle' => $deliveryOrder->vehicle ? [
                'id' => $deliveryOrder->vehicle->id,
                'vehicle_code' => $deliveryOrder->vehicle->vehicle_code,
                'vehicle_number' => $deliveryOrder->vehicle->vehicle_number,
                'vehicle_type' => $deliveryOrder->vehicle->vehicle_type,
                'load_capacity_kg' => (float)$deliveryOrder->vehicle->load_capacity_kg,
            ] : null,
            'active_assignment' => $activeAssignment ? [
                'id' => $activeAssignment->id,
                'assignment_number' => $activeAssignment->assignment_number,
                'driver_id' => $activeAssignment->driver_id,
                'driver_name' => $activeAssignment->driver?->driver_name,
                'driver_phone' => $activeAssignment->driver?->phone_number,
                'vehicle_id' => $activeAssignment->vehicle_id,
                'vehicle_number' => $activeAssignment->vehicle?->vehicle_number,
                'vehicle_type' => $activeAssignment->vehicle?->vehicle_type,
                'assigned_by_name' => $activeAssignment->assignedByUser?->name ?? 'Transport Manager',
                'assigned_at' => $activeAssignment->assigned_at?->format('H:i, d M Y'),
                'status' => $activeAssignment->status,
                'status_label' => $activeAssignment->status_label,
            ] : null,
            'eligible_drivers' => $eligibleDrivers,
            'eligible_vehicles' => $eligibleVehicles,
            'timeline' => $deliveryOrder->timeline_events,
        ]);
    }

    /**
     * Phase 4 — Confirm Driver & Vehicle Assignment Endpoint
     */
    public function assignDriverAndVehicle(Request $request, TransportRequest $transportRequest): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'driver_id' => 'required|integer|exists:drivers,id',
            'vehicle_id' => 'required|integer|exists:vehicles,id',
            'instructions' => 'nullable|string|max:1000',
        ]);

        try {
            $assignment = $this->planningEngine->assignDriverAndVehicle(
                $transportRequest,
                (int)$validated['driver_id'],
                (int)$validated['vehicle_id'],
                auth()->id() ?? 1,
                $validated['instructions'] ?? null
            );

            $message = "Driver and vehicle assigned successfully. (Assignment #{$assignment->assignment_number})";

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'assignment_number' => $assignment->assignment_number,
                ]);
            }

            return redirect()->back()->with('success', $message);
        } catch (\InvalidArgumentException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Phase 4 — Controlled Reassign Endpoint
     */
    public function reassignDriverAndVehicle(Request $request, TransportRequest $transportRequest): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'new_driver_id' => 'required|integer|exists:drivers,id',
            'new_vehicle_id' => 'required|integer|exists:vehicles,id',
            'reassignment_reason' => 'required|string|min:3|max:1000',
        ]);

        try {
            $assignment = $this->planningEngine->reassignDriverAndVehicle(
                $transportRequest,
                (int)$validated['new_driver_id'],
                (int)$validated['new_vehicle_id'],
                auth()->id() ?? 1,
                $validated['reassignment_reason']
            );

            $message = "Driver and vehicle reassigned successfully. (New Assignment #{$assignment->assignment_number})";

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'assignment_number' => $assignment->assignment_number,
                ]);
            }

            return redirect()->back()->with('success', $message);
        } catch (\InvalidArgumentException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Phase 4 — Search & Filter Eligible Drivers Endpoint
     */
    public function getEligibleDrivers(Request $request): JsonResponse
    {
        $search = trim($request->input('search', ''));

        $query = Driver::where('status', 'available')
            ->whereNull('deactivated_at')
            ->where(function ($q) {
                $q->whereNull('suspended_at')->orWhere('status', '!=', 'suspended');
            });

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('driver_name', 'like', "%{$search}%")
                  ->orWhere('driver_code', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        $drivers = $query->orderBy('driver_name')->get()
            ->filter(fn($d) => !$d->isLicenseExpired())
            ->values()
            ->map(fn($d) => [
                'id' => $d->id,
                'driver_code' => $d->driver_code,
                'employee_id' => $d->employee_id,
                'driver_name' => $d->driver_name,
                'phone_number' => $d->phone_number,
                'license_class' => $d->license_class,
                'license_expiry' => $d->license_expiry_date?->format('Y-m-d'),
                'status' => $d->status,
                'status_label' => $d->status_label,
            ]);

        return response()->json($drivers);
    }

    /**
     * Phase 4 — Search & Filter Eligible Vehicles Endpoint
     */
    public function getEligibleVehicles(Request $request): JsonResponse
    {
        $search = trim($request->input('search', ''));
        $minCapacity = (float)$request->input('min_capacity_kg', 0.0);

        $query = Vehicle::where('status', 'available')
            ->where(function ($q) {
                $q->where('maintenance_status', '!=', 'Under Repair')
                  ->orWhereNull('maintenance_status');
            })
            ->whereNull('deactivated_at');

        if ($minCapacity > 0) {
            $query->where('load_capacity_kg', '>=', $minCapacity);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('vehicle_number', 'like', "%{$search}%")
                  ->orWhere('vehicle_code', 'like', "%{$search}%")
                  ->orWhere('vehicle_type', 'like', "%{$search}%");
            });
        }

        $vehicles = $query->orderBy('vehicle_number')->get()
            ->map(fn($v) => [
                'id' => $v->id,
                'vehicle_code' => $v->vehicle_code,
                'vehicle_number' => $v->vehicle_number,
                'vehicle_type' => $v->vehicle_type,
                'load_capacity_kg' => (float)$v->load_capacity_kg,
                'volume_capacity_m3' => (float)$v->volume_capacity_m3,
                'status' => $v->status,
                'compliance_status' => $v->getDocumentComplianceStatus(now()),
            ]);

        return response()->json($vehicles);
    }
}
