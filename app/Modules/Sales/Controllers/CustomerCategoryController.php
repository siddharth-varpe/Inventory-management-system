<?php

declare(strict_types=1);

namespace App\Modules\Sales\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CustomerCategory;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CustomerCategoryController extends Controller
{
    public function index(): View
    {
        $categories = CustomerCategory::withCount('customers')->latest()->get();
        return view('sales.categories', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:customer_categories,code',
            'description' => 'nullable|string',
        ]);

        CustomerCategory::create($validated);

        return redirect()->route('sales.categories.index')->with('success', 'Customer Category registered successfully.');
    }

    public function destroy(CustomerCategory $category): RedirectResponse
    {
        $category->delete();
        return redirect()->route('sales.categories.index')->with('success', 'Customer Category removed successfully.');
    }
}
