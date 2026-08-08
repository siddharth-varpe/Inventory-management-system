<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TransportRequest extends Model
{
    protected $fillable = [
        'request_number',
        'sales_order_id',
        'order_reference',
        'customer_name',
        'delivery_address',
        'delivery_city',
        'contact_person',
        'phone_number',
        'priority',
        'expected_delivery_date',
        'required_dispatch_date',
        'package_count',
        'package_type',
        'weight_kg',
        'volume_m3',
        'dimensions',
        'warehouse_completed_at',
        'source_module',
        'warehouse_status',
        'status',
        'driver_status',
        'delivery_notes',
        'delivery_confirmed_at',
        'delivery_failure_reason',
        'transport_trip_id',
        'dispatch_manifest_id',
        'vehicle_id',
        'driver_id',
        'accepted_by',
        'accepted_at',
        'acceptance_department',
        'carrier',
        'driver_name',
        'vehicle_number',
        'tracking_number',
        'route_name',
        'dispatched_at',
        'delivered_at',
        'created_by',
        'assigned_driver_id',
        'driver_vehicle_assignment_id',
    ];

    protected $casts = [
        'expected_delivery_date' => 'date',
        'required_dispatch_date' => 'date',
        'warehouse_completed_at' => 'datetime',
        'accepted_at' => 'datetime',
        'dispatched_at' => 'datetime',
        'delivered_at' => 'datetime',
        'delivery_confirmed_at' => 'datetime',
        'weight_kg' => 'decimal:2',
        'volume_m3' => 'decimal:2',
    ];

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }

    public function transportTrip(): BelongsTo
    {
        return $this->belongsTo(TransportTrip::class, 'transport_trip_id');
    }

    public function dispatchManifest(): BelongsTo
    {
        return $this->belongsTo(DispatchManifest::class, 'dispatch_manifest_id');
    }

    public function dispatchChecklist(): HasOne
    {
        return $this->hasOne(DispatchChecklist::class, 'transport_request_id');
    }

    public function deliveryTimelines(): HasMany
    {
        return $this->hasMany(DeliveryTimeline::class, 'transport_request_id')->orderBy('recorded_at', 'asc');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    public function activeAssignment(): BelongsTo
    {
        return $this->belongsTo(DriverVehicleAssignment::class, 'driver_vehicle_assignment_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(DriverVehicleAssignment::class, 'transport_request_id');
    }

    public function acceptedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedDriver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_driver_id');
    }

    public function getCityAttribute(): string
    {
        if (!empty($this->delivery_city)) {
            return $this->delivery_city;
        }

        if (!empty($this->delivery_address)) {
            $parts = explode(',', $this->delivery_address);
            $city = trim(end($parts));
            if (!empty($city) && strlen($city) > 2 && strlen($city) < 30) {
                return $city;
            }
            if (count($parts) >= 2) {
                return trim($parts[count($parts) - 2]);
            }
        }

        return 'Mumbai';
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'awaiting_warehouse' => 'Awaiting Warehouse',
            'ready_for_assignment' => 'Ready for Assignment',
            'driver_vehicle_assigned', 'assigned' => 'Driver & Vehicle Assigned',
            'waiting_planning', 'pending_packaging' => 'Waiting Planning',
            'vehicle_assigned_pending' => 'Vehicle Assigned',
            'driver_assigned_pending' => 'Driver Assigned',
            'planning_in_progress' => 'Planning In Progress',
            'planning_completed' => 'Planning Completed',
            'accepted_by_transport' => 'Accepted By Transport',
            'ready_for_dispatch' => 'Ready for Dispatch',
            'in_transit', 'dispatched', 'out_for_delivery' => 'In Transit',
            'delivered', 'completed' => 'Delivered',
            'returned_to_warehouse' => 'Returned To Warehouse',
            'delivery_failed' => 'Delivery Failed',
            'cancelled' => 'Cancelled',
            default => 'Awaiting Warehouse',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'awaiting_warehouse' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
            'ready_for_assignment' => 'bg-success-subtle text-success border-success-subtle',
            'driver_vehicle_assigned', 'assigned' => 'bg-info-subtle text-info border-info-subtle',
            'waiting_planning', 'pending_packaging' => 'bg-info-subtle text-info border-info-subtle',
            'vehicle_assigned_pending' => 'bg-info-subtle text-info border-info-subtle',
            'driver_assigned_pending' => 'bg-primary-subtle text-primary border-primary-subtle',
            'planning_in_progress' => 'bg-indigo-subtle text-indigo border-indigo-subtle',
            'planning_completed' => 'bg-purple-subtle text-purple border-purple-subtle',
            'accepted_by_transport' => 'bg-teal-subtle text-teal border-teal-subtle',
            'ready_for_dispatch' => 'bg-primary-subtle text-primary border-primary-subtle',
            'in_transit', 'dispatched', 'out_for_delivery' => 'bg-success-subtle text-success border-success-subtle',
            'delivered', 'completed' => 'bg-secondary-subtle text-secondary border-secondary-subtle',
            'returned_to_warehouse', 'delivery_failed', 'cancelled' => 'bg-danger-subtle text-danger border-danger-subtle',
            default => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
        };
    }

    public function getWarehouseStatusLabelAttribute(): string
    {
        if ($this->warehouse_completed_at || in_array($this->status, ['ready_for_assignment', 'ready_for_dispatch', 'in_transit', 'dispatched', 'delivered', 'completed'])) {
            return 'Seal & Ready to Dispatch';
        }

        return match($this->warehouse_status ?? 'picking_in_progress') {
            'pending', 'picking', 'picking_in_progress' => 'Picking & Packing In Progress',
            'picked', 'packed' => 'Packed',
            'seal_ready', 'ready_for_dispatch', 'completed' => 'Seal & Ready to Dispatch',
            default => 'Picking & Packing In Progress',
        };
    }

    public function getWarehouseStatusBadgeClassAttribute(): string
    {
        if ($this->warehouse_completed_at || in_array($this->status, ['ready_for_assignment', 'ready_for_dispatch', 'in_transit', 'dispatched', 'delivered', 'completed'])) {
            return 'bg-success-subtle text-success border-success-subtle';
        }

        return match($this->warehouse_status ?? 'picking_in_progress') {
            'pending', 'picking', 'picking_in_progress' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
            'picked', 'packed' => 'bg-info-subtle text-info border-info-subtle',
            'seal_ready', 'ready_for_dispatch', 'completed' => 'bg-success-subtle text-success border-success-subtle',
            default => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
        };
    }

    public function getDriverStatusLabelAttribute(): string
    {
        return match($this->driver_status) {
            'dispatched' => 'Trip Assigned',
            'accepted' => 'Driver Accepted',
            'reached_destination' => 'Reached Destination',
            'attempted' => 'Delivery Attempt',
            'delivered' => 'Delivered',
            'customer_unavailable' => 'Customer Unavailable',
            'delivery_refused' => 'Delivery Refused',
            'address_not_found' => 'Address Not Found',
            'vehicle_breakdown' => 'Vehicle Breakdown',
            'returned_to_warehouse' => 'Returned To Warehouse',
            default => 'Trip Assigned',
        };
    }

    public function getPriorityBadgeClassAttribute(): string
    {
        return match(strtolower($this->priority ?? 'normal')) {
            'urgent' => 'bg-danger text-white',
            'high' => 'bg-warning text-dark',
            'normal', 'medium' => 'bg-info-subtle text-info border-info-subtle',
            'low' => 'bg-secondary-subtle text-secondary border-secondary-subtle',
            default => 'bg-secondary-subtle text-secondary border-secondary-subtle',
        };
    }

    public function getTimelineEventsAttribute(): array
    {
        $events = [];

        // 1. Sales Order Created
        if ($this->salesOrder) {
            $events[] = [
                'title' => 'Sales Order Created',
                'description' => "Order #{$this->order_reference} created in CRM",
                'timestamp' => $this->salesOrder->created_at?->format('Y-m-d H:i:s') ?? $this->created_at->format('Y-m-d H:i:s'),
                'icon' => '🛒',
                'status' => 'completed',
            ];
        }

        // 2. Transport Task Created
        $events[] = [
            'title' => 'Transport Task Created',
            'description' => "Task #{$this->request_number} linked (Awaiting Warehouse)",
            'timestamp' => $this->created_at->format('Y-m-d H:i:s'),
            'icon' => '📦',
            'status' => 'completed',
        ];

        // 3. Warehouse Pick & Pack
        if ($this->warehouse_completed_at || in_array($this->status, ['ready_for_assignment', 'ready_for_dispatch', 'in_transit', 'dispatched', 'delivered', 'completed'])) {
            $events[] = [
                'title' => 'Pick & Pack Completed',
                'description' => 'Warehouse items picked and verified',
                'timestamp' => $this->warehouse_completed_at?->format('Y-m-d H:i:s') ?? $this->created_at->addMinutes(15)->format('Y-m-d H:i:s'),
                'icon' => '🏭',
                'status' => 'completed',
            ];
            $events[] = [
                'title' => 'Seal & Ready to Dispatch',
                'description' => 'Order sealed and marked ready in Organize Stock',
                'timestamp' => $this->warehouse_completed_at?->format('Y-m-d H:i:s') ?? $this->created_at->addMinutes(20)->format('Y-m-d H:i:s'),
                'icon' => '🔒',
                'status' => 'completed',
            ];
            $events[] = [
                'title' => 'Ready for Assignment',
                'description' => 'Transport status automatically transitioned to Ready for Assignment',
                'timestamp' => $this->warehouse_completed_at?->format('Y-m-d H:i:s') ?? $this->updated_at->format('Y-m-d H:i:s'),
                'icon' => '✅',
                'status' => 'completed',
            ];

            if ($this->driver_id && $this->vehicle_id) {
                $assignment = $this->activeAssignment;
                $events[] = [
                    'title' => 'Driver & Vehicle Assigned',
                    'description' => "Assigned Driver: {$this->driver_name} | Vehicle: {$this->vehicle_number}" . ($assignment ? " (Ref: {$assignment->assignment_number})" : ""),
                    'timestamp' => $assignment?->assigned_at?->format('Y-m-d H:i:s') ?? $this->updated_at->format('Y-m-d H:i:s'),
                    'icon' => '🚚',
                    'status' => 'completed',
                ];
            }
        } else {
            $events[] = [
                'title' => 'Awaiting Warehouse Completion',
                'description' => 'Organize Stock is performing Pick & Pack',
                'timestamp' => null,
                'icon' => '⏳',
                'status' => 'pending',
            ];
        }

        // 4. Dispatched Event
        if ($this->dispatched_at) {
            $events[] = [
                'title' => 'Shipment Dispatched',
                'description' => "Dispatched via Trip #{$this->transportTrip?->trip_number}",
                'timestamp' => $this->dispatched_at->format('Y-m-d H:i:s'),
                'icon' => '🚀',
                'status' => 'completed',
            ];
        }

        // 5. Delivered Event
        if ($this->delivered_at) {
            $events[] = [
                'title' => 'Shipment Delivered',
                'description' => 'Goods successfully delivered to customer',
                'timestamp' => $this->delivered_at->format('Y-m-d H:i:s'),
                'icon' => '🎉',
                'status' => 'completed',
            ];
        }

        // 6. Cancelled Event
        if ($this->status === 'cancelled') {
            $events[] = [
                'title' => 'Transport Task Cancelled',
                'description' => $this->delivery_failure_reason ?? 'Order cancelled',
                'timestamp' => $this->updated_at->format('Y-m-d H:i:s'),
                'icon' => '❌',
                'status' => 'cancelled',
            ];
        }

        return $events;
    }

}
