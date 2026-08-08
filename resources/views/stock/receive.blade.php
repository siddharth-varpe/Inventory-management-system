@extends('layouts.app')

@section('title', 'Receive Supplier Stock - StockManager ERP')

@section('header', 'Receive Supplier Stock (Goods Receipt)')
@section('subheader', 'Log incoming goods from suppliers, assign batch & lot numbers, specify storage conditions, update QC clearance status & physical stock.')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('stock.dashboard') }}" class="text-decoration-none">Manage Stock</a></li>
    <li class="breadcrumb-item active" aria-current="page">Receive Stock</li>
@endsection

@section('content')
@php
    $productOptions = [];
    foreach($products as $p) {
        $productOptions[] = [
            'value' => $p->id,
            'label' => $p->name . ' (SKU: ' . $p->sku . ' | Current Stock: ' . $p->physical_stock . ')',
            'cost' => $p->cost_price,
            'stock' => $p->physical_stock,
            'price' => $p->selling_price,
        ];
    }

    $storageConditionOptions = [
        ['value' => 'Ambient Room Temperature', 'label' => 'Ambient Room Temperature'],
        ['value' => 'Air Conditioned Storage', 'label' => 'Air Conditioned Storage'],
        ['value' => 'Refrigerated (2°C – 8°C)', 'label' => 'Refrigerated (2°C – 8°C)'],
        ['value' => 'Frozen (-18°C or below)', 'label' => 'Frozen (-18°C or below)'],
        ['value' => 'Dry Storage', 'label' => 'Dry Storage'],
        ['value' => 'Humidity Controlled', 'label' => 'Humidity Controlled'],
        ['value' => 'Hazardous Material Storage', 'label' => 'Hazardous Material Storage'],
        ['value' => 'Secure Storage', 'label' => 'Secure Storage'],
        ['value' => 'Quarantine Area', 'label' => 'Quarantine Area'],
    ];

    $qcStatusOptions = [
        ['value' => 'Pending Inspection', 'label' => 'Pending Inspection'],
        ['value' => 'Under Inspection', 'label' => 'Under Inspection'],
        ['value' => 'Passed & Cleared', 'label' => 'Passed & Cleared'],
        ['value' => 'Conditionally Approved', 'label' => 'Conditionally Approved'],
        ['value' => 'Rejected', 'label' => 'Rejected'],
        ['value' => 'Quarantined', 'label' => 'Quarantined'],
    ];
@endphp

<div class="row g-4">
    <!-- Left Column: Sidebar -->
    <div class="col-12 col-lg-3">
        <x-stock-sidebar />
    </div>

    <!-- Right Column: Dedicated Receiving Form -->
    <div class="col-12 col-lg-9">
        <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body">
            <h5 class="fw-bold text-body mb-3">Goods Receipt Entry</h5>
            <form action="{{ route('stock.receive.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Supplier Name</label>
                        <input type="text" name="supplier_name" class="form-control rounded-3" value="{{ old('supplier_name') }}" placeholder="e.g. Apex Industrial Global">
                    </div>

                    <!-- Required Storage Condition Dropdown -->
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Storage Condition <span class="text-danger">*</span></label>
                        <x-searchable-select 
                            name="storage_condition" 
                            id="storage_condition" 
                            :options="$storageConditionOptions" 
                            :selected="old('storage_condition', 'Ambient Room Temperature')" 
                            placeholder="Select Storage Condition..." 
                            required />
                        @error('storage_condition') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <!-- Required QC Inspection Status Dropdown -->
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">QC Inspection Status <span class="text-danger">*</span></label>
                        <x-searchable-select 
                            name="qc_status" 
                            id="qc_status" 
                            :options="$qcStatusOptions" 
                            :selected="old('qc_status', 'Pending Inspection')" 
                            placeholder="Select QC Status..." 
                            required />
                        @error('qc_status') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Select Product <span class="text-danger">*</span></label>
                        <x-searchable-select 
                            name="product_id" 
                            id="product_id" 
                            :options="$productOptions" 
                            :selected="old('product_id')" 
                            placeholder="Choose active product..." 
                            required />
                        @error('product_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold">Quantity Received <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" id="quantity" class="form-control rounded-3 @error('quantity') is-invalid @enderror" value="{{ old('quantity', 1) }}" min="1" required>
                        @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold">Unit Cost (₹) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="unit_cost" id="unit_cost" class="form-control rounded-3 @error('unit_cost') is-invalid @enderror" value="{{ old('unit_cost', '0.00') }}" required>
                        @error('unit_cost') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold">Updated Selling Price (₹)</label>
                        <input type="number" step="0.01" name="selling_price" id="selling_price" class="form-control rounded-3" value="{{ old('selling_price', '0.00') }}">
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold">Batch / Lot Number</label>
                        <input type="text" name="batch_number" class="form-control rounded-3" value="{{ old('batch_number') }}" placeholder="e.g. BATCH-2026-001">
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold">Manufacturing Date</label>
                        <input type="date" name="mfg_date" class="form-control rounded-3" value="{{ old('mfg_date') }}">
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold">Expiry Date</label>
                        <input type="date" name="expiry_date" class="form-control rounded-3 @error('expiry_date') is-invalid @enderror" value="{{ old('expiry_date') }}">
                        @error('expiry_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Live Calculation Card -->
                    <div class="col-12">
                        <div class="p-3 rounded-4 bg-body-tertiary border">
                            <div class="row align-items-center text-center">
                                <div class="col-4">
                                    <div class="text-muted small">Total Cost</div>
                                    <div class="fs-5 fw-bold text-primary" id="live_total_cost">₹0.00</div>
                                </div>
                                <div class="col-4">
                                    <div class="text-muted small">Current WAC</div>
                                    <div class="fs-5 fw-bold text-body" id="live_current_cost">₹0.00</div>
                                </div>
                                <div class="col-4">
                                    <div class="text-muted small">New Projected WAC</div>
                                    <div class="fs-5 fw-bold text-success" id="live_projected_wac">₹0.00</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Notes / Inspection Comments</label>
                        <textarea name="notes" class="form-control rounded-3" rows="2" placeholder="Quality checks passed, delivery challan no, etc.">{{ old('notes') }}</textarea>
                    </div>

                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary rounded-3 px-4 py-2 fw-bold">Post Goods Receipt</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const productSelect = document.getElementById('product_id');
    const qtyInput = document.getElementById('quantity');
    const costInput = document.getElementById('unit_cost');
    const priceInput = document.getElementById('selling_price');

    const liveTotalCost = document.getElementById('live_total_cost');
    const liveCurrentCost = document.getElementById('live_current_cost');
    const liveProjectedWac = document.getElementById('live_projected_wac');

    function updateCalculations() {
        if (!productSelect) return;
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        if (!selectedOption || !selectedOption.value) {
            liveTotalCost.textContent = '₹0.00';
            liveCurrentCost.textContent = '₹0.00';
            liveProjectedWac.textContent = '₹0.00';
            return;
        }

        const currentStock = parseFloat(selectedOption.dataset.stock || 0);
        const currentCost = parseFloat(selectedOption.dataset.cost || 0);
        const currentPrice = parseFloat(selectedOption.dataset.price || 0);

        if (!costInput.value || parseFloat(costInput.value) === 0) {
            costInput.value = currentCost.toFixed(2);
        }
        if (!priceInput.value || parseFloat(priceInput.value) === 0) {
            priceInput.value = currentPrice.toFixed(2);
        }

        const qty = parseFloat(qtyInput.value || 0);
        const unitCost = parseFloat(costInput.value || 0);
        const totalCost = qty * unitCost;
        const newStock = currentStock + qty;

        const newWac = newStock > 0 ? ((currentStock * currentCost) + totalCost) / newStock : unitCost;

        liveTotalCost.textContent = '₹' + totalCost.toFixed(2);
        liveCurrentCost.textContent = '₹' + currentCost.toFixed(2);
        liveProjectedWac.textContent = '₹' + newWac.toFixed(2);
    }

    if (productSelect) productSelect.addEventListener('change', updateCalculations);
    if (qtyInput) qtyInput.addEventListener('input', updateCalculations);
    if (costInput) costInput.addEventListener('input', updateCalculations);

    updateCalculations();
});
</script>
@endsection
