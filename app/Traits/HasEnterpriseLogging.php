<?php

declare(strict_types=1);

namespace App\Traits;

use App\Services\Logging\EnterpriseLogger;

trait HasEnterpriseLogging
{
    /**
     * Log application info message.
     *
     * @param string $message
     * @param array<string, mixed> $context
     * @return void
     */
    protected function logApp(string $message, array $context = []): void
    {
        EnterpriseLogger::app($message, $context);
    }

    /**
     * Log security message.
     *
     * @param string $message
     * @param array<string, mixed> $context
     * @return void
     */
    protected function logSecurity(string $message, array $context = []): void
    {
        EnterpriseLogger::security($message, $context);
    }

    /**
     * Log system error message.
     *
     * @param string $message
     * @param array<string, mixed> $context
     * @return void
     */
    protected function logError(string $message, array $context = []): void
    {
        EnterpriseLogger::systemError($message, $context);
    }

    /**
     * Log audit message.
     *
     * @param string $action
     * @param string $resourceType
     * @param int|string|null $resourceId
     * @param array<string, mixed> $changes
     * @return void
     */
    protected function logAudit(
        string $action,
        string $resourceType,
        int|string|null $resourceId = null,
        array $changes = []
    ): void {
        EnterpriseLogger::audit($action, $resourceType, $resourceId, $changes);
    }
}
