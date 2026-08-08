<?php

declare(strict_types=1);

namespace App\Core\DTOs;

class WorkflowStateDTO
{
    public function __construct(
        public string $workflowId,
        public string $correlationId,
        public string $name,
        public string $status, // pending, in_progress, completed, failed
        public array $steps = [],
        public ?string $errorMessage = null
    ) {}

    public function addStep(string $stepName, string $status = 'completed', array $metadata = []): void
    {
        $this->steps[] = [
            'step_name' => $stepName,
            'status' => $status,
            'timestamp' => now()->toIso8601String(),
            'metadata' => $metadata,
        ];
    }
}
