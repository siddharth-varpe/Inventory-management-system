<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerGroup extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
    ];

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }
}
