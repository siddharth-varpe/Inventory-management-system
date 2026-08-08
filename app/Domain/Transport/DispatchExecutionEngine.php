<?php

declare(strict_types=1);

namespace App\Domain\Transport;

use App\Models\TransportRequest;
use App\Models\TransportTrip;
use App\Models\DispatchManifest;
use App\Models\DispatchChecklist;
use App\Models\DeliveryTimeline;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\SalesOrder;
use App\Models\PickingTask;
use App\Models\AuditLog;
use App\Models\CrmActivity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class DispatchExecutionEngine
{
    /**
     * Step 1: Transport Accepts Custody (Digital Handover)
     */
    public function acceptCustody(TransportRequest $task, int $operatorId): TransportRequest
    {
        return DB::transaction(function () use ($task, $operatorId) {
            $task = TransportRequest::where('id', $task->id)->lockForUpdate()->firstOrFail();

            if (!in_array($task->status, ['planning_completed', 'waiting_planning', 'planning_in_progress', 'ready_for_dispatch'])) {
                if ($task->status === 'accepted_by_transport') {
                    return $task;
                }
                throw new InvalidArgumentException("Order #{$task->order_reference} is not ready for Transport Custody Acceptance.");
            }

            if (!$task->transport_trip_id || !$task->vehicle_id || !$task->driver_id) {
                throw new InvalidArgumentException("Order #{$task->order_reference} must have Vehicle, Driver, and Transport Trip assigned before custody acceptance.");
            }

            $now = now();
            $task->update([
                'status' => 'accepted_by_transport',
                'accepted_by' => $operatorId,
                'accepted_at' => $now,
                'acceptance_department' => 'Transport Department',
            ]);

            AuditLog::create([
                'user_id' => $operatorId,
                'module' => 'Transport Department',
                'action' => 'Custody Accepted',
                'table_name' => 'transport_requests',
                'record_id' => $task->id,
                'old_values' => null,
                'new_values' => json_encode([
                    'order_reference' => $task->order_reference,
                    'accepted_by' => $operatorId,
                    'accepted_at' => $now->toIso8601String(),
                    'department' => 'Transport Department',
                ]),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
            ]);

            Log::info("TransportExecution: Custody accepted for Order #{$task->order_reference} by User #{$operatorId}");

            return $task;
        });
    }

    /**
     * Step 2: Mandatory 9-Point Verification Checklist Update
     */
    public function updateChecklist(TransportRequest $task, array $checklistItems, int $operatorId): DispatchChecklist
    {
        return DB::transaction(function () use ($task, $checklistItems, $operatorId) {
            $task = TransportRequest::where('id', $task->id)->lockForUpdate()->firstOrFail();

            $checklist = DispatchChecklist::firstOrNew(['transport_request_id' => $task->id]);

            $fields = [
                'vehicle_inspected', 'packages_loaded', 'package_count_verified',
                'labels_verified', 'delivery_documents_verified', 'vehicle_doors_sealed',
                'driver_documents_verified', 'loading_completed', 'supervisor_approved'
            ];

            foreach ($fields as $field) {
                $checklist->{$field} = !empty($checklistItems[$field]);
            }

            $isCompleted = true;
            foreach ($fields as $field) {
                if (!$checklist->{$field}) {
                    $isCompleted = false;
                    break;
                }
            }

            $checklist->is_completed = $isCompleted;
            $checklist->transport_trip_id = $task->transport_trip_id;
            $checklist->verified_by = $operatorId;
            $checklist->verified_at = now();
            $checklist->save();

            AuditLog::create([
                'user_id' => $operatorId,
                'module' => 'Transport Department',
                'action' => 'Checklist Completed',
                'table_name' => 'dispatch_checklists',
                'record_id' => $checklist->id,
                'old_values' => null,
                'new_values' => json_encode([
                    'transport_request_id' => $task->id,
                    'is_completed' => $isCompleted,
                    'verified_by' => $operatorId,
                ]),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
            ]);

            if ($isCompleted && !$task->dispatch_manifest_id) {
                $this->generateManifest($task, $operatorId);
            }

            return $checklist;
        });
    }

    /**
     * Step 3: Issue Permanent Dispatch Manifest (MAN-YYYY-XXXXXX)
     */
    public function generateManifest(TransportRequest $task, int $operatorId): DispatchManifest
    {
        return DB::transaction(function () use ($task, $operatorId) {
            $task = TransportRequest::with(['transportTrip', 'vehicle', 'driver', 'salesOrder'])
                ->where('id', $task->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($task->dispatch_manifest_id && $task->dispatchManifest) {
                return $task->dispatchManifest;
            }

            $seq = DispatchManifest::count() + 1;
            $manifestNo = 'MAN-' . date('Y') . '-' . str_pad((string)$seq, 6, '0', STR_PAD_LEFT);

            $manifest = DispatchManifest::create([
                'manifest_number' => $manifestNo,
                'transport_trip_id' => $task->transport_trip_id,
                'vehicle_id' => $task->vehicle_id,
                'driver_id' => $task->driver_id,
                'package_count' => $task->package_count,
                'total_weight_kg' => $task->weight_kg,
                'total_volume_m3' => $task->volume_m3 ?? 0.5,
                'destination_summary' => $task->city,
                'dispatch_timestamp' => now(),
                'checklist_result' => true,
                'warehouse_completed_at' => $task->warehouse_completed_at ?? now(),
                'transport_accepted_at' => $task->accepted_at ?? now(),
                'warehouse_supervisor_name' => 'Warehouse Supervisor',
                'status' => 'locked',
                'created_by' => $operatorId,
            ]);

            $task->update(['dispatch_manifest_id' => $manifest->id]);

            if ($task->transportTrip && !$task->transportTrip->dispatch_manifest_id) {
                $task->transportTrip->update(['dispatch_manifest_id' => $manifest->id]);
            }

            AuditLog::create([
                'user_id' => $operatorId,
                'module' => 'Transport Department',
                'action' => 'Manifest Generated',
                'table_name' => 'dispatch_manifests',
                'record_id' => $manifest->id,
                'old_values' => null,
                'new_values' => json_encode([
                    'manifest_number' => $manifestNo,
                    'trip_number' => $task->transportTrip->trip_number ?? null,
                    'order_reference' => $task->order_reference,
                    'created_by' => $operatorId,
                ]),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
            ]);

            Log::info("TransportExecution: Manifest {$manifestNo} generated for Trip #{$task->transportTrip->trip_number}");

            return $manifest;
        });
    }

    /**
     * Step 4 & 5: Dispatch Trip Departure Execution
     */
    public function dispatchTrip(TransportRequest $task, int $operatorId): TransportTrip
    {
        return DB::transaction(function () use ($task, $operatorId) {
            $task = TransportRequest::with(['transportTrip', 'vehicle', 'driver', 'dispatchManifest', 'salesOrder'])
                ->where('id', $task->id)
                ->lockForUpdate()
                ->firstOrFail();

            $trip = TransportTrip::with(['transportRequests', 'vehicle', 'driver'])
                ->where('id', $task->transport_trip_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($trip->status === 'dispatched' || $task->status === 'in_transit') {
                return $trip;
            }

            if (!$task->accepted_at) {
                throw new InvalidArgumentException("Cannot dispatch Trip #{$trip->trip_number}: Custody must be accepted first.");
            }

            $checklist = DispatchChecklist::where('transport_request_id', $task->id)->first();
            if (!$checklist || !$checklist->is_completed) {
                throw new InvalidArgumentException("Cannot dispatch Trip #{$trip->trip_number}: Mandatory 9-point checklist must be completed.");
            }

            if (!$task->dispatch_manifest_id) {
                $manifest = $this->generateManifest($task, $operatorId);
            } else {
                $manifest = $task->dispatchManifest;
            }

            $now = now();

            $trip->update([
                'status' => 'dispatched',
                'dispatched_at' => $now,
            ]);

            foreach ($trip->transportRequests as $req) {
                $req->update([
                    'status' => 'in_transit',
                    'driver_status' => 'dispatched',
                    'dispatched_at' => $now,
                ]);

                if ($req->salesOrder) {
                    $req->salesOrder->update(['status' => 'dispatched']);
                }

                PickingTask::where('order_reference', $req->order_reference)->update([
                    'status' => 'handed_over_to_transport',
                ]);
            }

            if ($trip->vehicle) {
                $trip->vehicle->update([
                    'status' => 'on_trip',
                    'current_location' => "In Transit to {$trip->destination_city} on Trip #{$trip->trip_number}",
                ]);
            }

            if ($trip->driver) {
                $trip->driver->update([
                    'status' => 'on_delivery',
                    'current_assignment' => "In Transit on Trip #{$trip->trip_number} with Manifest #{$manifest->manifest_number} (Destination: {$trip->destination_city})",
                ]);
            }

            if ($manifest) {
                $manifest->update([
                    'status' => 'locked',
                    'dispatch_timestamp' => $now,
                ]);
            }

            DeliveryTimeline::create([
                'transport_request_id' => $task->id,
                'transport_trip_id' => $trip->id,
                'event_type' => 'Vehicle Departed',
                'status' => 'dispatched',
                'notes' => "Trip #{$trip->trip_number} departed under Manifest #{$manifest->manifest_number}",
                'user_id' => $operatorId,
                'driver_name' => $trip->driver->driver_name ?? 'Driver',
                'recorded_at' => $now,
            ]);

            AuditLog::create([
                'user_id' => $operatorId,
                'module' => 'Transport Department',
                'action' => 'Dispatch Executed',
                'table_name' => 'transport_trips',
                'record_id' => $trip->id,
                'old_values' => null,
                'new_values' => json_encode([
                    'trip_number' => $trip->trip_number,
                    'manifest_number' => $manifest->manifest_number,
                    'vehicle_number' => $trip->vehicle->vehicle_number ?? 'N/A',
                    'driver_name' => $trip->driver->driver_name ?? 'N/A',
                    'dispatched_at' => $now->toIso8601String(),
                ]),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
            ]);

            Log::info("TransportExecution: Trip #{$trip->trip_number} officially dispatched under Manifest #{$manifest->manifest_number}");

            return $trip;
        });
    }

    /**
     * PHASE 5: Official Trip Closure, Resource Release, & Enterprise Synchronization
     */
    public function closeTrip(TransportTrip $trip, int $coordinatorId): TransportTrip
    {
        return DB::transaction(function () use ($trip, $coordinatorId) {
            $trip = TransportTrip::with(['transportRequests.salesOrder', 'vehicle', 'driver', 'dispatchManifest'])
                ->where('id', $trip->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($trip->status === 'closed') {
                return $trip;
            }

            $now = now();

            // 1. Update Trip Status -> closed
            $trip->update([
                'status' => 'closed',
                'closed_at' => $now,
                'closed_by' => $coordinatorId,
            ]);

            // 2. Update Manifest Status -> closed
            if ($trip->dispatchManifest) {
                $trip->dispatchManifest->update([
                    'status' => 'closed',
                    'closed_at' => $now,
                ]);
            }

            // 3. Update Transport Tasks -> archived/completed
            foreach ($trip->transportRequests as $req) {
                if ($req->status !== 'delivered' && $req->status !== 'returned_to_warehouse') {
                    $req->update(['status' => 'delivered', 'delivered_at' => $now]);
                } else {
                    $req->update(['status' => 'completed']);
                }

                // Sync CRM Order History & Log
                if ($req->salesOrder) {
                    CrmActivity::create([
                        'customer_id' => $req->salesOrder->customer_id,
                        'activity_type' => 'note',
                        'subject' => "Trip #{$trip->trip_number} Officially Closed by Transport Dept",
                        'description' => "Logistics lifecycle for Order #{$req->order_reference} officially completed and closed by Transport Coordinator at {$now->format('d M Y, H:i')}.",
                        'activity_date' => $now,
                        'user_id' => $coordinatorId,
                    ]);
                }

                // Operational Delivery Timeline Log
                DeliveryTimeline::create([
                    'transport_request_id' => $req->id,
                    'transport_trip_id' => $trip->id,
                    'event_type' => 'Trip Closed',
                    'status' => 'closed',
                    'notes' => "Trip #{$trip->trip_number} officially closed and archived by Transport Department",
                    'user_id' => $coordinatorId,
                    'driver_name' => $trip->driver->driver_name ?? 'Driver',
                    'recorded_at' => $now,
                ]);
            }

            // 4. RESOURCE RELEASE: Vehicle -> Available
            if ($trip->vehicle) {
                $trip->vehicle->update([
                    'status' => 'available',
                    'current_location' => "Central Warehouse Yard - Checked In after Trip #{$trip->trip_number} Closure",
                ]);
            }

            // 5. RESOURCE RELEASE: Driver -> Available
            if ($trip->driver) {
                $trip->driver->update([
                    'status' => 'available',
                    'current_assignment' => "Available for Assignment - Checked In from Trip #{$trip->trip_number}",
                ]);
            }

            // 6. Billing Notification Log
            AuditLog::create([
                'user_id' => $coordinatorId,
                'module' => 'Billing System Notification',
                'action' => 'Delivery Completed & Trip Closed',
                'table_name' => 'transport_trips',
                'record_id' => $trip->id,
                'old_values' => null,
                'new_values' => json_encode([
                    'notification' => "Delivery & Trip #{$trip->trip_number} Officially Completed & Closed - Eligible for Invoicing",
                    'trip_number' => $trip->trip_number,
                    'manifest_number' => $trip->dispatchManifest->manifest_number ?? 'MAN-Closed',
                    'closed_at' => $now->toIso8601String(),
                ]),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
            ]);

            // 7. Immutable Audit Log for Trip Closure
            AuditLog::create([
                'user_id' => $coordinatorId,
                'module' => 'Transport Department',
                'action' => 'Trip Closed',
                'table_name' => 'transport_trips',
                'record_id' => $trip->id,
                'old_values' => null,
                'new_values' => json_encode([
                    'trip_number' => $trip->trip_number,
                    'manifest_number' => $trip->dispatchManifest->manifest_number ?? null,
                    'vehicle_number' => $trip->vehicle->vehicle_number ?? null,
                    'driver_name' => $trip->driver->driver_name ?? null,
                    'coordinator_id' => $coordinatorId,
                    'closed_at' => $now->toIso8601String(),
                    'final_status' => 'closed',
                ]),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
            ]);

            Log::info("TransportExecution SUCCESS: Trip #{$trip->trip_number} officially CLOSED & ARCHIVED. Vehicle & Driver released to Fleet.");

            return $trip;
        });
    }

    /**
     * PHASE 5: Operational Transport Analytics & Statistics Metrics
     */
    public function getOperationalAnalytics(): array
    {
        $today = now()->startOfDay();

        $tripsCompletedToday = TransportTrip::whereIn('status', ['closed', 'completed'])
            ->where('updated_at', '>=', $today)
            ->count();

        $activeTripsCount = TransportTrip::where('status', 'dispatched')->count();

        $pendingClosureTripsCount = TransportTrip::where('status', 'pending_closure')->count();

        $totalVehicles = Vehicle::count();
        $onTripVehicles = Vehicle::where('status', 'on_trip')->count();
        $vehicleUtilization = $totalVehicles > 0 ? round(($onTripVehicles / $totalVehicles) * 100, 1) : 0.0;

        $totalDrivers = Driver::count();
        $onDeliveryDrivers = Driver::whereIn('status', ['on_delivery', 'on_trip'])->count();
        $driverUtilization = $totalDrivers > 0 ? round(($onDeliveryDrivers / $totalDrivers) * 100, 1) : 0.0;

        $delayedTripsCount = TransportTrip::where('status', 'dispatched')
            ->where('expected_delivery_date', '<', now())
            ->count();

        $returnedTripsCount = TransportRequest::whereIn('status', ['returned_to_warehouse', 'delivery_failed'])->count();

        return [
            'trips_completed_today' => $tripsCompletedToday,
            'active_trips_count' => $activeTripsCount,
            'pending_closure_count' => $pendingClosureTripsCount,
            'vehicle_utilization_pct' => $vehicleUtilization,
            'driver_utilization_pct' => $driverUtilization,
            'delayed_trips_count' => $delayedTripsCount,
            'returned_trips_count' => $returnedTripsCount,
        ];
    }

    /**
     * Phase 5 Master Endpoint: Confirm Transactional Atomic Dispatch Execution
     */
    public function confirmDispatchOrder(TransportRequest $task, int $operatorId, ?string $notes = null): TransportRequest
    {
        return DB::transaction(function () use ($task, $operatorId, $notes) {
            // 1. Lock Task & Load Relations
            $task = TransportRequest::with(['salesOrder', 'driver', 'vehicle', 'activeAssignment'])
                ->where('id', $task->id)
                ->lockForUpdate()
                ->firstOrFail();

            // 2. Audit Event: Dispatch Confirmation Started
            AuditLog::create([
                'user_id' => $operatorId,
                'module' => 'Transport Department',
                'action' => 'Dispatch Confirmation Started',
                'table_name' => 'transport_requests',
                'record_id' => $task->id,
                'old_values' => json_encode(['status' => $task->status]),
                'new_values' => json_encode(['order_reference' => $task->order_reference, 'started_by' => $operatorId]),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
            ]);

            // 3. Concurrency Protection Check
            if (in_array($task->status, ['dispatched', 'in_transit'])) {
                throw new InvalidArgumentException("This delivery has already been dispatched.");
            }

            if ($task->status === 'cancelled') {
                throw new InvalidArgumentException("Dispatch unavailable: order has been cancelled.");
            }

            // 4. Strict 11-Point Eligibility Verification
            $eligibility = $task->dispatch_eligibility;
            if (!$eligibility['eligible']) {
                AuditLog::create([
                    'user_id' => $operatorId,
                    'module' => 'Transport Department',
                    'action' => 'Dispatch Failed',
                    'table_name' => 'transport_requests',
                    'record_id' => $task->id,
                    'old_values' => json_encode(['status' => $task->status]),
                    'new_values' => json_encode(['failure_reason' => $eligibility['reason']]),
                    'ip_address' => request()->ip() ?? '127.0.0.1',
                    'user_agent' => request()->userAgent() ?? 'System',
                ]);

                throw new InvalidArgumentException($eligibility['reason']);
            }

            // 5. Auto-Generate Unique Canonical Dispatch ID (DSP-YYYY-XXXXXX)
            if (!$task->dispatch_number) {
                $seq = (int)(TransportRequest::whereNotNull('dispatch_number')->count() + 1);
                $dispatchNo = 'DSP-' . date('Y') . '-' . str_pad((string)$seq, 6, '0', STR_PAD_LEFT);
                while (TransportRequest::where('dispatch_number', $dispatchNo)->exists()) {
                    $seq++;
                    $dispatchNo = 'DSP-' . date('Y') . '-' . str_pad((string)$seq, 6, '0', STR_PAD_LEFT);
                }
                $task->dispatch_number = $dispatchNo;
            }

            $now = now();

            // 6. Update Transport Request
            $task->update([
                'status' => 'dispatched',
                'driver_status' => 'dispatched',
                'dispatched_at' => $now,
                'dispatched_by' => $operatorId,
                'dispatch_notes' => $notes ?? $task->dispatch_notes,
            ]);

            // 7. Update Sales Order State
            if ($task->salesOrder) {
                $task->salesOrder->update(['status' => 'dispatched']);
            }

            // 8. Update Driver State -> ON DELIVERY
            if ($task->driver) {
                $task->driver->lockForUpdate();
                $task->driver->update([
                    'status' => 'on_delivery',
                    'current_assignment' => "Active Delivery #{$task->dispatch_number} for Order #{$task->order_reference} (Destination: {$task->city})",
                ]);

                AuditLog::create([
                    'user_id' => $operatorId,
                    'module' => 'Transport Department',
                    'action' => 'Driver Status Changed',
                    'table_name' => 'drivers',
                    'record_id' => $task->driver->id,
                    'old_values' => null,
                    'new_values' => json_encode([
                        'driver_name' => $task->driver->driver_name,
                        'new_status' => 'ON DELIVERY',
                        'dispatch_number' => $task->dispatch_number,
                    ]),
                    'ip_address' => request()->ip() ?? '127.0.0.1',
                    'user_agent' => request()->userAgent() ?? 'System',
                ]);
            }

            // 9. Update Vehicle State -> ON TRIP
            if ($task->vehicle) {
                $task->vehicle->lockForUpdate();
                $task->vehicle->update([
                    'status' => 'on_trip',
                    'current_location' => "In Transit to {$task->city} on Delivery #{$task->dispatch_number}",
                ]);

                AuditLog::create([
                    'user_id' => $operatorId,
                    'module' => 'Transport Department',
                    'action' => 'Vehicle Status Changed',
                    'table_name' => 'vehicles',
                    'record_id' => $task->vehicle->id,
                    'old_values' => null,
                    'new_values' => json_encode([
                        'vehicle_number' => $task->vehicle->vehicle_number,
                        'new_status' => 'ON TRIP',
                        'dispatch_number' => $task->dispatch_number,
                    ]),
                    'ip_address' => request()->ip() ?? '127.0.0.1',
                    'user_agent' => request()->userAgent() ?? 'System',
                ]);
            }

            // 10. Update Driver Vehicle Assignment
            if ($task->activeAssignment) {
                $task->activeAssignment->update([
                    'status' => 'dispatched',
                ]);
            }

            // 11. Create Real Event Delivery Timeline Entry
            DeliveryTimeline::create([
                'transport_request_id' => $task->id,
                'event_type' => 'Shipment Dispatched',
                'status' => 'dispatched',
                'notes' => "Shipment released for delivery under Dispatch ID #{$task->dispatch_number}. Driver: {$task->driver?->driver_name}, Vehicle: {$task->vehicle?->vehicle_number}",
                'user_id' => $operatorId,
                'driver_name' => $task->driver?->driver_name ?? 'Driver',
                'recorded_at' => $now,
            ]);

            // 12. Create Audit Log Events
            AuditLog::create([
                'user_id' => $operatorId,
                'module' => 'Transport Department',
                'action' => 'Dispatch Successful',
                'table_name' => 'transport_requests',
                'record_id' => $task->id,
                'old_values' => null,
                'new_values' => json_encode([
                    'order_reference' => $task->order_reference,
                    'dispatch_number' => $task->dispatch_number,
                    'driver_id' => $task->driver_id,
                    'vehicle_id' => $task->vehicle_id,
                    'dispatched_at' => $now->toIso8601String(),
                ]),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
            ]);

            AuditLog::create([
                'user_id' => $operatorId,
                'module' => 'Transport Department',
                'action' => 'Active Delivery Created',
                'table_name' => 'transport_requests',
                'record_id' => $task->id,
                'old_values' => null,
                'new_values' => json_encode([
                    'dispatch_number' => $task->dispatch_number,
                    'order_reference' => $task->order_reference,
                    'customer_name' => $task->customer_name,
                    'destination' => $task->city,
                ]),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
            ]);

            Log::info("TransportExecution: Order #{$task->order_reference} officially dispatched under Dispatch ID #{$task->dispatch_number}");

            return $task;
        });
    }

    /**
     * Phase 5 Master Endpoint: Controlled Dispatch Cancellation
     */
    public function cancelDispatchOrder(TransportRequest $task, string $reason, int $operatorId): TransportRequest
    {
        return DB::transaction(function () use ($task, $reason, $operatorId) {
            $task = TransportRequest::with(['salesOrder', 'driver', 'vehicle', 'activeAssignment'])
                ->where('id', $task->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!in_array($task->status, ['dispatched', 'in_transit'])) {
                throw new InvalidArgumentException("Only active dispatched orders can be cancelled.");
            }

            if (empty(trim($reason)) || strlen(trim($reason)) < 3) {
                throw new InvalidArgumentException("A valid cancellation reason (minimum 3 characters) is required.");
            }

            $oldStatus = $task->status;
            $now = now();

            $task->update([
                'status' => 'cancelled',
                'driver_status' => 'cancelled',
                'delivery_failure_reason' => $reason,
            ]);

            if ($task->salesOrder) {
                $task->salesOrder->update(['status' => 'cancelled']);
            }

            if ($task->activeAssignment) {
                $task->activeAssignment->update([
                    'status' => 'cancelled',
                    'reassignment_reason' => "Dispatch Cancelled: {$reason}",
                ]);
            }

            // Restore Driver Availability if no other active dispatched tasks
            if ($task->driver) {
                $otherActiveCount = TransportRequest::where('driver_id', $task->driver_id)
                    ->whereIn('status', ['dispatched', 'in_transit'])
                    ->where('id', '!=', $task->id)
                    ->count();

                if ($otherActiveCount === 0) {
                    $task->driver->update([
                        'status' => 'available',
                        'current_assignment' => null,
                    ]);
                }
            }

            // Restore Vehicle Availability if no other active dispatched tasks
            if ($task->vehicle) {
                $otherActiveCount = TransportRequest::where('vehicle_id', $task->vehicle_id)
                    ->whereIn('status', ['dispatched', 'in_transit'])
                    ->where('id', '!=', $task->id)
                    ->count();

                if ($otherActiveCount === 0) {
                    $task->vehicle->update([
                        'status' => 'available',
                        'current_location' => 'Depot Yard',
                    ]);
                }
            }

            DeliveryTimeline::create([
                'transport_request_id' => $task->id,
                'event_type' => 'Dispatch Cancelled',
                'status' => 'cancelled',
                'notes' => "Dispatch cancelled. Reason: {$reason}",
                'user_id' => $operatorId,
                'driver_name' => $task->driver?->driver_name ?? 'Driver',
                'recorded_at' => $now,
            ]);

            AuditLog::create([
                'user_id' => $operatorId,
                'module' => 'Transport Department',
                'action' => 'Dispatch Cancelled',
                'table_name' => 'transport_requests',
                'record_id' => $task->id,
                'old_values' => json_encode(['status' => $oldStatus]),
                'new_values' => json_encode([
                    'reason' => $reason,
                    'cancelled_by' => $operatorId,
                    'cancelled_at' => $now->toIso8601String(),
                ]),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
            ]);

            Log::info("TransportExecution: Dispatch #{$task->dispatch_number} for Order #{$task->order_reference} CANCELLED. Reason: {$reason}");

            return $task;
        });
    }
}
