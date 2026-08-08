<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Warehouse;
use App\Services\Warehouse\WarehouseExceptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WarehouseExceptionController extends Controller
{
    public function __construct(protected WarehouseExceptionService $exceptionService) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'status', 'exception_type']);
        $exceptions = $this->exceptionService->getExceptions($filters, 15);
        $products = Product::where('status', 'active')->get();
        $warehouses = Warehouse::where('status', 'active')->get();

        return view('organize.exceptions', compact('exceptions', 'products', 'warehouses', 'filters'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'exception_type' => ['required', 'string'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'affected_quantity' => ['required', 'integer', 'min:1'],
            'description' => ['required', 'string'],
            'action_taken' => ['required', 'string'],
        ]);

        $exc = $this->exceptionService->reportException($validated);

        return redirect()->route('organize.exceptions.index')
                         ->with('success', "Warehouse exception #{$exc->exception_number} reported! Action '{$exc->action_taken}' initiated.");
    }
}
