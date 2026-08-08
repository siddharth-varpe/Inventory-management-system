<?php

declare(strict_types=1);

namespace App\Domain\Transport;

use App\Models\TransportRequest;
use App\Models\TransportTrip;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\AuditLog;
use App\Models\SalesOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class TransportPlanningEngine
{
    /**
     * Vehicle Assignment with Concurrency Control & Availability Checks
     */
    public function assignVehicle(TransportRequest $task, int $vehicleId, int $operatorId): TransportRequest
    {
        return DB::transaction(function () use ($task, $vehicleId, $operatorId) {
            // Lock task row for concurrency safety
            $task = TransportRequest::where('id', $task->id)->lockForUpdate()->firstOrFail();

            // Prevent assignment if task is awaiting warehouse completion or cancelled
            if ($task->status === 'awaiting_warehouse') {
                throw new InvalidArgumentException("Transport Task #{$task->request_number} is AWAITING WAREHOUSE completion (Order #{$task->order_reference}). Vehicle & Driver assignments are locked until Organize Stock completes Pick & Pack and seals the shipment.");
            }

            if (in_array($task->status, ['dispatched', 'out_for_delivery', 'delivered', 'completed', 'cancelled'])) {
                throw new InvalidArgumentException("Cannot assign vehicle to Transport Task #{$task->request_number} because it has already progressed past planning (Status: {$task->status_label}).");
            }

            // Lock vehicle row
            $vehicle = Vehicle::where('id', $vehicleId)->lockForUpdate()->first();
            if (!$vehicle) {
                throw new InvalidArgumentException("Selected vehicle record does not exist.");
            }

            // Availability Check
            if ($vehicle->status !== 'available' || strtolower($vehicle->maintenance_status) === 'under repair') {
                throw new InvalidArgumentException("Vehicle {$vehicle->vehicle_number} ({$vehicle->vehicle_type}) is currently unavailable or under maintenance! (Status: {$vehicle->status}, Maintenance: {$vehicle->maintenance_status})");
            }

            // Active Trip Check
            $activeTrip = TransportTrip::where('vehicle_id', $vehicleId)
                ->whereIn('status', ['created', 'ready', 'dispatched'])
                ->first();
            if ($activeTrip) {
                throw new InvalidArgumentException("Vehicle {$vehicle->vehicle_number} is already assigned to active Trip #{$activeTrip->trip_number}.");
            }

            // Perform Vehicle Assignment
            $task->update([
                'vehicle_id' => $vehicle->id,
                'vehicle_number' => $vehicle->vehicle_number,
                'carrier' => $vehicle->vehicle_type,
                'status' => $task->driver_id ? 'planning_in_progress' : 'vehicle_assigned_pending',
            ]);

            // Immutable Audit Log
            AuditLog::create([
                'user_id' => $operatorId,
                'module' => 'Transport Department',
                'action' => 'Vehicle Assigned',
                'table_name' => 'transport_requests',
                'record_id' => $task->id,
                'old_values' => null,
                'new_values' => json_encode([
                    'transport_task_id' => $task->request_number,
                    'vehicle_number' => $vehicle->vehicle_number,
                    'vehicle_type' => $vehicle->vehicle_type,
                    'operator_id' => $operatorId,
                    'timestamp' => now()->toIso8601String(),
                ]),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System Console',
            ]);

            Log::info("TransportPlanning: Vehicle {$vehicle->vehicle_number} assigned to Task #{$task->request_number}");

            return $task;
        });
    }

    /**
     * Driver Assignment with Concurrency Control & Availability Checks
     */
    public function assignDriver(TransportRequest $task, int $driverId, int $operatorId): TransportRequest
    {
        return DB::transaction(function () use ($task, $driverId, $operatorId) {
            // Lock task row
            $task = TransportRequest::where('id', $task->id)->lockForUpdate()->firstOrFail();

            if ($task->status === 'awaiting_warehouse') {
                throw new InvalidArgumentException("Transport Task #{$task->request_number} is AWAITING WAREHOUSE completion (Order #{$task->order_reference}). Vehicle & Driver assignments are locked until Organize Stock completes Pick & Pack and seals the shipment.");
            }

            if (in_array($task->status, ['dispatched', 'out_for_delivery', 'delivered', 'completed', 'cancelled'])) {
                throw new InvalidArgumentException("Cannot assign driver to Transport Task #{$task->request_number} because it has already progressed past planning.");
            }

            // Lock driver row
            $driver = Driver::where('id', $driverId)->lockForUpdate()->first();
            if (!$driver) {
                throw new InvalidArgumentException("Selected driver record does not exist.");
            }

            // Availability Check
            if ($driver->status !== 'available') {
                throw new InvalidArgumentException("Driver {$driver->driver_name} ({$driver->employee_id}) is currently unavailable or off-duty! (Status: {$driver->status})");
            }

            // Active Trip Check
            $activeTrip = TransportTrip::where('driver_id', $driverId)
                ->whereIn('status', ['created', 'ready', 'dispatched'])
                ->first();
            if ($activeTrip) {
                throw new InvalidArgumentException("Driver {$driver->driver_name} is already assigned to active Trip #{$activeTrip->trip_number}.");
            }

            // Perform Driver Assignment
            $task->update([
                'driver_id' => $driver->id,
                'driver_name' => $driver->driver_name,
                'assigned_driver_id' => $operatorId,
                'status' => $task->vehicle_id ? 'planning_in_progress' : 'driver_assigned_pending',
            ]);

            // Immutable Audit Log
            AuditLog::create([
                'user_id' => $operatorId,
                'module' => 'Transport Department',
                'action' => 'Driver Assigned',
                'table_name' => 'transport_requests',
                'record_id' => $task->id,
                'old_values' => null,
                'new_values' => json_encode([
                    'transport_task_id' => $task->request_number,
                    'driver_name' => $driver->driver_name,
                    'employee_id' => $driver->employee_id,
                    'operator_id' => $operatorId,
                    'timestamp' => now()->toIso8601String(),
                ]),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System Console',
            ]);

            Log::info("TransportPlanning: Driver {$driver->driver_name} assigned to Task #{$task->request_number}");

            return $task;
        });
    }

    /**
     * Create Transport Trip & Finalize Transport Planning (Phase 2 Master Endpoint)
     */
    public function createTrip(TransportRequest $task, array $planningData, int $operatorId): TransportTrip
    {
        return DB::transaction(function () use ($task, $planningData, $operatorId) {
            // Lock Task
            $task = TransportRequest::with(['salesOrder'])->where('id', $task->id)->lockForUpdate()->firstOrFail();

            // Comprehensive Phase 2 Pre-Validation
            // Check 1: Warehouse Completed & Verification Checked
            if (empty($task->warehouse_completed_at) && $task->status === 'pending_packaging') {
                throw new InvalidArgumentException("Validation Failed: Warehouse Pick & Pack has not completed for Order #{$task->order_reference}.");
            }

            // Check 2: Transport Task Exists
            if (!$task || !$task->id) {
                throw new InvalidArgumentException("Validation Failed: Valid Transport Task is required.");
            }

            // Check 3: Enterprise Order Exists
            if (!$task->salesOrder && empty($task->sales_order_id)) {
                throw new InvalidArgumentException("Validation Failed: Enterprise Sales Order not found for reference {$task->order_reference}.");
            }

            // Check 4 & 5: Vehicle & Driver Assigned
            if (!$task->vehicle_id) {
                throw new InvalidArgumentException("Validation Failed: Vehicle must be assigned before creating a Transport Trip.");
            }
            if (!$task->driver_id) {
                throw new InvalidArgumentException("Validation Failed: Driver must be assigned before creating a Transport Trip.");
            }

            // Check 6: Vehicle Available
            $vehicle = Vehicle::where('id', $task->vehicle_id)->lockForUpdate()->firstOrFail();
            if ($vehicle->status !== 'available' || strtolower($vehicle->maintenance_status) === 'under repair') {
                throw new InvalidArgumentException("Validation Failed: Assigned Vehicle {$vehicle->vehicle_number} is no longer available.");
            }

            // Check 7: Driver Available
            $driver = Driver::where('id', $task->driver_id)->lockForUpdate()->firstOrFail();
            if ($driver->status !== 'available') {
                throw new InvalidArgumentException("Validation Failed: Assigned Driver {$driver->driver_name} is no longer available.");
            }

            // Check 8: Duplicate Trip Check
            if ($task->transport_trip_id) {
                $existingTrip = TransportTrip::find($task->transport_trip_id);
                if ($existingTrip) {
                    throw new InvalidArgumentException("Validation Failed: A Transport Trip (#{$existingTrip->trip_number}) already exists for Transport Task #{$task->request_number}.");
                }
            }

            // Generate Globally Unique Sequential Immutable Trip ID (e.g. TRIP-2026-000241)
            $nextSeq = (int) (TransportTrip::max('id') + 1);
            $tripNumber = 'TRIP-' . date('Y') . '-' . str_pad((string)$nextSeq, 6, '0', STR_PAD_LEFT);

            while (TransportTrip::where('trip_number', $tripNumber)->exists()) {
                $nextSeq++;
                $tripNumber = 'TRIP-' . date('Y') . '-' . str_pad((string)$nextSeq, 6, '0', STR_PAD_LEFT);
            }

            // Create Primary Transport Trip Object
            $trip = TransportTrip::create([
                'trip_number' => $tripNumber,
                'vehicle_id' => $vehicle->id,
                'driver_id' => $driver->id,
                'planned_departure_at' => !empty($planningData['planned_departure_at']) ? $planningData['planned_departure_at'] : now()->addHours(2),
                'expected_delivery_date' => $task->required_dispatch_date ?? $task->expected_delivery_date ?? now()->addDays(1),
                'total_package_count' => $task->package_count ?: 1,
                'total_weight_kg' => $task->weight_kg ?: 5.0,
                'total_volume_m3' => $task->volume_m3 ?: 0.50,
                'destination_city' => $task->city,
                'status' => 'ready',
                'created_by' => $operatorId,
            ]);

            // Link Transport Task to Trip & Update Status
            $task->update([
                'transport_trip_id' => $trip->id,
                'status' => 'ready_for_dispatch', // Status: Planning Completed -> Ready For Dispatch
            ]);

            // Update Vehicle Status
            $vehicle->update([
                'status' => 'on_trip',
                'current_location' => "Assigned to Trip #{$trip->trip_number} at Dispatch Bay",
            ]);

            // Update Driver Status
            $driver->update([
                'status' => 'on_trip',
                'current_assignment' => "Assigned to Trip #{$trip->trip_number} with Vehicle {$vehicle->vehicle_number}",
            ]);

            // Record Immutable Audit Logs
            AuditLog::create([
                'user_id' => $operatorId,
                'module' => 'Transport Department',
                'action' => 'Trip Created',
                'table_name' => 'transport_trips',
                'record_id' => $trip->id,
                'old_values' => null,
                'new_values' => json_encode([
                    'trip_id' => $trip->trip_number,
                    'enterprise_order_id' => $task->order_reference,
                    'transport_task_id' => $task->request_number,
                    'vehicle_number' => $vehicle->vehicle_number,
                    'driver_name' => $driver->driver_name,
                    'operator_id' => $operatorId,
                    'timestamp' => now()->toIso8601String(),
                    'status' => 'Ready',
                ]),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System Console',
            ]);

            AuditLog::create([
                'user_id' => $operatorId,
                'module' => 'Transport Department',
                'action' => 'Planning Completed',
                'table_name' => 'transport_requests',
                'record_id' => $task->id,
                'old_values' => null,
                'new_values' => json_encode([
                    'transport_task_id' => $task->request_number,
                    'trip_id' => $trip->trip_number,
                    'status' => 'Ready For Dispatch',
                    'timestamp' => now()->toIso8601String(),
                ]),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System Console',
            ]);

            Log::info("TransportPlanning SUCCESS: Trip #{$trip->trip_number} created for Task #{$task->request_number} with Vehicle {$vehicle->vehicle_number} & Driver {$driver->driver_name}");

            return $trip;
        });
    }
}
