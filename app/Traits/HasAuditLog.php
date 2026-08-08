<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

trait HasAuditLog
{
    /**
     * Boot trait to attach Eloquent event listeners.
     */
    public static function bootHasAuditLog(): void
    {
        static::created(function (Model $model) {
            self::logAuditRecord($model, 'created', null, $model->getAttributes());
        });

        static::updated(function (Model $model) {
            $old = array_intersect_key($model->getOriginal(), $model->getDirty());
            $new = $model->getDirty();
            self::logAuditRecord($model, 'updated', $old, $new);
        });

        static::deleted(function (Model $model) {
            self::logAuditRecord($model, 'deleted', $model->getOriginal(), null);
        });
    }

    /**
     * Record audit log entry.
     *
     * @param Model $model
     * @param string $action
     * @param array<string, mixed>|null $oldValues
     * @param array<string, mixed>|null $newValues
     * @return void
     */
    protected static function logAuditRecord(Model $model, string $action, ?array $oldValues, ?array $newValues): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'module' => class_basename($model),
            'action' => $action,
            'table_name' => $model->getTable(),
            'record_id' => $model->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip() ?? 'cli',
            'user_agent' => request()->userAgent() ?? 'cli',
            'created_at' => now(),
        ]);
    }
}
