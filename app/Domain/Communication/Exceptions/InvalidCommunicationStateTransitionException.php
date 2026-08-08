<?php

declare(strict_types=1);

namespace App\Domain\Communication\Exceptions;

use RuntimeException;

class InvalidCommunicationStateTransitionException extends RuntimeException
{
    public function __construct(string $fromStatus, string $toStatus, string $recordNumber)
    {
        parent::__construct(
            "Illegal communication lifecycle state transition from '{$fromStatus}' to '{$toStatus}' for Record #{$recordNumber}."
        );
    }
}
