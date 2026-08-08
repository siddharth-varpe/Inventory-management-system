<?php

declare(strict_types=1);

namespace App\Domain\Transport;

use App\Models\TransportRequest;
use App\Models\TransportTrip;
use App\Models\DispatchManifest;
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

class DriverExecutionEngine
{
    /**
     * Predefined Allowed Operational Delivery Statuses
     */
    public const ALLOWED_STATUSES = [
        'accepted' => 'Driver Accepted',
        'reached_destination' => 'Reached Destination',
        'delivery_attempt' => 'Delivery Attempt',
        'delivered' => 'Delivered',
        'customer_unavailable' => 'Customer Unavailable',
        'delivery_refused' => 'Delivery Refused',
        'address_not_found' => 'Address Not Found',
        'vehicle_breakdown' => 'Vehicle Breakdown',
        'returned_to_warehouse' => 'Returned To Warehouse',
    ];

    /**
     * Driver Accepts Assigned Trip
     */
    public function acceptTrip(TransportTrip $trip, Driver $driver, int $userId): TransportTrip
    {
        return DB::transaction(function () use ($trip, $driver, $userId) {
            $trip = TransportTrip::with(['transportRequests', 'vehicle', 'driver'])
                ->where('id', $trip->id)
                ->lockForUpdate()
                ->firstOrFail();

            foreach ($trip->transportRequests as $task) {
                if ($task->driver_status === 'dispatched') {
                    $task->update(['driver_status' => 'accepted']);

                    DeliveryTimeline::create([
                        'transport_request_id' => $task->id,
                        'transport_trip_id' => $trip->id,
                        'event_type' => 'Driver Accepted',
                        'status' => 'accepted',
                        'notes' => "Driver {$driver->driver_name} accepted custody of Trip #{$trip->trip_number}",
                        'user_id' => $userId,
                        'driver_name' => $driver->driver_name,
                        'recorded_at' => now(),
                    ]);
                }
            }

            AuditLog::create([
                'user_id' => $userId,
                'module' => 'Driver Terminal',
                'action' => 'Trip Accepted',
                'table_name' => 'transport_trips',
                'record_id' => $trip->id,
                'old_values' => null,
                'new_values' => json_encode([
                    'trip_id' => $trip->trip_number,
                    'driver_name' => $driver->driver_name,
                    'timestamp' => now()->toIso8601String(),
                ]),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'Driver Console',
            ]);

            Log::info("DriverExecution: Driver {$driver->driver_name} accepted Trip #{$trip->trip_number}");

            return $trip;
        });
    }

    /**
     * Driver Delivery Status Update & Confirmation
     */
    public function updateDeliveryStatus(TransportRequest $task, string $newStatus, ?string $notes, Driver $driver, int $userId): TransportRequest
    {
        // Predefined Status Check
        if (!array_key_exists($newStatus, self::ALLOWED_STATUSES)) {
            throw new InvalidArgumentException("Invalid delivery status '{$newStatus}'. Predefined operational delivery statuses only.");
        }

        // Exception Mandatory Remarks Check
        $exceptionStatuses = ['customer_unavailable', 'delivery_refused', 'address_not_found', 'vehicle_breakdown', 'returned_to_warehouse'];
        if (in_array($newStatus, $exceptionStatuses) && empty(trim($notes ?? ''))) {
            throw new InvalidArgumentException("Mandatory remarks/notes required when reporting exception status '" . self::ALLOWED_STATUSES[$newStatus] . "'.");
        }

        return DB::transaction(function () use ($task, $newStatus, $notes, $driver, $userId) {
            $task = TransportRequest::with(['transportTrip', 'salesOrder', 'vehicle', 'driver', 'dispatchManifest'])
                ->where('id', $task->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Prevent modification of already finalized/delivered orders
            if ($task->status === 'delivered' || $task->driver_status === 'delivered') {
                throw new InvalidArgumentException("Delivery for Order #{$task->order_reference} has already been confirmed and finalized.");
            }

            $now = now();
            $eventLabel = self::ALLOWED_STATUSES[$newStatus];

            $updateData = [
                'driver_status' => $newStatus,
            ];
            if (!empty($notes)) {
                $updateData['delivery_notes'] = $notes;
            }

            // DELIVERY CONFIRMATION (Delivered)
            if ($newStatus === 'delivered') {
                $updateData['status'] = 'delivered';
                $updateData['delivered_at'] = $now;
                $updateData['delivery_confirmed_at'] = $now;

                // Sync Enterprise Order
                if ($task->salesOrder) {
                    $task->salesOrder->update(['status' => 'delivered']);

                    CrmActivity::create([
                        'customer_id' => $task->salesOrder->customer_id,
                        'activity_type' => 'note',
                        'subject' => "Order #{$task->order_reference} Successfully Delivered",
                        'description' => "Shipment delivered by Driver {$driver->driver_name} under Trip #{$task->transportTrip->trip_number}. Delivered at {$now->format('d M Y, H:i')}.",
                        'activity_date' => $now,
                        'user_id' => $userId,
                    ]);
                }

                // Sync Warehouse
                PickingTask::where('order_reference', $task->order_reference)->update([
                    'status' => 'completed',
                    'completed_at' => $now,
                ]);
            }

            // RETURNED TO WAREHOUSE EXCEPTION
            if ($newStatus === 'returned_to_warehouse') {
                $updateData['status'] = 'returned_to_warehouse';
                $updateData['delivery_failure_reason'] = $notes;

                if ($task->salesOrder) {
                    $task->salesOrder->update(['status' => 'returned']);

                    CrmActivity::create([
                        'customer_id' => $task->salesOrder->customer_id,
                        'activity_type' => 'note',
                        'subject' => "Order #{$task->order_reference} Returned to Warehouse",
                        'description' => "Shipment returned by Driver {$driver->driver_name}. Reason: {$notes}",
                        'activity_date' => $now,
                        'user_id' => $userId,
                    ]);
                }

                PickingTask::where('order_reference', $task->order_reference)->update([
                    'status' => 'returned',
                ]);
            }

            if (in_array($newStatus, ['customer_unavailable', 'delivery_refused', 'address_not_found', 'vehicle_breakdown'])) {
                $updateData['delivery_failure_reason'] = $notes;
            }

            $task->update($updateData);

            // Operational Delivery Timeline Log
            DeliveryTimeline::create([
                'transport_request_id' => $task->id,
                'transport_trip_id' => $task->transport_trip_id,
                'event_type' => $eventLabel,
                'status' => $newStatus,
                'notes' => $notes,
                'user_id' => $userId,
                'driver_name' => $driver->driver_name,
                'recorded_at' => $now,
            ]);

            // Immutable Audit Log
            AuditLog::create([
                'user_id' => $userId,
                'module' => 'Driver Terminal',
                'action' => $newStatus === 'delivered' ? 'Delivery Completed' : ($newStatus === 'returned_to_warehouse' ? 'Delivery Failed' : 'Status Update'),
                'table_name' => 'transport_requests',
                'record_id' => $task->id,
                'old_values' => null,
                'new_values' => json_encode([
                    'transport_task_id' => $task->request_number,
                    'enterprise_order_id' => $task->order_reference,
                    'driver_status' => $newStatus,
                    'driver_name' => $driver->driver_name,
                    'notes' => $notes,
                    'timestamp' => $now->toIso8601String(),
                ]),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'Driver Console',
            ]);

            // Check if full Trip is complete (releases vehicle & driver for future trips)
            if ($task->transportTrip) {
                $this->checkTripCompletion($task->transportTrip);
            }

            Log::info("DriverExecution SUCCESS: Task #{$task->request_number} updated to '{$eventLabel}' by Driver {$driver->driver_name}");

            return $task;
        });
    }

    /**
     * Check Trip Completion & Release Vehicle/Driver Availability
     */

    public function checkTripCompletion(TransportTrip $trip): bool
    {
        $allFinalized = true;
        foreach ($trip->transportRequests as $req) {
            if (!in_array($req->status, ['delivered', 'completed', 'returned_to_warehouse'])) {
                $allFinalized = false;
                break;
            }
        }

        if ($allFinalized && $trip->status !== 'completed') {
            $trip->update(['status' => 'completed']);

            // Release Vehicle Availability
            if ($trip->vehicle) {
                $trip->vehicle->update([
                    'status' => 'available',
                    'current_location' => "Returned to Central Warehouse Yard from Trip #{$trip->trip_number}",
                ]);
            }

            // Release Driver Availability
            if ($trip->driver) {
                $trip->driver->update([
                    'status' => 'available',
                    'current_assignment' => "Available at Dispatch Standby Bay",
                ]);
            }

            Log::info("DriverExecution SUCCESS: Trip #{$trip->trip_number} completed. Vehicle {$trip->vehicle->vehicle_number} & Driver {$trip->driver->driver_name} released to Available Fleet.");

            return true;
        }

        return false;
    }
}
