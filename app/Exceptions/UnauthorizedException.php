<?php

declare(strict_types=1);

namespace App\Exceptions;

use Throwable;

class UnauthorizedException extends BaseException
{
    /**
     * UnauthorizedException constructor.
     *
     * @param string $message
     * @param int $statusCode
     * @param array<string, mixed> $context
     * @param Throwable|null $previous
     */
    public function __construct(
        string $message = 'Unauthorized action or access level required.',
        int $statusCode = 403,
        array $context = [],
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $statusCode, $context, $previous);
    }
}
