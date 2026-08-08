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
            default => 'Waiting Planning',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'waiting_planning', 'pending_packaging' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
            'vehicle_assigned_pending' => 'bg-info-subtle text-info border-info-subtle',
            'driver_assigned_pending' => 'bg-primary-subtle text-primary border-primary-subtle',
            'planning_in_progress' => 'bg-indigo-subtle text-indigo border-indigo-subtle',
            'planning_completed' => 'bg-purple-subtle text-purple border-purple-subtle',
            'accepted_by_transport' => 'bg-teal-subtle text-teal border-teal-subtle',
            'ready_for_dispatch' => 'bg-primary-subtle text-primary border-primary-subtle',
            'in_transit', 'dispatched', 'out_for_delivery' => 'bg-success-subtle text-success border-success-subtle',
            'delivered', 'completed' => 'bg-secondary-subtle text-secondary border-secondary-subtle',
            'returned_to_warehouse', 'delivery_failed' => 'bg-danger-subtle text-danger border-danger-subtle',
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
}
