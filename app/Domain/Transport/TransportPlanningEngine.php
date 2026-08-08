<?php

declare(strict_types=1);

namespace App\Domain\Transport;

use App\Models\TransportRequest;
use App\Models\TransportTrip;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\AuditLog;
use App\Models\SalesOrder;
use App\Models\DriverVehicleAssignment;
use App\Models\DriverNotification;
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

    /**
     * Phase 4 — Atomic Driver & Vehicle Assignment with Concurrency Control & Capacity Validation
     */
    public function assignDriverAndVehicle(
        TransportRequest $task,
        int $driverId,
        int $vehicleId,
        int $operatorId,
        ?string $instructions = null
    ): DriverVehicleAssignment {
        return DB::transaction(function () use ($task, $driverId, $vehicleId, $operatorId, $instructions) {
            // 1. Lock Transport Task Row
            $task = TransportRequest::where('id', $task->id)->lockForUpdate()->firstOrFail();

            // Status Verification Guard
            if ($task->status === 'awaiting_warehouse') {
                throw new InvalidArgumentException("Transport Task #{$task->request_number} is AWAITING WAREHOUSE completion (Order #{$task->order_reference}). Vehicle & Driver assignments are locked until Organize Stock completes Pick & Pack and seals the shipment.");
            }

            if ($task->status === 'cancelled') {
                throw new InvalidArgumentException("Transport Task #{$task->request_number} is CANCELLED and cannot be assigned.");
            }

            if (in_array($task->status, ['dispatched', 'in_transit', 'out_for_delivery', 'delivered', 'completed'])) {
                throw new InvalidArgumentException("Cannot assign driver and vehicle to Transport Task #{$task->request_number} because it has already been dispatched.");
            }

            if (!in_array($task->status, ['ready_for_assignment', 'waiting_planning'])) {
                throw new InvalidArgumentException("Cannot assign driver and vehicle to Transport Task #{$task->request_number} because its status is '{$task->status_label}'. Only orders in 'Ready for Assignment' status can be assigned.");
            }

            // 2. Lock Driver Row & Check Eligibility
            $driver = Driver::where('id', $driverId)->lockForUpdate()->first();
            if (!$driver) {
                throw new InvalidArgumentException("Selected driver record does not exist.");
            }

            if ($driver->status !== 'available' || $driver->isSuspended() || $driver->isInactive() || $driver->isLicenseExpired()) {
                throw new InvalidArgumentException("Driver {$driver->driver_name} is no longer available.");
            }

            // Active Assignment Check for Driver
            $activeDriverAssignment = DriverVehicleAssignment::where('driver_id', $driverId)
                ->where('status', 'active')
                ->first();
            if ($activeDriverAssignment) {
                throw new InvalidArgumentException("Driver {$driver->driver_name} is no longer available.");
            }

            // 3. Lock Vehicle Row & Check Eligibility
            $vehicle = Vehicle::where('id', $vehicleId)->lockForUpdate()->first();
            if (!$vehicle) {
                throw new InvalidArgumentException("Selected vehicle record does not exist.");
            }

            if ($vehicle->status !== 'available' || $vehicle->isUnderMaintenance() || $vehicle->isBreakdown() || $vehicle->isInactive()) {
                throw new InvalidArgumentException("Vehicle {$vehicle->vehicle_number} is no longer available.");
            }

            // Active Assignment Check for Vehicle
            $activeVehicleAssignment = DriverVehicleAssignment::where('vehicle_id', $vehicleId)
                ->where('status', 'active')
                ->first();
            if ($activeVehicleAssignment) {
                throw new InvalidArgumentException("Vehicle {$vehicle->vehicle_number} is no longer available.");
            }

            // 4. Capacity Validation (Weight & Volume)
            $orderWeight = (float) ($task->weight_kg ?: 0.0);
            $vehicleCapacity = (float) ($vehicle->load_capacity_kg ?: 0.0);
            if ($orderWeight > 0 && $vehicleCapacity > 0 && $orderWeight > $vehicleCapacity) {
                throw new InvalidArgumentException("Selected vehicle does not have sufficient capacity for this order.");
            }

            $orderVolume = (float) ($task->volume_m3 ?: 0.0);
            $vehicleVolumeCapacity = (float) ($vehicle->volume_capacity_m3 ?: 0.0);
            if ($orderVolume > 0 && $vehicleVolumeCapacity > 0 && $orderVolume > $vehicleVolumeCapacity) {
                throw new InvalidArgumentException("Selected vehicle does not have sufficient capacity for this order.");
            }

            // 5. Generate Unique Assignment Identifier (e.g. ASN-000001)
            $nextSeq = (int) (DriverVehicleAssignment::max('id') + 1);
            $assignmentNumber = 'ASN-' . str_pad((string)$nextSeq, 6, '0', STR_PAD_LEFT);

            while (DriverVehicleAssignment::where('assignment_number', $assignmentNumber)->exists()) {
                $nextSeq++;
                $assignmentNumber = 'ASN-' . str_pad((string)$nextSeq, 6, '0', STR_PAD_LEFT);
            }

            // 6. Create Canonical Assignment Record
            $assignment = DriverVehicleAssignment::create([
                'assignment_number' => $assignmentNumber,
                'transport_request_id' => $task->id,
                'sales_order_id' => $task->sales_order_id,
                'enterprise_order_id' => $task->order_reference,
                'driver_id' => $driver->id,
                'vehicle_id' => $vehicle->id,
                'assigned_by' => $operatorId,
                'assigned_at' => now(),
                'status' => 'active',
                'instructions' => $instructions,
            ]);

            // 7. Update Transport Task Status
            $task->update([
                'driver_id' => $driver->id,
                'driver_name' => $driver->driver_name,
                'vehicle_id' => $vehicle->id,
                'vehicle_number' => $vehicle->vehicle_number,
                'status' => 'driver_vehicle_assigned',
                'driver_vehicle_assignment_id' => $assignment->id,
            ]);

            // 8. Update Driver Operational Status -> ON DELIVERY
            $driver->update([
                'status' => 'on_delivery',
                'current_assignment' => "Assigned to Order #{$task->order_reference} (Task #{$task->request_number})",
            ]);

            // 9. Update Vehicle Operational Status -> ON TRIP
            $vehicle->update([
                'status' => 'on_trip',
                'current_location' => "Assigned to Order #{$task->order_reference} (Task #{$task->request_number})",
            ]);

            // 10. Record Audit Log Events
            AuditLog::create([
                'user_id' => $operatorId,
                'module' => 'Transport Department',
                'action' => 'Assignment Created',
                'table_name' => 'driver_vehicle_assignments',
                'record_id' => $assignment->id,
                'old_values' => null,
                'new_values' => json_encode([
                    'assignment_number' => $assignment->assignment_number,
                    'enterprise_order_id' => $task->order_reference,
                    'transport_task_id' => $task->request_number,
                    'driver_id' => $driver->id,
                    'driver_name' => $driver->driver_name,
                    'vehicle_id' => $vehicle->id,
                    'vehicle_number' => $vehicle->vehicle_number,
                    'operator_id' => $operatorId,
                    'timestamp' => now()->toIso8601String(),
                ]),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System Console',
            ]);

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
                    'status' => 'ON DELIVERY',
                    'timestamp' => now()->toIso8601String(),
                ]),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System Console',
            ]);

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
                    'status' => 'ON TRIP',
                    'timestamp' => now()->toIso8601String(),
                ]),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System Console',
            ]);

            // 11. Queue Targeted Driver Notification
            DriverNotification::create([
                'driver_id' => $driver->id,
                'assignment_id' => $assignment->id,
                'title' => 'New Delivery Assigned',
                'enterprise_order_id' => $task->order_reference,
                'customer_name' => $task->customer_name ?? $task->salesOrder?->customer?->company_name ?? 'Customer',
                'delivery_address' => $task->delivery_address,
                'destination_city' => $task->city,
                'package_count' => $task->package_count ?: 1,
                'priority' => $task->priority ?: 'normal',
                'required_delivery_date' => $task->required_dispatch_date ?? $task->expected_delivery_date,
                'vehicle_registration_number' => $vehicle->vehicle_number,
                'assignment_time' => now(),
                'delivery_instructions' => $instructions,
                'is_read' => false,
            ]);

            Log::info("TransportPlanning SUCCESS: Driver {$driver->driver_name} & Vehicle {$vehicle->vehicle_number} assigned to Order #{$task->order_reference} (Assignment #{$assignment->assignment_number})");

            return $assignment;
        });
    }

    /**
     * Phase 4 — Controlled Driver & Vehicle Reassignment
     */
    public function reassignDriverAndVehicle(
        TransportRequest $task,
        int $newDriverId,
        int $newVehicleId,
        int $operatorId,
        string $reassignmentReason
    ): DriverVehicleAssignment {
        return DB::transaction(function () use ($task, $newDriverId, $newVehicleId, $operatorId, $reassignmentReason) {
            // 1. Lock Task
            $task = TransportRequest::where('id', $task->id)->lockForUpdate()->firstOrFail();

            // Dispatch Boundary Check
            if (in_array($task->status, ['dispatched', 'in_transit', 'out_for_delivery', 'delivered', 'completed'])) {
                throw new InvalidArgumentException("Cannot reassign order #{$task->order_reference} because it is already dispatched or in transit.");
            }

            if ($task->status === 'cancelled') {
                throw new InvalidArgumentException("Cannot reassign order #{$task->order_reference} because it has been cancelled.");
            }

            // 2. Release Previous Assignment Records & Resources
            $activeAssignment = DriverVehicleAssignment::where('transport_request_id', $task->id)
                ->where('status', 'active')
                ->first();

            if ($activeAssignment) {
                $activeAssignment->update([
                    'status' => 'reassigned',
                    'reassignment_reason' => $reassignmentReason,
                ]);
            }

            if ($task->driver_id) {
                $oldDriver = Driver::where('id', $task->driver_id)->lockForUpdate()->first();
                if ($oldDriver) {
                    $oldDriver->update([
                        'status' => 'available',
                        'current_assignment' => null,
                    ]);

                    AuditLog::create([
                        'user_id' => $operatorId,
                        'module' => 'Transport Department',
                        'action' => 'Driver Released',
                        'table_name' => 'drivers',
                        'record_id' => $oldDriver->id,
                        'old_values' => null,
                        'new_values' => json_encode([
                            'driver_name' => $oldDriver->driver_name,
                            'released_from_order' => $task->order_reference,
                            'reason' => $reassignmentReason,
                            'timestamp' => now()->toIso8601String(),
                        ]),
                        'ip_address' => request()->ip() ?? '127.0.0.1',
                        'user_agent' => request()->userAgent() ?? 'System Console',
                    ]);
                }
            }

            if ($task->vehicle_id) {
                $oldVehicle = Vehicle::where('id', $task->vehicle_id)->lockForUpdate()->first();
                if ($oldVehicle) {
                    $oldVehicle->update([
                        'status' => 'available',
                        'current_location' => 'Depot Yard',
                    ]);

                    AuditLog::create([
                        'user_id' => $operatorId,
                        'module' => 'Transport Department',
                        'action' => 'Vehicle Released',
                        'table_name' => 'vehicles',
                        'record_id' => $oldVehicle->id,
                        'old_values' => null,
                        'new_values' => json_encode([
                            'vehicle_number' => $oldVehicle->vehicle_number,
                            'released_from_order' => $task->order_reference,
                            'reason' => $reassignmentReason,
                            'timestamp' => now()->toIso8601String(),
                        ]),
                        'ip_address' => request()->ip() ?? '127.0.0.1',
                        'user_agent' => request()->userAgent() ?? 'System Console',
                    ]);
                }
            }

            // 3. Lock & Validate New Driver
            $newDriver = Driver::where('id', $newDriverId)->lockForUpdate()->first();
            if (!$newDriver) {
                throw new InvalidArgumentException("Selected new driver record does not exist.");
            }

            if ($newDriver->status !== 'available' || $newDriver->isSuspended() || $newDriver->isInactive() || $newDriver->isLicenseExpired()) {
                throw new InvalidArgumentException("Driver {$newDriver->driver_name} is no longer available.");
            }

            // 4. Lock & Validate New Vehicle
            $newVehicle = Vehicle::where('id', $newVehicleId)->lockForUpdate()->first();
            if (!$newVehicle) {
                throw new InvalidArgumentException("Selected new vehicle record does not exist.");
            }

            if ($newVehicle->status !== 'available' || $newVehicle->isUnderMaintenance() || $newVehicle->isBreakdown() || $newVehicle->isInactive()) {
                throw new InvalidArgumentException("Vehicle {$newVehicle->vehicle_number} is no longer available.");
            }

            // Capacity Check for New Vehicle
            $orderWeight = (float) ($task->weight_kg ?: 0.0);
            $vehicleCapacity = (float) ($newVehicle->load_capacity_kg ?: 0.0);
            if ($orderWeight > 0 && $vehicleCapacity > 0 && $orderWeight > $vehicleCapacity) {
                throw new InvalidArgumentException("Selected vehicle does not have sufficient capacity for this order.");
            }

            // 5. Generate New Assignment Number
            $nextSeq = (int) (DriverVehicleAssignment::max('id') + 1);
            $assignmentNumber = 'ASN-' . str_pad((string)$nextSeq, 6, '0', STR_PAD_LEFT);

            while (DriverVehicleAssignment::where('assignment_number', $assignmentNumber)->exists()) {
                $nextSeq++;
                $assignmentNumber = 'ASN-' . str_pad((string)$nextSeq, 6, '0', STR_PAD_LEFT);
            }

            // 6. Create New Canonical Assignment Record
            $newAssignment = DriverVehicleAssignment::create([
                'assignment_number' => $assignmentNumber,
                'transport_request_id' => $task->id,
                'sales_order_id' => $task->sales_order_id,
                'enterprise_order_id' => $task->order_reference,
                'driver_id' => $newDriver->id,
                'vehicle_id' => $newVehicle->id,
                'assigned_by' => $operatorId,
                'assigned_at' => now(),
                'status' => 'active',
                'reassignment_reason' => $reassignmentReason,
            ]);

            // 7. Update Task, Driver, and Vehicle Statuses
            $task->update([
                'driver_id' => $newDriver->id,
                'driver_name' => $newDriver->driver_name,
                'vehicle_id' => $newVehicle->id,
                'vehicle_number' => $newVehicle->vehicle_number,
                'status' => 'driver_vehicle_assigned',
                'driver_vehicle_assignment_id' => $newAssignment->id,
            ]);

            $newDriver->update([
                'status' => 'on_delivery',
                'current_assignment' => "Reassigned to Order #{$task->order_reference} (Task #{$task->request_number})",
            ]);

            $newVehicle->update([
                'status' => 'on_trip',
                'current_location' => "Reassigned to Order #{$task->order_reference} (Task #{$task->request_number})",
            ]);

            // 8. Record Reassignment Audit Log
            AuditLog::create([
                'user_id' => $operatorId,
                'module' => 'Transport Department',
                'action' => 'Assignment Reassigned',
                'table_name' => 'driver_vehicle_assignments',
                'record_id' => $newAssignment->id,
                'old_values' => json_encode(['previous_assignment' => $activeAssignment?->assignment_number]),
                'new_values' => json_encode([
                    'assignment_number' => $newAssignment->assignment_number,
                    'enterprise_order_id' => $task->order_reference,
                    'new_driver_id' => $newDriver->id,
                    'new_driver_name' => $newDriver->driver_name,
                    'new_vehicle_id' => $newVehicle->id,
                    'new_vehicle_number' => $newVehicle->vehicle_number,
                    'reason' => $reassignmentReason,
                    'operator_id' => $operatorId,
                    'timestamp' => now()->toIso8601String(),
                ]),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System Console',
            ]);

            // 9. Queue Notification for New Driver
            DriverNotification::create([
                'driver_id' => $newDriver->id,
                'assignment_id' => $newAssignment->id,
                'title' => 'New Delivery Assigned (Reassignment)',
                'enterprise_order_id' => $task->order_reference,
                'customer_name' => $task->customer_name ?? $task->salesOrder?->customer?->company_name ?? 'Customer',
                'delivery_address' => $task->delivery_address,
                'destination_city' => $task->city,
                'package_count' => $task->package_count ?: 1,
                'priority' => $task->priority ?: 'normal',
                'required_delivery_date' => $task->required_dispatch_date ?? $task->expected_delivery_date,
                'vehicle_registration_number' => $newVehicle->vehicle_number,
                'assignment_time' => now(),
                'delivery_instructions' => "Reassigned order. Reason: {$reassignmentReason}",
                'is_read' => false,
            ]);

            Log::info("TransportPlanning REASSIGN SUCCESS: Order #{$task->order_reference} reassigned to Driver {$newDriver->driver_name} & Vehicle {$newVehicle->vehicle_number}");

            return $newAssignment;
        });
    }
}
