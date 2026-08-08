@extends('layouts.app')

@section('title', 'Stock Adjustments - StockManager ERP')

@section('header', 'Stock Adjustments & Reconciliation')
@section('subheader', 'Log physical count differences, damaged goods, or expired losses for inventory reconciliation.')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('stock.dashboard') }}" class="text-decoration-none">Manage Stock</a></li>
    <li class="breadcrumb-item active" aria-current="page">Stock Adjustments</li>
@endsection

@section('content')
@php
    $productOptions = [];
    foreach($products as $p) {
        $productOptions[] = [
            'value' => $p->id,
            'label' => $p->name . ' (SKU: ' . $p->sku . ' | Physical: ' . $p->physical_stock . ' | Cost: ₹' . number_format((float)$p->cost_price, 2) . ')',
        ];
    }
@endphp

<div class="row g-4">
    <!-- Left Column: Sidebar -->
    <div class="col-12 col-lg-3">
        <x-stock-sidebar />
    </div>

    <!-- Right Column: Dedicated Adjustments Form -->
    <div class="col-12 col-lg-9">
        <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body">
            <h5 class="fw-bold text-body mb-3">Post Stock Adjustment</h5>
            <form action="{{ route('stock.adjustments.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Select Product <span class="text-danger">*</span></label>
                        <x-searchable-select 
                            name="product_id" 
                            id="adj_product_id_select" 
                            :options="$productOptions" 
                            :selected="old('product_id')" 
                            placeholder="Choose product..." 
                            required />
                        @error('product_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Adjustment Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select rounded-3 @error('type') is-invalid @enderror" required>
                            <option value="damaged">Damaged Stock (-)</option>
                            <option value="expired">Expired Stock (-)</option>
                            <option value="lost">Lost / Stolen (-)</option>
                            <option value="audit_difference">Audit Difference (+/-)</option>
                            <option value="transfer_correction">Manual Correction (+/-)</option>
                        </select>
                        @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Quantity Delta (+ for addition, - for deduction) <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" class="form-control rounded-3 @error('quantity') is-invalid @enderror" value="{{ old('quantity', -1) }}" required>
                        <div class="form-text small">Enter negative number (e.g. -5) for inventory loss or positive for count gain.</div>
                        @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Reason Classification</label>
                        <input type="text" name="reason" class="form-control rounded-3" value="{{ old('reason') }}" placeholder="e.g. Water damage in Section B">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Internal Notes</label>
                        <textarea name="notes" class="form-control rounded-3" rows="2" placeholder="Auditor notes or inspection reference...">{{ old('notes') }}</textarea>
                    </div>

                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-warning rounded-3 px-4 py-2 fw-bold">Submit Adjustment</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
