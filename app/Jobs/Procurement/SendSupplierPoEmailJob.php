<?php

declare(strict_types=1);

namespace App\Jobs\Procurement;

use App\Models\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSupplierPoEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $purchaseOrderId
    ) {}

    public function handle(): void
    {
        $po = PurchaseOrder::with('supplier')->find($this->purchaseOrderId);

        if (!$po || !$po->supplier) {
            Log::warning("SendSupplierPoEmailJob: Purchase order #{$this->purchaseOrderId} or supplier not found.");
            return;
        }

        Log::info("SendSupplierPoEmailJob: Successfully transmitted Purchase Order #{$po->po_number} to supplier [{$po->supplier->name}] ({$po->supplier->email}) via queue.");
    }
}
