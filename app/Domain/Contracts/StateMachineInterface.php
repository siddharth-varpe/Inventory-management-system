<?php

declare(strict_types=1);

namespace App\Domain\Contracts;

interface StateMachineInterface
{
    /**
     * Verify if a state transition is allowed from currentState to targetState.
     */
    public function canTransition(string $currentState, string $targetState): bool;

    /**
     * Transition an entity or workflow state, throwing an exception if invalid.
     */
    public function transition(object|string $context, string $currentState, string $targetState): string;
}
