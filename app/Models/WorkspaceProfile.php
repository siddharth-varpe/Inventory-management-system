<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkspaceProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'role_name',
        'layout_type',
        'sidebar_config',
        'dashboard_config',
        'quick_actions',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sidebar_config' => 'array',
            'dashboard_config' => 'array',
            'quick_actions' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
