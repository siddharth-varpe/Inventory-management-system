<?php

declare(strict_types=1);

namespace App\Domain\Contracts;

interface TaskGeneratorInterface
{
    /**
     * Generate a Put-Away storage request task from a Goods Receipt.
     */
    public function generatePutAwayTask(array $data): object;

    /**
     * Generate a Picking task from an approved Sales/Pick order.
     */
    public function generatePickingTask(array $data): object;

    /**
     * Generate a Packing task from a completed Picking task.
     */
    public function generatePackingTask(array $data): object;

    /**
     * Generate a Dispatch task from a completed Packing task.
     */
    public function generateDispatchTask(array $data): object;

    /**
     * Generate a Transfer task from an internal transfer request.
     */
    public function generateTransferTask(array $data): object;

    /**
     * Generate a Cycle Count verification task.
     */
    public function generateCycleCountTask(array $data): object;
}
