<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

abstract class BaseException extends Exception
{
    /**
     * HTTP Status code associated with exception.
     *
     * @var int
     */
    protected int $statusCode = 500;

    /**
     * Context data for logging.
     *
     * @var array<string, mixed>
     */
    protected array $context = [];

    /**
     * BaseException constructor.
     *
     * @param string $message
     * @param int $statusCode
     * @param array<string, mixed> $context
     * @param Throwable|null $previous
     */
    public function __construct(
        string $message = 'An unexpected error occurred.',
        int $statusCode = 500,
        array $context = [],
        ?Throwable $previous = null
    ) {
        $this->statusCode = $statusCode;
        $this->context = $context;
        parent::__construct($message, $statusCode, $previous);
    }

    /**
     * Get the HTTP status code.
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get exception context for logging.
     *
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param Request $request
     * @return JsonResponse|RedirectResponse|Response
     */
    public function render(Request $request): JsonResponse|RedirectResponse|Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => $this->getMessage(),
                'error_code' => class_basename($this),
            ], $this->statusCode);
        }

        return redirect()->back()->with('error', $this->getMessage());
    }
}
