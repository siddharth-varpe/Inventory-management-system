<?php

declare(strict_types=1);

namespace App\Exceptions;

use Throwable;

class DomainException extends BaseException
{
    /**
     * DomainException constructor.
     *
     * @param string $message
     * @param int $statusCode
     * @param array<string, mixed> $context
     * @param Throwable|null $previous
     */
    public function __construct(
        string $message = 'Domain business rule violation.',
        int $statusCode = 422,
        array $context = [],
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $statusCode, $context, $previous);
    }
}
