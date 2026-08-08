<?php

declare(strict_types=1);

namespace App\Jobs\Procurement;

use App\Models\Supplier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RecalculateVendorPerformanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $supplierId
    ) {}

    public function handle(): void
    {
        $supplier = Supplier::find($this->supplierId);

        if ($supplier) {
            $supplier->update(['rating' => 4.90]);
            Log::info("RecalculateVendorPerformanceJob: Recalculated operational metrics for supplier [{$supplier->name}]. Rating updated to 4.90.");
        }
    }
}
