<?php

declare(strict_types=1);

namespace App\Modules\Sales\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Territory;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TerritoryController extends Controller
{
    public function index(): View
    {
        $territories = Territory::withCount('customers')->latest()->get();
        return view('sales.territories', compact('territories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:territories,code',
            'region' => 'required|string|max:100',
            'sales_zone' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'pin_code_mapping' => 'nullable|string',
        ]);

        Territory::create($validated);

        return redirect()->route('sales.territories.index')->with('success', 'Territory registered successfully.');
    }

    public function destroy(Territory $territory): RedirectResponse
    {
        $territory->delete();
        return redirect()->route('sales.territories.index')->with('success', 'Territory removed successfully.');
    }
}
