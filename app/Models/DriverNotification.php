<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverNotification extends Model
{
    protected $table = 'driver_notifications';

    protected $fillable = [
        'driver_id',
        'assignment_id',
        'title',
        'enterprise_order_id',
        'customer_name',
        'delivery_address',
        'destination_city',
        'package_count',
        'priority',
        'required_delivery_date',
        'vehicle_registration_number',
        'assignment_time',
        'delivery_instructions',
        'is_read',
    ];

    protected $casts = [
        'required_delivery_date' => 'date',
        'assignment_time' => 'datetime',
        'is_read' => 'boolean',
        'package_count' => 'integer',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(DriverVehicleAssignment::class, 'assignment_id');
    }
}
