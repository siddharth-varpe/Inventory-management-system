<?php

declare(strict_types=1);

namespace App\Modules\Sales\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\CustomerCategory;
use App\Models\Territory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $query = Customer::with(['group', 'category', 'territory', 'salesperson']);

        // Instant Global Search
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('customer_code', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('display_name', 'like', "%{$search}%")
                  ->orWhere('gst_number', 'like', "%{$search}%")
                  ->orWhere('pan_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($request->filled('customer_type')) {
            $query->where('customer_type', $request->input('customer_type'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('customer_group_id')) {
            $query->where('customer_group_id', $request->input('customer_group_id'));
        }
        if ($request->filled('customer_category_id')) {
            $query->where('customer_category_id', $request->input('customer_category_id'));
        }
        if ($request->filled('territory_id')) {
            $query->where('territory_id', $request->input('territory_id'));
        }

        $customers = $query->latest()->paginate(15)->withQueryString();

        $groups = CustomerGroup::all();
        $categories = CustomerCategory::all();
        $territories = Territory::all();
        $salespersons = User::all();

        return view('sales.customers.index', compact('customers', 'groups', 'categories', 'territories', 'salespersons'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'customer_type' => 'required|string|in:retail,dealer,distributor,corporate,government,oem,institution',
            'display_name' => 'nullable|string|max:255',
            'gst_number' => 'nullable|string|max:15|unique:customers,gst_number',
            'pan_number' => 'nullable|string|max:10|unique:customers,pan_number',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:255',
            'customer_group_id' => 'nullable|exists:customer_groups,id',
            'customer_category_id' => 'nullable|exists:customer_categories,id',
            'territory_id' => 'nullable|exists:territories,id',
            'salesperson_id' => 'nullable|exists:users,id',
            'payment_term' => 'nullable|string|max:50',
            'preferred_communication_channel' => 'nullable|string|in:email,whatsapp',
            'status' => 'required|string|in:active,inactive,blocked,blacklisted',
            'internal_notes' => 'nullable|string',
        ]);

        // Generate Customer Code (e.g. CUST-2026-001)
        $nextId = Customer::max('id') + 1;
        $validated['customer_code'] = 'CUST-' . date('Y') . '-' . str_pad((string)$nextId, 4, '0', STR_PAD_LEFT);
        $validated['created_by'] = auth()->id();

        $customer = Customer::create($validated);

        return redirect()->route('sales.customers.show', $customer->id)
            ->with('success', "Customer {$customer->company_name} ({$customer->customer_code}) created successfully.");
    }

    public function show(Customer $customer): View
    {
        $customer->load(['group', 'category', 'territory', 'salesperson', 'createdBy', 'addresses', 'contacts', 'notes.author', 'documents.uploader']);

        return view('sales.customers.show', compact('customer'));
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'customer_type' => 'required|string|in:retail,dealer,distributor,corporate,government,oem,institution',
            'display_name' => 'nullable|string|max:255',
            'gst_number' => 'nullable|string|max:15|unique:customers,gst_number,' . $customer->id,
            'pan_number' => 'nullable|string|max:10|unique:customers,pan_number,' . $customer->id,
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:255',
            'customer_group_id' => 'nullable|exists:customer_groups,id',
            'customer_category_id' => 'nullable|exists:customer_categories,id',
            'territory_id' => 'nullable|exists:territories,id',
            'salesperson_id' => 'nullable|exists:users,id',
            'payment_term' => 'nullable|string|max:50',
            'preferred_communication_channel' => 'nullable|string|in:email,whatsapp',
            'status' => 'required|string|in:active,inactive,blocked,blacklisted',
            'internal_notes' => 'nullable|string',
        ]);

        $customer->update($validated);

        return redirect()->route('sales.customers.show', $customer->id)
            ->with('success', "Customer {$customer->company_name} updated successfully.");
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $name = $customer->company_name;
        $customer->delete();

        return redirect()->route('sales.customers.index')
            ->with('success', "Customer {$name} removed successfully.");
    }

    public function addAddress(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|string|in:billing,shipping,branch,warehouse_delivery',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'is_primary' => 'boolean',
        ]);

        $customer->addresses()->create($validated);

        return redirect()->route('sales.customers.show', $customer->id)
            ->with('success', 'Address added successfully.');
    }

    public function addContact(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'designation' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'type' => 'required|string|in:primary,accounts,purchase,technical,management',
            'preferred_contact_method' => 'required|string|in:email,phone,whatsapp,post',
        ]);

        $customer->contacts()->create($validated);

        return redirect()->route('sales.customers.show', $customer->id)
            ->with('success', 'Contact added successfully.');
    }

    public function addNote(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|string|in:internal,sales,support,management',
            'note' => 'required|string',
        ]);

        $validated['author_id'] = auth()->id();

        $customer->notes()->create($validated);

        return redirect()->route('sales.customers.show', $customer->id)
            ->with('success', 'Note recorded successfully.');
    }

    public function uploadDocument(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'document_type' => 'required|string|in:gst_cert,pan_card,trade_license,agreement,contract',
            'title' => 'required|string|max:255',
            'file_path' => 'required|string|max:500',
        ]);

        $validated['uploaded_by'] = auth()->id();

        $customer->documents()->create($validated);

        return redirect()->route('sales.customers.show', $customer->id)
            ->with('success', 'Document attached successfully.');
    }
}
