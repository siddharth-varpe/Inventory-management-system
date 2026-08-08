<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortalPermission extends Model
{
    use HasFactory;

    /**
     * Mass assignable attributes.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'portal_module_id',
        'permission_name',
    ];

    /**
     * PortalModule relationship.
     */
    public function portalModule(): BelongsTo
    {
        return $this->belongsTo(PortalModule::class);
    }
}
