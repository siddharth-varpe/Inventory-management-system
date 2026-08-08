<?php

declare(strict_types=1);

namespace App\Services\Logging;

use Illuminate\Support\Facades\Log;

class EnterpriseLogger
{
    /**
     * Log general application events.
     *
     * @param string $message
     * @param array<string, mixed> $context
     * @return void
     */
    public static function app(string $message, array $context = []): void
    {
        Log::channel('app')->info($message, self::enrichContext($context));
    }

    /**
     * Log security events (Auth events, permission checks, suspicious activity).
     *
     * @param string $message
     * @param array<string, mixed> $context
     * @return void
     */
    public static function security(string $message, array $context = []): void
    {
        Log::channel('security')->notice($message, self::enrichContext($context));
    }

    /**
     * Log system errors.
     *
     * @param string $message
     * @param array<string, mixed> $context
     * @return void
     */
    public static function systemError(string $message, array $context = []): void
    {
        Log::channel('system_errors')->error($message, self::enrichContext($context));
    }

    /**
     * Log audit trail events for compliance.
     *
     * @param string $action
     * @param string $resourceType
     * @param int|string|null $resourceId
     * @param array<string, mixed> $changes
     * @return void
     */
    public static function audit(
        string $action,
        string $resourceType,
        int|string|null $resourceId = null,
        array $changes = []
    ): void {
        $context = [
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'changes' => $changes,
        ];

        Log::channel('audit')->info("AUDIT: {$action} on {$resourceType}", self::enrichContext($context));
    }

    /**
     * Enrich context with user ID, IP address, and Request URI if available.
     *
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    protected static function enrichContext(array $context): array
    {
        return array_merge([
            'user_id' => auth()->id() ?? 'guest',
            'ip' => request()->ip() ?? 'cli',
            'url' => request()->fullUrl() ?? 'cli',
            'timestamp' => now()->toIso8601String(),
        ], $context);
    }
}
