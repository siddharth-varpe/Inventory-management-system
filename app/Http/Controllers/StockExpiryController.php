<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\StockAdjustment;
use App\Repositories\Contracts\InventoryRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StockExpiryController extends Controller
{
    /**
     * InventoryRepositoryInterface instance.
     *
     * @var InventoryRepositoryInterface
     */
    protected InventoryRepositoryInterface $inventoryRepo;

    /**
     * StockExpiryController constructor.
     *
     * @param InventoryRepositoryInterface $inventoryRepo
     */
    public function __construct(InventoryRepositoryInterface $inventoryRepo)
    {
        $this->inventoryRepo = $inventoryRepo;
    }

    /**
     * Display Expiry Manager timeline.
     */
    public function index(Request $request): View
    {
        $today = Carbon::today();
        $counts = [
            'expired' => Inventory::where('expiry_date', '<', $today)->count(),
            'days_7' => Inventory::whereBetween('expiry_date', [$today, $today->copy()->addDays(7)])->count(),
            'days_30' => Inventory::whereBetween('expiry_date', [$today, $today->copy()->addDays(30)])->count(),
            'days_90' => Inventory::whereBetween('expiry_date', [$today, $today->copy()->addDays(90)])->count(),
        ];

        $filter = $request->input('range', '30');
        $expiringLots = $this->inventoryRepo->getExpiringLots($filter, 15);

        return view('stock.expiry', compact('expiringLots', 'counts', 'filter'));
    }

    /**
     * Process Expiry Management Action (Dispose, Return, Discount, Transfer).
     */
    public function processAction(Request $request): RedirectResponse
    {
        $request->validate([
            'inventory_id' => ['required', 'exists:inventories,id'],
            'action_type' => ['required', 'string', 'in:dispose,return,discount,transfer'],
            'discount_percentage' => ['nullable', 'numeric', 'min:1', 'max:99'],
            'target_location' => ['nullable', 'string', 'max:255'],
        ]);

        $inventory = Inventory::with('product')->findOrFail($request->input('inventory_id'));
        $action = $request->input('action_type');
        $product = $inventory->product;

        DB::transaction(function () use ($inventory, $product, $action, $request) {
            switch ($action) {
                case 'dispose':
                    $qty = $inventory->quantity;
                    if ($qty > 0) {
                        StockAdjustment::create([
                            'reference_no' => 'EXP-DSP-' . strtoupper(Str::random(6)),
                            'product_id' => $product->id,
                            'type' => 'expired',
                            'quantity' => -$qty,
                            'unit_cost' => $inventory->unit_cost,
                            'total_amount' => $qty * $inventory->unit_cost,
                            'reason' => 'Disposed Expired Inventory Lot #' . $inventory->batch_number,
                            'status' => 'approved',
                            'created_by' => auth()->id(),
                            'approved_by' => auth()->id(),
                        ]);

                        $inventory->update(['quantity' => 0, 'status' => 'disposed']);
                        $newStock = max(0, $product->physical_stock - $qty);
                        $product->update([
                            'physical_stock' => $newStock,
                            'available_stock' => max(0, $newStock - $product->reserved_stock),
                        ]);
                    }
                    break;

                case 'return':
                    $inventory->update(['status' => 'returned']);
                    break;

                case 'discount':
                    $pct = (float) ($request->input('discount_percentage') ?? 20);
                    $discountedPrice = round($product->selling_price * (1 - ($pct / 100)), 2);
                    $inventory->update(['selling_price' => $discountedPrice]);
                    $product->update(['selling_price' => $discountedPrice]);
                    break;

                case 'transfer':
                    $loc = $request->input('target_location') ?? 'Quarantine Rack Q-01';
                    $inventory->update(['storage_condition' => $loc]);
                    break;
            }
        });

        return back()->with('success', 'Expiry action [' . ucfirst($action) . '] executed successfully for Lot #' . $inventory->batch_number);
    }
}
