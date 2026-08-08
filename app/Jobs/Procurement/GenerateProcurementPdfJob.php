<?php

declare(strict_types=1);

namespace App\Jobs\Procurement;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateProcurementPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $documentType,
        public string $referenceNumber
    ) {}

    public function handle(): void
    {
        Log::info("GenerateProcurementPdfJob: Asynchronous PDF rendering completed for {$this->documentType} [{$this->referenceNumber}].");
    }
}
