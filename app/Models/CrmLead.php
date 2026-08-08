<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CrmLead extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'lead_number',
        'company_name',
        'contact_person',
        'phone',
        'email',
        'source',
        'industry',
        'expected_revenue',
        'probability',
        'salesperson_id',
        'territory_id',
        'priority',
        'status',
        'expected_closing_date',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'expected_closing_date' => 'date',
        'expected_revenue' => 'decimal:2',
        'probability' => 'integer',
    ];

    public function salesperson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesperson_id');
    }

    public function territory(): BelongsTo
    {
        return $this->belongsTo(Territory::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(CrmActivity::class, 'lead_id');
    }

    public function followups(): HasMany
    {
        return $this->hasMany(CrmFollowup::class, 'lead_id');
    }

    public function meetings(): HasMany
    {
        return $this->hasMany(CrmMeeting::class, 'lead_id');
    }
}
