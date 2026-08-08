@extends('layouts.app')

@section('title', 'Admin Center & Enterprise Control Tower - StockManager ERP')

@section('header', 'Admin Center')
@section('subheader', 'Centralized enterprise administration, master data configuration, organization hierarchy, system logs, and control tower settings.')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Admin Center</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Quick Master Configuration Navigation Cards -->
    <div class="col-12">
        <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body mb-4">
            <h6 class="fw-bold text-body mb-3">Enterprise Master Data & Organization Management</h6>
            <div class="row g-3">
                <div class="col-md-3">
                    <a href="{{ route('categories.index') }}" class="btn btn-outline-primary w-100 rounded-3 text-start p-3">
                        <div class="fw-bold mb-1">📁 Product Categories</div>
                        <div class="text-muted small">Manage taxonomy & tree</div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('brands.index') }}" class="btn btn-outline-primary w-100 rounded-3 text-start p-3">
                        <div class="fw-bold mb-1">🏷️ Brands & Make</div>
                        <div class="text-muted small">Manufacturer catalog</div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('units.index') }}" class="btn btn-outline-primary w-100 rounded-3 text-start p-3">
                        <div class="fw-bold mb-1">📏 Units of Measure</div>
                        <div class="text-muted small">UOM definitions</div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('taxes.index') }}" class="btn btn-outline-primary w-100 rounded-3 text-start p-3">
                        <div class="fw-bold mb-1">💸 GST & Tax Slabs</div>
                        <div class="text-muted small">Tax rates & rules</div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('attributes.index') }}" class="btn btn-outline-secondary w-100 rounded-3 text-start p-3">
                        <div class="fw-bold mb-1">⚙️ Product Attributes</div>
                        <div class="text-muted small">Custom attributes</div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('branches.index') }}" class="btn btn-outline-secondary w-100 rounded-3 text-start p-3">
                        <div class="fw-bold mb-1">🏢 Branches & Depots</div>
                        <div class="text-muted small">Facility locations</div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('departments.index') }}" class="btn btn-outline-secondary w-100 rounded-3 text-start p-3">
                        <div class="fw-bold mb-1">👥 Departments</div>
                        <div class="text-muted small">Organizational units</div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('audit-logs.index') }}" class="btn btn-outline-dark w-100 rounded-3 text-start p-3">
                        <div class="fw-bold mb-1">📜 Audit & Activity Logs</div>
                        <div class="text-muted small">System compliance</div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- ERP Settings Tabs -->
    <div class="col-12">
        <div class="card p-0 overflow-hidden rounded-4 shadow-sm border-translucent">
            <div class="card-header bg-body border-bottom border-translucent p-3">
                <ul class="nav nav-tabs card-header-tabs" id="settingsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-semibold" id="prefixes-tab" data-bs-toggle="tab" data-bs-target="#prefixes" type="button" role="tab">Document Prefixes</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold" id="system-tab" data-bs-toggle="tab" data-bs-target="#system" type="button" role="tab">System & Formatting</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold" id="notifications-tab" data-bs-toggle="tab" data-bs-target="#notifications" type="button" role="tab">Notification Preferences</button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-4">
                <div class="tab-content" id="settingsTabsContent">

                    <!-- Tab 1: Document Prefixes -->
                    <div class="tab-pane fade show active" id="prefixes" role="tabpanel">
                        <form action="{{ route('settings.update') }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="group" value="prefixes">

                            <h5 class="fw-bold text-body mb-3">Enterprise Document Sequence Prefixes</h5>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Invoice Sequence Prefix</label>
                                    <input type="text" name="invoice_prefix" class="form-control" value="{{ setting('invoice_prefix', 'INV-') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Purchase Order Prefix</label>
                                    <input type="text" name="purchase_prefix" class="form-control" value="{{ setting('purchase_prefix', 'PO-') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Sales Order Prefix</label>
                                    <input type="text" name="sales_prefix" class="form-control" value="{{ setting('sales_prefix', 'SO-') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Quotation Sequence Prefix</label>
                                    <input type="text" name="quote_prefix" class="form-control" value="{{ setting('quote_prefix', 'QT-') }}">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm fw-semibold">Save Prefixes</button>
                        </form>
                    </div>

                    <!-- Tab 2: System & Formatting -->
                    <div class="tab-pane fade" id="system" role="tabpanel">
                        <form action="{{ route('settings.update') }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="group" value="system">

                            <h5 class="fw-bold text-body mb-3">Decimal & Currency Precision</h5>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Decimal Precision</label>
                                    <select name="decimal_precision" class="form-select">
                                        <option value="2" {{ setting('decimal_precision', '2') == '2' ? 'selected' : '' }}>2 Decimals (0.00)</option>
                                        <option value="3" {{ setting('decimal_precision') == '3' ? 'selected' : '' }}>3 Decimals (0.000)</option>
                                        <option value="4" {{ setting('decimal_precision') == '4' ? 'selected' : '' }}>4 Decimals (0.0000)</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Default Tax %</label>
                                    <input type="number" step="0.01" name="default_tax" class="form-control" value="{{ setting('default_tax', '0.00') }}">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm fw-semibold">Save System Defaults</button>
                        </form>
                    </div>

                    <!-- Tab 3: Notification Preferences -->
                    <div class="tab-pane fade" id="notifications" role="tabpanel">
                        <form action="{{ route('settings.update') }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="group" value="notifications">

                            <h5 class="fw-bold text-body mb-3">Notification Channels</h5>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="notify_email" value="1" id="notify_email" {{ setting('notify_email', '1') ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold small" for="notify_email">Enable Email Notifications for Enterprise Events</label>
                            </div>
                            <div class="form-check form-switch mb-4">
                                <input class="form-check-input" type="checkbox" name="notify_browser" value="1" id="notify_browser" {{ setting('notify_browser', '1') ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold small" for="notify_browser">Enable Browser Toast Alerts</label>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm fw-semibold">Save Preferences</button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
