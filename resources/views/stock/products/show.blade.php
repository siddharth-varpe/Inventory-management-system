@extends('layouts.app')

@section('title', $product->name . ' - Product Master - StockManager ERP')

@section('header', $product->name)
@section('subheader', 'SKU: ' . $product->sku . ' | Code: ' . $product->code)

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('stock.dashboard') }}" class="text-decoration-none">Manage Stock</a></li>
    <li class="breadcrumb-item"><a href="{{ route('products.index') }}" class="text-decoration-none">Product Master</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $product->sku }}</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Left Vertical Side Panel -->
    <div class="col-12 col-lg-3">
        <x-stock-sidebar />
    </div>

    <!-- Main Workspace Area -->
    <div class="col-12 col-lg-9">
        <div class="row g-4 mb-4">
            <!-- Top Summary Card -->
            <div class="col-12">
                <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-3">
                            @if($product->image_url)
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="rounded-4 border" style="width: 70px; height: 70px; object-fit: cover;">
                            @else
                                <div class="bg-body-secondary rounded-4 p-3 border d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-box-seam text-muted" viewBox="0 0 16 16"><path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2zm3.564 1.426L5.596 5 8 5.961 14.154 3.5zm3.25 1.7-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.838L1 4.239v7.923l6.5 2.6zM7.443.184a1.5 1.5 0 0 1 1.114 0l7.129 2.852A.5.5 0 0 1 16 3.5v8.662a1.5 1.5 0 0 1-.901 1.37l-7 2.8a1.5 1.5 0 0 1-1.198 0l-7-2.8A1.5 1.5 0 0 1 0 12.162V3.5a.5.5 0 0 1 .314-.464L7.443.184z"/></svg>
                                </div>
                            @endif
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <h4 class="fw-bold text-body mb-0">{{ $product->name }}</h4>
                                    <span class="badge bg-primary-subtle text-primary rounded-pill">{{ ucfirst($product->status) }}</span>
                                </div>
                                <div class="text-muted small mt-1">
                                    <span class="me-3">Category: <strong>{{ $product->category->name ?? 'Uncategorized' }}</strong></span>
                                    <span class="me-3">Brand: <strong>{{ $product->brand->name ?? 'Generic' }}</strong></span>
                                    <span>Barcode: <code>{{ $product->barcode ?? 'N/A' }}</code></span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-primary rounded-3 px-3 fw-semibold">Edit Product</a>
                            <form action="{{ route('products.duplicate', $product) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary rounded-3 px-3 fw-semibold">Duplicate</button>
                            </form>
                            <a href="{{ route('stock.barcodes.index', ['product_id' => $product->id]) }}" class="btn btn-outline-dark rounded-3 px-3 fw-semibold">Barcode</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 9-Tab Interface -->
        <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body">
            <ul class="nav nav-tabs nav-tabs-bordered mb-4" id="productTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">1. Overview</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" id="inventory-tab" data-bs-toggle="tab" data-bs-target="#inventory" type="button" role="tab">2. Inventory</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" id="pricing-tab" data-bs-toggle="tab" data-bs-target="#pricing" type="button" role="tab">3. Pricing Tiers</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" id="images-tab" data-bs-toggle="tab" data-bs-target="#images" type="button" role="tab">4. Images</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button" role="tab">5. Documents</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" id="attributes-tab" data-bs-toggle="tab" data-bs-target="#attributes" type="button" role="tab">6. Attributes</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" id="suppliers-tab" data-bs-toggle="tab" data-bs-target="#suppliers" type="button" role="tab">7. Suppliers</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button" role="tab">8. History</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" id="audit-tab" data-bs-toggle="tab" data-bs-target="#audit" type="button" role="tab">9. Audit Logs</button>
                </li>
            </ul>

            <div class="tab-content" id="productTabsContent">
                <!-- Tab 1: Overview -->
                <div class="tab-pane fade show active" id="overview" role="tabpanel">
                    <div class="row g-4">
                        <div class="col-12 col-md-6">
                            <h6 class="fw-bold text-body mb-3">Identity & Classification</h6>
                            <table class="table table-borderless table-sm">
                                <tr><td class="text-muted w-50">Product Name:</td><td class="fw-bold text-body">{{ $product->name }}</td></tr>
                                <tr><td class="text-muted">SKU:</td><td><code>{{ $product->sku }}</code></td></tr>
                                <tr><td class="text-muted">Product Code:</td><td><code>{{ $product->code }}</code></td></tr>
                                <tr><td class="text-muted">Barcode:</td><td><code>{{ $product->barcode ?? 'N/A' }}</code></td></tr>
                                <tr><td class="text-muted">QR Code:</td><td><code>{{ $product->qr_code ?? 'N/A' }}</code></td></tr>
                                <tr><td class="text-muted">Category:</td><td class="fw-semibold">{{ $product->category->name ?? 'N/A' }}</td></tr>
                                <tr><td class="text-muted">Brand:</td><td class="fw-semibold">{{ $product->brand->name ?? 'N/A' }}</td></tr>
                                <tr><td class="text-muted">Unit:</td><td>{{ $product->unit->name ?? 'N/A' }} ({{ $product->unit->short_name ?? 'pcs' }})</td></tr>
                                <tr><td class="text-muted">Tax Slab:</td><td>{{ $product->tax->name ?? 'No Tax' }} ({{ $product->tax->rate ?? 0 }}%)</td></tr>
                            </table>
                        </div>

                        <div class="col-12 col-md-6">
                            <h6 class="fw-bold text-body mb-3">Stock Overview</h6>
                            <div class="p-3 rounded-4 bg-body-tertiary border mb-3">
                                <div class="row text-center">
                                    <div class="col-4">
                                        <div class="text-muted small">Physical Stock</div>
                                        <div class="fs-4 fw-bold text-body">{{ $product->physical_stock }}</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-muted small">Reserved</div>
                                        <div class="fs-4 fw-bold text-warning-emphasis">{{ $product->reserved_stock }}</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-muted small">Available</div>
                                        <div class="fs-4 fw-bold text-success">{{ $product->available_stock }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-muted small">
                                <strong>Description:</strong><br>
                                {{ $product->description ?? 'No detailed description provided.' }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Inventory -->
                <div class="tab-pane fade" id="inventory" role="tabpanel">
                    <h6 class="fw-bold text-body mb-3">Inventory Lots & Thresholds</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-3">
                            <div class="p-3 rounded-3 bg-body-tertiary border">
                                <div class="text-muted small">Min Stock</div>
                                <div class="fw-bold fs-5 text-body">{{ $product->min_stock }}</div>
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="p-3 rounded-3 bg-body-tertiary border">
                                <div class="text-muted small">Reorder Level</div>
                                <div class="fw-bold fs-5 text-body">{{ $product->reorder_level }}</div>
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="p-3 rounded-3 bg-body-tertiary border">
                                <div class="text-muted small">Max Stock</div>
                                <div class="fw-bold fs-5 text-body">{{ $product->max_stock }}</div>
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="p-3 rounded-3 bg-body-tertiary border">
                                <div class="text-muted small">Warehouse Rack</div>
                                <div class="fw-bold fs-5 text-body">{{ $product->warehouse_location ?? 'Main' }} / {{ $product->rack_location ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold text-body mb-2">Active Inventory Batches / Lots</h6>
                    @if($product->inventories->isEmpty())
                        <div class="text-muted small py-3">No inventory batch lots recorded yet.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr class="text-muted small">
                                        <th>Batch No</th>
                                        <th>Lot No</th>
                                        <th>Quantity</th>
                                        <th>Unit Cost</th>
                                        <th>Mfg Date</th>
                                        <th>Expiry Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($product->inventories as $inv)
                                    <tr>
                                        <td><code>{{ $inv->batch_number ?? 'DEFAULT' }}</code></td>
                                        <td><code>{{ $inv->lot_number }}</code></td>
                                        <td class="fw-bold">{{ $inv->quantity }}</td>
                                        <td>₹{{ number_format((float)$inv->unit_cost, 2) }}</td>
                                        <td>{{ $inv->mfg_date ? $inv->mfg_date->format('Y-m-d') : 'N/A' }}</td>
                                        <td>{{ $inv->expiry_date ? $inv->expiry_date->format('Y-m-d') : 'N/A' }}</td>
                                        <td><span class="badge bg-success-subtle text-success">{{ $inv->status }}</span></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <!-- Tab 3: Pricing Tiers -->
                <div class="tab-pane fade" id="pricing" role="tabpanel">
                    <h6 class="fw-bold text-body mb-3">7-Tier Pricing Matrix</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead>
                                <tr class="table-light text-muted small">
                                    <th>Tier Name</th>
                                    <th>Amount (₹)</th>
                                    <th>Description / Policy</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>Purchase Price</td><td class="fw-bold">₹{{ number_format((float)$product->purchase_price, 2) }}</td><td class="text-muted small">Supplier buying rate</td></tr>
                                <tr><td>Cost Price (WAC)</td><td class="fw-bold text-primary">₹{{ number_format((float)$product->cost_price, 2) }}</td><td class="text-muted small">Weighted Average Cost for inventory valuation</td></tr>
                                <tr><td>Selling Price (Base)</td><td class="fw-bold text-success">₹{{ number_format((float)$product->selling_price, 2) }}</td><td class="text-muted small">Default customer checkout rate (Profit Margin: {{ $product->profit_margin }}%)</td></tr>
                                <tr><td>MRP</td><td class="fw-bold">₹{{ number_format((float)$product->mrp, 2) }}</td><td class="text-muted small">Maximum Retail Price tag</td></tr>
                                <tr><td>Dealer Price</td><td class="fw-bold">₹{{ number_format((float)$product->dealer_price, 2) }}</td><td class="text-muted small">Special pricing for registered dealers</td></tr>
                                <tr><td>Wholesale Price</td><td class="fw-bold">₹{{ number_format((float)$product->wholesale_price, 2) }}</td><td class="text-muted small">Bulk order tier rate</td></tr>
                                <tr><td>Min Selling Price</td><td class="fw-bold text-danger">₹{{ number_format((float)$product->min_selling_price, 2) }}</td><td class="text-muted small">Strict bottom limit for discounts</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab 4: Images -->
                <div class="tab-pane fade" id="images" role="tabpanel">
                    <h6 class="fw-bold text-body mb-3">Product Media Gallery</h6>
                    @if($product->image_url)
                        <div class="d-flex gap-3">
                            <div class="border rounded-4 p-2 bg-body-tertiary text-center">
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="rounded-3" style="max-width: 250px; max-height: 250px; object-fit: contain;">
                                <div class="small fw-semibold mt-2">Primary Image</div>
                            </div>
                        </div>
                    @else
                        <div class="text-muted small py-3">No product images uploaded yet.</div>
                    @endif
                </div>

                <!-- Tab 5: Documents -->
                <div class="tab-pane fade" id="documents" role="tabpanel">
                    <h6 class="fw-bold text-body mb-3">Attached Product Documents</h6>
                    @if(empty($product->documents))
                        <div class="text-muted small py-3">No document attachments uploaded for this product master.</div>
                    @else
                        <ul class="list-group rounded-3 border-translucent">
                            @foreach($product->documents as $doc)
                            <li class="list-group-item d-flex align-items-center justify-content-between py-3">
                                <div class="d-flex align-items-center gap-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-file-earmark-text text-primary" viewBox="0 0 16 16"><path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5"/><path d="M9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.5zm0 1v3A1.5 1.5 0 0 0 11 4.5h3z"/></svg>
                                    <div>
                                        <div class="fw-bold text-body small">{{ $doc['name'] ?? 'Document File' }}</div>
                                        <div class="text-muted small" style="font-size: 0.75rem;">Size: {{ round(($doc['size'] ?? 0) / 1024, 1) }} KB</div>
                                    </div>
                                </div>
                                <a href="{{ asset('storage/' . ($doc['path'] ?? '')) }}" target="_blank" class="btn btn-outline-primary btn-sm rounded-3">Download</a>
                            </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <!-- Tab 6: Attributes -->
                <div class="tab-pane fade" id="attributes" role="tabpanel">
                    <h6 class="fw-bold text-body mb-3">Dynamic Attribute Specifications</h6>
                    @if($product->attributeValues->isEmpty())
                        <div class="text-muted small py-3">No dynamic attributes assigned.</div>
                    @else
                        <div class="row g-3">
                            @foreach($product->attributeValues as $val)
                            <div class="col-12 col-md-4">
                                <div class="p-3 rounded-3 bg-body-tertiary border">
                                    <div class="text-muted small">{{ $val->attribute->name ?? 'Attribute' }}</div>
                                    <div class="fw-bold text-body mt-1">{{ $val->attribute_value }}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Tab 7: Suppliers -->
                <div class="tab-pane fade" id="suppliers" role="tabpanel">
                    <h6 class="fw-bold text-body mb-3">Supplier & Order Parameters</h6>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="p-3 rounded-3 bg-body-tertiary border">
                                <div class="text-muted small">Primary Supplier</div>
                                <div class="fw-bold fs-5 text-body mt-1">{{ $product->primary_supplier ?? 'Not Specified' }}</div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="p-3 rounded-3 bg-body-tertiary border">
                                <div class="text-muted small">Minimum Order Quantity (MOQ)</div>
                                <div class="fw-bold fs-5 text-body mt-1">{{ $product->moq }} units</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 8: History -->
                <div class="tab-pane fade" id="history" role="tabpanel">
                    <h6 class="fw-bold text-body mb-3">Recent Receipts & Adjustments History</h6>
                    
                    <div class="mb-4">
                        <h6 class="fw-semibold text-muted small">Supplier Stock Receipts</h6>
                        @if($product->receipts->isEmpty())
                            <div class="text-muted small py-2">No receipts recorded for this product.</div>
                        @else
                            <table class="table table-sm table-hover align-middle">
                                <thead>
                                    <tr class="text-muted small">
                                        <th>Ref No</th>
                                        <th>Supplier</th>
                                        <th>Qty</th>
                                        <th>Unit Cost</th>
                                        <th>Total Cost</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($product->receipts as $r)
                                    <tr>
                                        <td><code>{{ $r->reference_no }}</code></td>
                                        <td>{{ $r->supplier_name }}</td>
                                        <td class="fw-bold text-success">+{{ $r->quantity }}</td>
                                        <td>₹{{ number_format((float)$r->unit_cost, 2) }}</td>
                                        <td>₹{{ number_format((float)$r->total_cost, 2) }}</td>
                                        <td>{{ $r->created_at->format('Y-m-d H:i') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>

                    <div>
                        <h6 class="fw-semibold text-muted small">Stock Adjustments</h6>
                        @if($product->adjustments->isEmpty())
                            <div class="text-muted small py-2">No stock adjustments recorded.</div>
                        @else
                            <table class="table table-sm table-hover align-middle">
                                <thead>
                                    <tr class="text-muted small">
                                        <th>Ref No</th>
                                        <th>Type</th>
                                        <th>Qty</th>
                                        <th>Reason</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($product->adjustments as $a)
                                    <tr>
                                        <td><code>{{ $a->reference_no }}</code></td>
                                        <td><span class="badge bg-secondary-subtle text-secondary">{{ $a->type }}</span></td>
                                        <td class="fw-bold {{ $a->quantity > 0 ? 'text-success' : 'text-danger' }}">{{ $a->quantity }}</td>
                                        <td>{{ $a->reason ?? 'N/A' }}</td>
                                        <td><span class="badge bg-success-subtle text-success">{{ $a->status }}</span></td>
                                        <td>{{ $a->created_at->format('Y-m-d H:i') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>

                <!-- Tab 9: Audit Logs -->
                <div class="tab-pane fade" id="audit" role="tabpanel">
                    <h6 class="fw-bold text-body mb-3">Product Audit Trail</h6>
                    @if($auditLogs->isEmpty())
                        <div class="text-muted small py-3">No system audit records found for this product.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr class="text-muted small">
                                        <th>Event</th>
                                        <th>User</th>
                                        <th>IP Address</th>
                                        <th>Timestamp</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($auditLogs as $log)
                                    <tr>
                                        <td><span class="badge bg-info-subtle text-info text-uppercase">{{ $log->event }}</span></td>
                                        <td>{{ $log->user->name ?? 'System' }}</td>
                                        <td><code>{{ $log->ip_address }}</code></td>
                                        <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
