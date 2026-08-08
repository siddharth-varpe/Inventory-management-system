<?php

declare(strict_types=1);

namespace App\Core\Transactions;

use App\Core\Correlation\CorrelationContext;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class TransactionalWorkflowRunner
{
    /**
     * Wrap cross-portal multi-step operations inside strict database transactions with correlation logging.
     *
     * @template T
     * @param string $workflowName
     * @param Closure(): T $action
     * @return T
     * @throws Throwable
     */
    public static function run(string $workflowName, Closure $action): mixed
    {
        $correlationId = CorrelationContext::getCorrelationId();
        $workflowId = CorrelationContext::getWorkflowId();

        Log::info("TransactionalWorkflowRunner: STARTING workflow '{$workflowName}' [WorkflowID: {$workflowId} | CorrelationID: {$correlationId}]");

        try {
            $result = DB::transaction(function () use ($action) {
                return $action();
            });

            Log::info("TransactionalWorkflowRunner: COMPLETED workflow '{$workflowName}' [WorkflowID: {$workflowId} | CorrelationID: {$correlationId}]");
            return $result;
        } catch (Throwable $e) {
            Log::error("TransactionalWorkflowRunner: FAILED workflow '{$workflowName}' - ROLLED BACK! Error: {$e->getMessage()} [WorkflowID: {$workflowId} | CorrelationID: {$correlationId}]");
            throw $e;
        }
    }
}
