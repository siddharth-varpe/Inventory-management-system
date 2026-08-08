<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasActivityLog;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DispatchTask extends Model
{
    use HasFactory, HasAuditLog, HasActivityLog;

    protected $fillable = [
        'dispatch_number',
        'picking_task_id',
        'order_reference',
        'customer_name',
        'delivery_address',
        'total_items',
        'total_weight_kg',
        'shipping_label_code',
        'status',
        'created_by',
    ];

    public function pickingTask(): BelongsTo
    {
        return $this->belongsTo(PickingTask::class);
    }
}
