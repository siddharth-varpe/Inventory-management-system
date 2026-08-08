<?php

declare(strict_types=1);

namespace App\Domain\StateMachine;

use App\Domain\Contracts\StateMachineInterface;
use InvalidArgumentException;

class WorkflowStateMachine implements StateMachineInterface
{
    /**
     * Allowed state transitions map.
     */
    protected array $transitions = [
        'pending' => ['assigned', 'in_progress', 'cancelled'],
        'assigned' => ['in_progress', 'pending', 'cancelled'],
        'in_progress' => ['completed', 'failed', 'cancelled'],
        'completed' => ['verified', 'closed'],
        'verified' => ['closed'],
        'closed' => [],
        'cancelled' => [],
    ];

    /**
     * {@inheritdoc}
     */
    public function canTransition(string $currentState, string $targetState): bool
    {
        $current = strtolower($currentState);
        $target = strtolower($targetState);

        if ($current === $target) {
            return true;
        }

        return in_array($target, $this->transitions[$current] ?? [], true);
    }

    /**
     * {@inheritdoc}
     */
    public function transition(object|string $context, string $currentState, string $targetState): string
    {
        if (!$this->canTransition($currentState, $targetState)) {
            $name = is_object($context) ? get_class($context) : $context;
            throw new InvalidArgumentException("Invalid state transition for [{$name}] from state '{$currentState}' to '{$targetState}'.");
        }

        return strtolower($targetState);
    }
}
