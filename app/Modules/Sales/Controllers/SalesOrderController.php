<?php

declare(strict_types=1);

namespace App\Modules\Sales\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use App\Models\Quotation;
use App\Models\PickingTask;
use App\Models\TransportRequest;
use App\Domain\Sales\SalesOrderService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class SalesOrderController extends Controller
{
    public function __construct(
        protected SalesOrderService $salesOrderService
    ) {}

    /**
     * Sales Orders Queue
     */
    public function index(Request $request): View
    {
        $query = SalesOrder::with(['customer', 'salesperson', 'warehouse']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($q) use ($search) {
                      $q->where('company_name', 'like', "%{$search}%");
                  });
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        return view('sales.orders.index', compact('orders'));
    }

    /**
     * Convert Quotation into Sales Order
     */
    public function createFromQuotation(Request $request, Quotation $quotation)
    {
        if ($quotation->status === 'converted' || $quotation->sales_order_id !== null) {
            $linkedOrder = $quotation->salesOrder ? " (#{$quotation->salesOrder->order_number})" : '';
            $errorMsg = "Quotation #{$quotation->quotation_number} has already been converted to a Sales Order{$linkedOrder}.";

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'type' => 'business_validation',
                    'message' => $errorMsg
                ], 422);
            }
            return redirect()->back()->with('error', $errorMsg);
        }

        if (!in_array($quotation->status, ['approved', 'customer_accepted'])) {
            $errorMsg = "Only approved or customer accepted quotations can be converted into Sales Orders. Current status: " . strtoupper($quotation->status);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'type' => 'business_validation',
                    'message' => $errorMsg
                ], 422);
            }
            return redirect()->back()->with('error', $errorMsg);
        }

        try {
            $order = $this->salesOrderService->createFromQuotation($quotation, auth()->id() ?? 1);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Quotation {$quotation->quotation_number} converted into Sales Order {$order->order_number}!",
                    'order' => $order,
                ]);
            }

            return redirect()->route('sales.orders.show', $order->id)
                ->with('success', "Quotation {$quotation->quotation_number} converted into Sales Order {$order->order_number}! Status: " . strtoupper($order->status));

        } catch (\InvalidArgumentException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'type' => 'business_validation',
                    'message' => $e->getMessage()
                ], 422);
            }

            return redirect()->back()->with('error', $e->getMessage());

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("SalesOrderController createFromQuotation exception for Quotation #{$quotation->quotation_number}: " . $e->getMessage(), [
                'exception' => $e
            ]);

            $errorMessage = "Unable to create the Sales Order right now. Please check item stock availability and try again.";

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'type' => 'system_error',
                    'message' => $errorMessage
                ], 500);
            }

            return redirect()->back()->with('error', $errorMessage);
        }
    }

    /**
     * Sales Order 360° Profile View
     */
    public function show(SalesOrder $order): View
    {
        $order->load(['customer', 'salesperson', 'warehouse', 'approvedBy', 'items.product', 'reservations.product', 'backorders.product']);

        $pickingTask = PickingTask::with(['items.product', 'items.sourceBin', 'assignedUser', 'warehouse'])
            ->where('order_reference', $order->order_number)
            ->first();

        $transportRequest = TransportRequest::with(['assignedDriver'])
            ->where('sales_order_id', $order->id)
            ->first();

        return view('sales.orders.show', compact('order', 'pickingTask', 'transportRequest'));
    }

    /**
     * Real-time Live Status Synchronization Endpoint for Sales Orders
     */
    public function liveStatus(SalesOrder $order): JsonResponse
    {
        $pickingTask = PickingTask::with(['items.product', 'items.sourceBin', 'assignedUser', 'warehouse'])
            ->where('order_reference', $order->order_number)
            ->first();

        $transportRequest = TransportRequest::with(['assignedDriver'])
            ->where('sales_order_id', $order->id)
            ->first();

        $warehouseData = null;
        if ($pickingTask) {
            $itemsData = $pickingTask->items->map(function ($item) {
                $isVerified = (bool)$item->is_verified || ((int)$item->picked_quantity > 0 && (int)$item->picked_quantity >= (int)$item->requested_quantity);
                return [
                    'id' => $item->id,
                    'sku' => $item->product->sku ?? 'N/A',
                    'product_name' => $item->product->name ?? 'Item',
                    'requested_quantity' => (int)$item->requested_quantity,
                    'picked_quantity' => (int)$item->picked_quantity,
                    'is_verified' => $isVerified,
                ];
            });

            $warehouseData = [
                'task_number' => $pickingTask->task_number,
                'status' => $pickingTask->status,
                'status_label' => ucfirst(str_replace('_', ' ', $pickingTask->status)),
                'priority' => ucfirst($pickingTask->priority),
                'operator_name' => $pickingTask->assignedUser->name ?? 'Unassigned Operator',
                'warehouse_name' => $pickingTask->warehouse->name ?? 'Main Distribution Center',
                'total_items' => $pickingTask->total_items_count,
                'verified_items' => $pickingTask->verified_items_count,
                'completion_percentage' => $pickingTask->completion_percentage,
                'progress_color' => $pickingTask->progress_color_class,
                'updated_at' => $pickingTask->updated_at ? $pickingTask->updated_at->format('d M Y, h:i A') : 'N/A',
                'items' => $itemsData,
            ];
        }

        $transportData = null;
        if ($transportRequest) {
            $transportData = [
                'request_number' => $transportRequest->request_number,
                'status' => $transportRequest->status,
                'status_label' => ucfirst(str_replace('_', ' ', $transportRequest->status)),
                'carrier' => $transportRequest->carrier ?? 'Internal Logistics Fleet',
                'vehicle_number' => $transportRequest->vehicle_number ?? 'Waiting for Transport Dept',
                'driver_name' => $transportRequest->driver_name ?? 'Unassigned Driver',
                'tracking_number' => $transportRequest->tracking_number ?? 'PENDING-DISPATCH',
                'completion_percentage' => $transportRequest->completion_percentage,
                'progress_color' => $transportRequest->progress_color_class,
                'dispatched_at' => $transportRequest->dispatched_at ? $transportRequest->dispatched_at->format('d M Y, h:i A') : 'Awaiting Dispatch',
                'delivered_at' => $transportRequest->delivered_at ? $transportRequest->delivered_at->format('d M Y, h:i A') : 'In Transit',
            ];
        }

        return response()->json([
            'success' => true,
            'order_number' => $order->order_number,
            'order_status' => $order->status,
            'warehouse' => $warehouseData,
            'transport' => $transportData,
        ]);
    }

    /**
     * Approve Order & Reserve Inventory
     */
    public function approve(SalesOrder $order): RedirectResponse
    {
        $this->salesOrderService->approveOrder($order, auth()->id() ?? 1);

        return redirect()->route('sales.orders.show', $order->id)
            ->with('success', "Sales Order {$order->order_number} approved and inventory reserved successfully.");
    }

    /**
     * Cancel Order & Release Inventory Reservation
     */
    public function cancel(Request $request, SalesOrder $order): RedirectResponse
    {
        $request->validate(['reason' => 'required|string|max:255']);

        $this->salesOrderService->cancelOrder($order, auth()->id() ?? 1, $request->input('reason'));

        return redirect()->route('sales.orders.show', $order->id)
            ->with('success', "Sales Order {$order->order_number} cancelled and inventory reservation released.");
    }
}
