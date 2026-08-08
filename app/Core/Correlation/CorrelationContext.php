<?php

declare(strict_types=1);

namespace App\Core\Correlation;

use Illuminate\Support\Str;

class CorrelationContext
{
    protected static ?string $currentCorrelationId = null;
    protected static ?string $currentWorkflowId = null;

    /**
     * Get current correlation ID or generate a new one if empty.
     */
    public static function getCorrelationId(): string
    {
        if (self::$currentCorrelationId === null) {
            self::$currentCorrelationId = 'CORR-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        }

        return self::$currentCorrelationId;
    }

    /**
     * Set explicit correlation ID.
     */
    public static function setCorrelationId(string $id): void
    {
        self::$currentCorrelationId = $id;
    }

    /**
     * Get current workflow ID or generate a new one.
     */
    public static function getWorkflowId(): string
    {
        if (self::$currentWorkflowId === null) {
            self::$currentWorkflowId = 'WF-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        }

        return self::$currentWorkflowId;
    }

    /**
     * Set explicit workflow ID.
     */
    public static function setWorkflowId(string $id): void
    {
        self::$currentWorkflowId = $id;
    }

    /**
     * Reset context for fresh execution.
     */
    public static function reset(): void
    {
        self::$currentCorrelationId = null;
        self::$currentWorkflowId = null;
    }
}
