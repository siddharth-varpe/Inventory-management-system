@extends('layouts.app')

@section('title', 'Opening Stock Entry - StockManager ERP')

@section('header', 'Opening Stock Setup')
@section('subheader', 'Register initial product stock balances during system setup or bulk import opening balances.')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('stock.dashboard') }}" class="text-decoration-none">Manage Stock</a></li>
    <li class="breadcrumb-item active" aria-current="page">Opening Stock</li>
@endsection

@section('content')
@php
    $productOptions = [];
    foreach($products as $p) {
        $productOptions[] = [
            'value' => $p->id,
            'label' => $p->name . ' (SKU: ' . $p->sku . ' | Current Stock: ' . $p->physical_stock . ')',
        ];
    }
@endphp

<div class="row g-4">
    <!-- Left Column: Sidebar -->
    <div class="col-12 col-lg-3">
        <x-stock-sidebar />
    </div>

    <!-- Right Column: Dedicated Form Area -->
    <div class="col-12 col-lg-9">
        <div class="row g-4">
            <!-- Single Product Opening Entry -->
            <div class="col-12 col-xl-7">
                <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body h-100">
                    <h5 class="fw-bold text-body mb-3">Single Product Initial Entry</h5>
                    <form action="{{ route('stock.opening-stock.store') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Select Product <span class="text-danger">*</span></label>
                                <x-searchable-select 
                                    name="product_id" 
                                    id="opening_product_id_select" 
                                    :options="$productOptions" 
                                    :selected="old('product_id')" 
                                    placeholder="Choose product..." 
                                    required />
                                @error('product_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Opening Quantity <span class="text-danger">*</span></label>
                                <input type="number" name="quantity" class="form-control rounded-3" value="{{ old('quantity', 1) }}" min="1" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Unit Cost (₹) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="unit_cost" class="form-control rounded-3" value="{{ old('unit_cost', '0.00') }}" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Batch Number</label>
                                <input type="text" name="batch_number" class="form-control rounded-3" value="{{ old('batch_number', 'OPN-LOT-01') }}">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Warehouse Location</label>
                                <input type="text" name="warehouse_location" class="form-control rounded-3" placeholder="e.g. Main Warehouse">
                            </div>

                            <div class="col-12 text-end mt-4">
                                <button type="submit" class="btn btn-primary rounded-3 px-4 py-2 fw-bold">Post Initial Stock</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Bulk Opening Stock CSV Upload -->
            <div class="col-12 col-xl-5">
                <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body h-100">
                    <h5 class="fw-bold text-body mb-3">Bulk Opening Stock CSV</h5>
                    <p class="text-muted small">Upload a CSV file containing <code>sku</code>, <code>quantity</code>, and <code>unit_cost</code> columns to initialize stock for multiple items at once.</p>
                    
                    <form action="{{ route('stock.opening-stock.bulk') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">CSV Data File</label>
                            <input type="file" name="opening_stock_file" class="form-control rounded-3" accept=".csv, .txt" required>
                        </div>
                        <button type="submit" class="btn btn-outline-primary rounded-3 w-100 py-2 fw-bold">Upload Bulk Opening CSV</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
