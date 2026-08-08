<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Procurement\ProcurementOrchestratorService;
use App\Models\PurchaseOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CheckShipmentArrivals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'erp:check-shipments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically evaluate in-transit inbound shipments and trigger arrival transitions when expected delivery date is reached.';

    /**
     * Execute the console command.
     */
    public function handle(ProcurementOrchestratorService $procurementEngine): int
    {
        $now = Carbon::now();
        $this->info("Checking in-transit shipments at {$now->toDateTimeString()}...");

        $inTransitPos = PurchaseOrder::where('shipment_status', 'in_transit')
            ->where('expected_delivery_date', '<=', $now)
            ->get();

        if ($inTransitPos->isEmpty()) {
            $this->info("No shipments currently due for arrival transition.");
            return Command::SUCCESS;
        }

        $count = 0;
        foreach ($inTransitPos as $po) {
            $procurementEngine->markShipmentArrived($po->id, 1);
            $this->info("✔ PO {$po->po_number} transitioned to ARRIVED and dispatched to Goods Receipt Queue.");
            $count++;
        }

        $this->info("Successfully processed {$count} inbound shipment arrivals.");
        return Command::SUCCESS;
    }
}
