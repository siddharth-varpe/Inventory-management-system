@extends('layouts.app')

@section('title', 'Manage Stock Settings - StockManager ERP')

@section('header', 'Manage Stock Portal Settings')
@section('subheader', 'Configure inventory defaults, SKU & Barcode generators, approval thresholds, and alert parameters.')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('stock.dashboard') }}" class="text-decoration-none">Manage Stock</a></li>
    <li class="breadcrumb-item active" aria-current="page">Settings</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Left Column: Sidebar -->
    <div class="col-12 col-lg-3">
        <x-stock-sidebar />
    </div>

    <!-- Right Column: Settings Form -->
    <div class="col-12 col-lg-9">
        <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body">
            <h5 class="fw-bold text-body mb-4">Inventory System Configuration</h5>
            
            <form action="{{ route('stock.settings.update') }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row g-4 mb-4">
                    <!-- Section 1: Inventory Defaults -->
                    <div class="col-12 col-md-6">
                        <div class="p-3 rounded-4 bg-body-tertiary border h-100">
                            <h6 class="fw-bold text-body mb-3">1. Inventory Defaults</h6>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Default Reorder Level</label>
                                <input type="number" name="default_reorder_level" class="form-control rounded-3" value="{{ $settings['default_reorder_level'] ?? 10 }}" min="1">
                            </div>
                            <div>
                                <label class="form-label fw-semibold">Default Warehouse Location</label>
                                <input type="text" name="default_warehouse" class="form-control rounded-3" value="{{ $settings['default_warehouse'] ?? 'Main Warehouse' }}">
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: SKU Generator -->
                    <div class="col-12 col-md-6">
                        <div class="p-3 rounded-4 bg-body-tertiary border h-100">
                            <h6 class="fw-bold text-body mb-3">2. SKU Generator Settings</h6>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">SKU Prefix</label>
                                <input type="text" name="sku_prefix" class="form-control rounded-3" value="{{ $settings['sku_prefix'] ?? 'SKU-' }}">
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="sku_auto_generate" value="1" id="sku_auto" {{ ($settings['sku_auto_generate'] ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="sku_auto">Auto-generate SKU on Product Creation</label>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Barcode Settings -->
                    <div class="col-12 col-md-6">
                        <div class="p-3 rounded-4 bg-body-tertiary border h-100">
                            <h6 class="fw-bold text-body mb-3">3. Barcode & QR Settings</h6>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Barcode Number Prefix</label>
                                <input type="text" name="barcode_prefix" class="form-control rounded-3" value="{{ $settings['barcode_prefix'] ?? '890' }}">
                            </div>
                            <div>
                                <label class="form-label fw-semibold">Barcode Standard Format</label>
                                <select name="barcode_type" class="form-select rounded-3">
                                    <option value="CODE128" {{ ($settings['barcode_type'] ?? 'CODE128') == 'CODE128' ? 'selected' : '' }}>Code 128 (Alphanumeric)</option>
                                    <option value="EAN13" {{ ($settings['barcode_type'] ?? '') == 'EAN13' ? 'selected' : '' }}>EAN-13 (Numeric 13 Digits)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Section 4: Approval Limits & Thresholds -->
                    <div class="col-12 col-md-6">
                        <div class="p-3 rounded-4 bg-body-tertiary border h-100">
                            <h6 class="fw-bold text-body mb-3">4. Approval Thresholds</h6>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Adjustment Approval Limit (₹)</label>
                                <input type="number" step="0.01" name="approval_threshold" class="form-control rounded-3" value="{{ $settings['approval_threshold'] ?? '50000' }}">
                                <div class="form-text small">Adjustments exceeding this total loss value require manager approval.</div>
                            </div>
                            <div>
                                <label class="form-label fw-semibold">Default Expiry Alert Horizon (Days)</label>
                                <input type="number" name="expiry_alert_days" class="form-control rounded-3" value="{{ $settings['expiry_alert_days'] ?? 30 }}" min="1">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary rounded-3 px-4 py-2 fw-bold">Save Portal Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
