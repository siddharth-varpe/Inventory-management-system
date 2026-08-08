<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Territory extends Model
{
    protected $fillable = [
        'name',
        'code',
        'region',
        'sales_zone',
        'country',
        'state',
        'district',
        'city',
        'pin_code_mapping',
    ];

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }
}
