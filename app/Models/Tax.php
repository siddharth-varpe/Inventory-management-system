<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasActivityLog;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tax extends Model
{
    use HasFactory, SoftDeletes, HasAuditLog, HasActivityLog;

    protected static function booted(): void
    {
        static::creating(function (Tax $tax): void {
            if (empty($tax->code)) {
                $slug = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $tax->name ?? 'TAX'));
                $tax->code = 'TAX-' . substr($slug, 0, 6) . '-' . strtoupper(\Illuminate\Support\Str::random(4));
            }
        });
    }

    /**
     * Mass assignable attributes.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'rate',
        'type',
        'effective_from',
        'effective_to',
        'status',
        'description',
    ];

    /**
     * Cast attributes.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'effective_from' => 'date',
            'effective_to' => 'date',
        ];
    }
}
