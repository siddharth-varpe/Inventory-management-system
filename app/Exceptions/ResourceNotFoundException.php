<?php

declare(strict_types=1);

namespace App\Exceptions;

use Throwable;

class ResourceNotFoundException extends BaseException
{
    /**
     * ResourceNotFoundException constructor.
     *
     * @param string $message
     * @param int $statusCode
     * @param array<string, mixed> $context
     * @param Throwable|null $previous
     */
    public function __construct(
        string $message = 'Requested resource was not found.',
        int $statusCode = 404,
        array $context = [],
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $statusCode, $context, $previous);
    }
}
