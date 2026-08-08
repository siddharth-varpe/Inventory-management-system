<?php

declare(strict_types=1);

namespace App\Domain\Communication\Exceptions;

use RuntimeException;

class CommunicationValidationException extends RuntimeException
{
    public function __construct(public readonly array $errors, string $message = "Communication Validation Failed")
    {
        parent::__construct($message . ": " . implode(' | ', $errors));
    }
}
