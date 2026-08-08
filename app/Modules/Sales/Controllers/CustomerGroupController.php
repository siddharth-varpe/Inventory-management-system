<?php

declare(strict_types=1);

namespace App\Modules\Sales\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CustomerGroup;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CustomerGroupController extends Controller
{
    public function index(): View
    {
        $groups = CustomerGroup::withCount('customers')->latest()->get();
        return view('sales.groups', compact('groups'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:customer_groups,code',
            'description' => 'nullable|string',
        ]);

        CustomerGroup::create($validated);

        return redirect()->route('sales.groups.index')->with('success', 'Customer Group registered successfully.');
    }

    public function destroy(CustomerGroup $group): RedirectResponse
    {
        $group->delete();
        return redirect()->route('sales.groups.index')->with('success', 'Customer Group removed successfully.');
    }
}
