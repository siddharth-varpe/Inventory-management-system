<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasActivityLog;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes, HasAuditLog, HasActivityLog;

    /**
     * Mass assignable attributes.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'legal_name',
        'business_type',
        'industry',
        'gst_number',
        'pan_number',
        'cin_number',
        'logo',
        'email',
        'phone',
        'website',
        'address_line_1',
        'address_line_2',
        'country',
        'state',
        'city',
        'postal_code',
        'currency',
        'timezone',
        'financial_year',
        'language',
        'status',
        'created_by',
        'updated_by',
    ];

    /**
     * Company branches relationship.
     *
     * @return HasMany
     */
    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }
}
