<?php

declare(strict_types=1);

namespace App\Enums;

enum NotificationType: string
{
    case SUCCESS = 'success';
    case WARNING = 'warning';
    case ERROR = 'error';
    case INFO = 'info';

    /**
     * Get badge CSS class for notification type.
     *
     * @return string
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::SUCCESS => 'bg-success-subtle text-success border-success-subtle',
            self::WARNING => 'bg-warning-subtle text-warning border-warning-subtle',
            self::ERROR => 'bg-danger-subtle text-danger border-danger-subtle',
            self::INFO => 'bg-info-subtle text-info border-info-subtle',
        };
    }
}
