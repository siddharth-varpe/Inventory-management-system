@extends('layouts.app')

@section('title', 'Quotation Workspace - ' . $quotation->quotation_number . ' - StockManager ERP')

@section('content')
<div class="d-flex gap-4">
    <!-- Sales Workspace Sidebar -->
    <x-sales-sidebar activeTab="quotations" />

    <!-- Main Workspace Area -->
    <div class="flex-grow-1">

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="alert alert-success border-0 rounded-3 shadow-sm mb-4">
                ✔ {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger border-0 rounded-3 shadow-sm mb-4">
                ⚠️ {{ session('error') }}
            </div>
        @endif

        <div id="cceGlobalAlert" class="alert border-0 rounded-3 shadow-sm mb-4 d-none"></div>

        <!-- 1. STICKY TOP BUSINESS ACTION PANEL (DOCUMENT-LEVEL ACTIONS ONLY) -->
        <div class="card p-3 rounded-4 shadow-sm border-translucent mb-4 bg-body sticky-top" style="top: 15px; z-index: 1020;">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <!-- Left: Document Reference & Status -->
                <div class="d-flex align-items-center gap-3">
                    <div class="p-3 bg-primary text-white rounded-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px;">
                        <span class="fs-4">📜</span>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h4 class="fw-bold text-body mb-0 font-monospace">{{ $quotation->quotation_number }}</h4>
                            <x-status-badge :status="$quotation->status" />
                            @if($quotation->sales_order_id || $quotation->salesOrder)
                                <span class="badge bg-purple-subtle text-purple border border-purple-subtle rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.75rem;">
                                    SO #{{ $quotation->salesOrder->order_number ?? 'SO-LINKED' }}
                                </span>
                            @endif
                        </div>
                        <div class="text-muted small mt-1">
                            Enterprise Order Ref: <code class="text-dark fw-bold">{{ $quotation->salesOrder->order_number ?? $quotation->order_reference ?? $quotation->quotation_number }}</code>
                        </div>
                    </div>
                </div>

                <!-- Right: Dedicated Document Actions Buttons Panel -->
                <div class="d-flex flex-wrap align-items-center gap-2">

                    <!-- Edit Action (Allowed for non-converted quotations) -->
                    @if($quotation->status !== 'converted' && !$quotation->sales_order_id)
                        <a href="{{ route('sales.quotations.edit', $quotation->id) }}" class="btn btn-outline-primary rounded-3 fw-semibold d-flex align-items-center gap-1.5">
                            <span>✏️</span> Edit Proposal
                        </a>
                    @endif

                    <!-- Actions for DRAFT status -->
                    @if($quotation->status === 'draft')
                        <form method="POST" action="{{ route('sales.quotations.approve', $quotation->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-success rounded-3 fw-bold text-white px-3 d-flex align-items-center gap-1.5 shadow-sm">
                                <span>✅</span> Approve Proposal
                            </button>
                        </form>
                    @endif

                    <!-- Actions for PENDING APPROVAL status -->
                    @if($quotation->status === 'pending_approval')
                        <form method="POST" action="{{ route('sales.quotations.approve', $quotation->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-success rounded-3 fw-bold text-white px-3 d-flex align-items-center gap-1.5 shadow-sm">
                                <span>✅</span> Approve Proposal
                            </button>
                        </form>

                        <button type="button" class="btn btn-outline-danger rounded-3 fw-semibold d-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <span>❌</span> Reject
                        </button>
                    @endif

                    <!-- Actions for APPROVED / CUSTOMER ACCEPTED status -->
                    @if(in_array($quotation->status, ['approved', 'customer_accepted']))
                        @php
                            $isCustomerActive = ($quotation->customer && $quotation->customer->status === 'active');
                            $isNotExpired = (!$quotation->validity_date || !$quotation->validity_date->isPast());
                            $canConvert = ($isCustomerActive && $isNotExpired);
                        @endphp

                        @if($canConvert)
                            <form method="POST" action="{{ route('sales.quotations.convert', $quotation->id) }}">
                                @csrf
                                <button type="submit" class="btn btn-success rounded-3 fw-bold text-white px-4 py-2 d-flex align-items-center gap-2 shadow-sm">
                                    <span>🚀</span> Convert to Sales Order &rarr;
                                </button>
                            </form>
                        @else
                            <button type="button" class="btn btn-secondary rounded-3 fw-bold px-3 d-flex align-items-center gap-2" disabled title="Quotation expired or Customer account inactive">
                                <span>🔒</span> Convert to Sales Order
                            </button>
                        @endif
                    @endif

                    <!-- Standard Document Level Actions -->
                    <a href="{{ route('sales.quotations.pdf', $quotation->id) }}" target="_blank" class="btn btn-outline-dark rounded-3 fw-semibold d-flex align-items-center gap-1.5">
                        <span>🖨️</span> PDF
                    </a>

                    <form method="POST" action="{{ route('sales.quotations.duplicate', $quotation->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary rounded-3 fw-semibold d-flex align-items-center gap-1.5">
                            <span>📋</span> Duplicate
                        </button>
                    </form>

                    <!-- Delete Action (Allowed for non-converted quotations) -->
                    @if($quotation->status !== 'converted' && !$quotation->sales_order_id)
                        <button type="button" class="btn btn-outline-danger rounded-3 fw-semibold d-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#deleteQuotationModal">
                            <span>🗑️</span> Delete
                        </button>
                    @endif

                    <a href="{{ route('sales.quotations.index') }}" class="btn btn-light border rounded-3 fw-semibold">
                        Back to Queue
                    </a>
                </div>
            </div>
        </div>

        <!-- 2. DOCUMENT TIMELINE STAGE INDICATOR -->
        <div class="card p-3 rounded-4 shadow-sm border-translucent mb-4 bg-body">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3 w-100">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success rounded-circle p-1.5">✓</span>
                        <span class="fw-bold small text-body">1. Draft Created</span>
                    </div>
                    <div class="flex-grow-1 border-top border-2 {{ in_array($quotation->status, ['pending_approval', 'approved', 'customer_accepted', 'converted']) ? 'border-success' : 'border-translucent' }}"></div>

                    <div class="d-flex align-items-center gap-2">
                        <span class="badge {{ in_array($quotation->status, ['approved', 'customer_accepted', 'converted']) ? 'bg-success' : ($quotation->status === 'pending_approval' ? 'bg-warning text-dark' : 'bg-secondary') }} rounded-circle p-1.5">
                            {{ in_array($quotation->status, ['approved', 'customer_accepted', 'converted']) ? '✓' : '2' }}
                        </span>
                        <span class="fw-bold small {{ in_array($quotation->status, ['approved', 'customer_accepted', 'converted']) ? 'text-body' : 'text-muted' }}">2. Manager Approved</span>
                    </div>
                    <div class="flex-grow-1 border-top border-2 {{ $quotation->status === 'converted' ? 'border-success' : 'border-translucent' }}"></div>

                    <div class="d-flex align-items-center gap-2">
                        <span class="badge {{ $quotation->status === 'converted' ? 'bg-purple text-white' : 'bg-secondary' }} rounded-circle p-1.5">
                            {{ $quotation->status === 'converted' ? '✓' : '3' }}
                        </span>
                        <span class="fw-bold small {{ $quotation->status === 'converted' ? 'text-purple' : 'text-muted' }}">3. Sales Order Converted</span>
                    </div>
                    <div class="flex-grow-1 border-top border-2 {{ ($pickingTask && in_array($pickingTask->status, ['picking', 'picked', 'packed', 'completed'])) ? 'border-success' : 'border-translucent' }}"></div>

                    <div class="d-flex align-items-center gap-2">
                        <span class="badge {{ ($transportRequest && in_array($transportRequest->status, ['out_for_delivery', 'delivered'])) ? 'bg-info text-white' : 'bg-secondary' }} rounded-circle p-1.5">
                            4
                        </span>
                        <span class="fw-bold small {{ ($transportRequest && in_array($transportRequest->status, ['out_for_delivery', 'delivered'])) ? 'text-info' : 'text-muted' }}">4. Warehouse & Transport</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. QUICK FINANCIAL KPI SUMMARY CARDS -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card p-3 rounded-4 shadow-sm border-translucent bg-body">
                    <div class="text-muted small fw-semibold">Subtotal</div>
                    <div class="fs-5 fw-bold text-body mt-1">₹{{ number_format((float)$quotation->subtotal, 2) }}</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card p-3 rounded-4 shadow-sm border-translucent bg-body">
                    <div class="text-muted small fw-semibold">Order Discount</div>
                    <div class="fs-5 fw-bold text-danger mt-1">-₹{{ number_format((float)$quotation->order_discount_amount, 2) }}</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card p-3 rounded-4 shadow-sm border-translucent bg-body">
                    <div class="text-muted small fw-semibold">Taxable Amount</div>
                    <div class="fs-5 fw-bold text-body mt-1">₹{{ number_format((float)$quotation->taxable_amount, 2) }}</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card p-3 rounded-4 shadow-sm border-translucent bg-body">
                    <div class="text-muted small fw-semibold">Total GST Tax</div>
                    @php
                        $totalGst = (float)$quotation->cgst_amount + (float)$quotation->sgst_amount + (float)$quotation->igst_amount;
                    @endphp
                    <div class="fs-5 fw-bold text-info mt-1">₹{{ number_format($totalGst, 2) }}</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card p-3 rounded-4 shadow-sm border-translucent bg-body border-success">
                    <div class="text-success small fw-bold">Grand Total</div>
                    <div class="fs-5 fw-black text-success mt-1">₹{{ number_format((float)$quotation->grand_total, 2) }}</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card p-3 rounded-4 shadow-sm border-translucent bg-body">
                    <div class="text-muted small fw-semibold">Total Line Items</div>
                    <div class="fs-5 fw-bold text-primary mt-1">{{ $quotation->items->count() }} SKUs</div>
                </div>
            </div>
        </div>

        <!-- 4. DOCUMENT BODY GRID -->
        <div class="row g-4 mb-4">
            <!-- Left Column: Customer Profile & Products Table -->
            <div class="col-12 col-xl-8">

                <!-- Customer Account Details Card -->
                <div class="card rounded-4 border-translucent shadow-sm bg-body mb-4">
                    <div class="card-header bg-transparent border-bottom border-translucent p-3 d-flex align-items-center justify-content-between">
                        <h6 class="fw-bold text-body mb-0">🏢 Customer Account & Contact Details</h6>
                        <span class="badge bg-secondary-subtle text-secondary font-monospace">{{ $quotation->customer->customer_code ?? 'CUST-N/A' }}</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <span class="text-muted small fw-semibold d-block">Company Name:</span>
                                <h5 class="fw-bold text-body mb-1">{{ $quotation->customer->company_name ?? 'N/A' }}</h5>
                                <div class="small text-muted">Type: <strong>{{ strtoupper($quotation->customer->customer_type ?? 'Retail') }}</strong></div>
                                <div class="small text-muted mt-1">GSTIN: <code class="text-dark fw-bold">{{ $quotation->customer->gst_number ?? 'UNREGISTERED' }}</code></div>
                            </div>
                            <div class="col-12 col-md-6">
                                <span class="text-muted small fw-semibold d-block">Contact Details:</span>
                                <div class="fw-bold text-body">{{ $quotation->customer->contact_person ?? 'N/A' }}</div>
                                <div class="small text-muted">✉️ {{ $quotation->customer->email ?? 'N/A' }}</div>
                                <div class="small text-muted">📞 {{ $quotation->customer->phone ?? 'N/A' }}</div>
                            </div>
                        </div>

                        <!-- Billing & Shipping Address Split -->
                        <div class="row g-3 mt-3 pt-3 border-top border-translucent">
                            @php
                                $billing = $quotation->customer->addresses->where('address_type', 'billing')->first();
                                $shipping = $quotation->customer->addresses->where('address_type', 'shipping')->first() ?? $billing;
                            @endphp
                            <div class="col-12 col-md-6">
                                <span class="text-muted small fw-bold d-block mb-1">Billing Address:</span>
                                <p class="small text-body mb-0">
                                    {{ $billing->address_line1 ?? 'Primary Commercial Billing Address' }}<br>
                                    {{ $billing->city ?? '' }} {{ $billing->state ?? '' }} {{ $billing->pincode ?? '' }}
                                </p>
                            </div>
                            <div class="col-12 col-md-6">
                                <span class="text-muted small fw-bold d-block mb-1">Shipping Address:</span>
                                <p class="small text-body mb-0">
                                    {{ $shipping->address_line1 ?? 'Primary Warehouse Delivery Site' }}<br>
                                    {{ $shipping->city ?? '' }} {{ $shipping->state ?? '' }} {{ $shipping->pincode ?? '' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Line Items Table Card -->
                <div class="card rounded-4 border-translucent shadow-sm bg-body mb-4">
                    <div class="card-header bg-transparent border-bottom border-translucent p-3 d-flex align-items-center justify-content-between">
                        <h6 class="fw-bold text-body mb-0">📦 Commercial Itemized Products Proposal</h6>
                        <span class="badge bg-primary-subtle text-primary fw-bold">{{ $quotation->items->count() }} Line Items</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr class="text-muted small">
                                    <th class="ps-4">SKU</th>
                                    <th>Product Name</th>
                                    <th>Qty</th>
                                    <th>Unit Price</th>
                                    <th>Taxable</th>
                                    <th>GST Rate</th>
                                    <th class="pe-4 text-end">Line Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($quotation->items as $item)
                                    <tr>
                                        <td class="ps-4 font-monospace text-primary fw-bold">{{ $item->product->sku ?? 'SKU-N/A' }}</td>
                                        <td>
                                            <div class="fw-bold text-body">{{ $item->product->name ?? 'Product Line' }}</div>
                                            <div class="small text-muted">{{ $item->product->category->name ?? 'General Category' }}</div>
                                        </td>
                                        <td class="fw-bold">{{ $item->quantity }} {{ $item->product->unit->short_name ?? 'Pcs' }}</td>
                                        <td>₹{{ number_format((float)$item->unit_price, 2) }}</td>
                                        <td>₹{{ number_format((float)$item->taxable_value, 2) }}</td>
                                        <td>{{ number_format((float)$item->gst_rate, 1) }}%</td>
                                        <td class="pe-4 text-end fw-bold text-success">₹{{ number_format((float)$item->line_total, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Terms & Conditions Card -->
                <div class="card rounded-4 border-translucent shadow-sm bg-body">
                    <div class="card-header bg-transparent border-bottom border-translucent p-3">
                        <h6 class="fw-bold text-body mb-0">📜 Commercial Terms & Conditions</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <span class="text-muted small fw-semibold">Payment Terms:</span>
                                <div class="fw-bold text-body">{{ $quotation->payment_terms ?? 'Net 30 Days' }}</div>
                            </div>
                            <div class="col-12 col-md-6">
                                <span class="text-muted small fw-semibold">Delivery Terms:</span>
                                <div class="fw-bold text-body">{{ $quotation->delivery_terms ?? 'Ex-Warehouse Dispatch' }}</div>
                            </div>
                        </div>
                        @if($quotation->remarks)
                            <div class="mt-3 pt-3 border-top border-translucent">
                                <span class="text-muted small fw-semibold">Internal Remarks / Special Notes:</span>
                                <p class="text-body small mb-0 mt-1">{{ $quotation->remarks }}</p>
                            </div>
                        @endif
                    </div>
                </div>

            </div>

            <!-- Right Column: Central Order Tracking Status Center & Audit Timeline -->
            <div class="col-12 col-xl-4">

                <!-- CUSTOMER COMMUNICATION ENGINE (CCE SIMPLIFIED MVP) ACTIONABLE CONTROL CENTER CARD -->
                <div class="card rounded-4 border-translucent shadow-sm bg-body mb-4">
                    <div class="card-header bg-transparent border-bottom border-translucent p-3 d-flex align-items-center justify-content-between">
                        <h6 class="fw-bold text-body mb-0">📡 Customer Communication Center (CCE)</h6>
                        @if($cceRecord)
                            <span class="badge bg-success font-monospace" id="cce-badge-num">{{ $cceRecord->communication_number }}</span>
                        @else
                            <span class="badge bg-secondary">Draft Engine</span>
                        @endif
                    </div>
                    <div class="card-body p-3">
                        @if($cceRecord)
                            @php
                                $custEmail = $quotation->customer->email ?? $cceRecord->recipient_email;
                                $custPhone = $quotation->customer->phone ?? $cceRecord->recipient_mobile;
                                $hasValidEmail = !empty($custEmail) && filter_var($custEmail, FILTER_VALIDATE_EMAIL);
                                $hasValidPhone = !empty($custPhone) && strlen(preg_replace('/[^0-9]/', '', $custPhone)) >= 10;
                            @endphp

                            <!-- CCE Status & Profile Card -->
                            <div class="p-3 bg-body-tertiary rounded-3 border border-translucent mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="fw-bold text-body small">Document Version: <code class="text-primary">v{{ $cceRecord->document_version }}</code></span>
                                    <span class="badge {{ in_array($cceRecord->status, ['opened', 'delivered', 'viewed', 'completed']) ? 'bg-success' : 'bg-primary' }} text-white text-uppercase" id="cce-badge-status" style="font-size: 0.7rem;">{{ $cceRecord->status }}</span>
                                </div>

                                <div class="small text-muted mb-1">
                                    Recipient Company: <strong class="text-body">{{ $cceRecord->customer_name }}</strong>
                                </div>
                                <div class="small text-muted mb-1">
                                    ✉️ Email: 
                                    @if($hasValidEmail)
                                        <strong class="text-body">{{ $custEmail }}</strong>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger">Invalid / Missing Email</span>
                                    @endif
                                </div>
                                <div class="small text-muted mb-2">
                                    📞 Mobile / WA: 
                                    @if($hasValidPhone)
                                        <strong class="text-body">{{ $custPhone }}</strong>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger">Invalid / Missing Mobile</span>
                                    @endif
                                </div>

                                <div class="d-flex align-items-center justify-content-between pt-2 border-top border-translucent small">
                                    <span class="text-muted">Preferred Channel:</span>
                                    <span class="badge bg-info-subtle text-info-emphasis text-uppercase" id="cce-badge-channel">{{ $cceRecord->preferred_channel }}</span>
                                </div>
                            </div>

                            <!-- Timestamps & Secure Document Link Box -->
                            <div class="p-3 bg-body-tertiary rounded-3 border border-translucent mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-1" style="font-size: 0.75rem;">
                                    <span class="text-muted fw-semibold">Validation Status:</span>
                                    @if($hasValidEmail || $hasValidPhone)
                                        <span class="badge bg-success-subtle text-success">✔ Ready for Dispatch</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger">⚠️ Contact Info Required</span>
                                    @endif
                                </div>
                                <div class="small text-muted mb-2">
                                    Last Communication: <strong id="cce-time-sent">{{ $cceRecord->last_sent_at ? $cceRecord->last_sent_at->format('d M Y, h:i A') : 'Awaiting Dispatch' }}</strong>
                                </div>
                                <div class="text-muted" style="font-size: 0.72rem;">
                                    Secure Document Access Link:<br>
                                    <a href="{{ route('sales.quotations.pdf', $quotation->id) }}" target="_blank" class="text-primary font-monospace text-truncate d-block">
                                        {{ route('sales.quotations.pdf', $quotation->id) }}
                                    </a>
                                </div>
                            </div>

                            <!-- CCE Action Dispatch Buttons -->
                            <div class="d-flex flex-column gap-2">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <button type="button" class="btn btn-primary btn-sm rounded-3 fw-bold w-100 d-flex align-items-center justify-content-center gap-1 py-2"
                                                onclick="launchCce('email')" {{ !$hasValidEmail ? 'disabled title="Customer Master email is missing or invalid"' : '' }}>
                                            <span>📧</span> Send Email
                                        </button>
                                    </div>
                                    <div class="col-6">
                                        <button type="button" class="btn btn-success btn-sm rounded-3 fw-bold w-100 d-flex align-items-center justify-content-center gap-1 py-2"
                                                onclick="launchCce('whatsapp')" {{ !$hasValidPhone ? 'disabled title="Customer Master mobile is missing or invalid"' : '' }}>
                                            <span>💬</span> Send WhatsApp
                                        </button>
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <a href="{{ route('sales.quotations.pdf', $quotation->id) }}" target="_blank" class="btn btn-outline-dark btn-sm rounded-3 fw-semibold w-100 text-center">
                                        👁️ Preview PDF Proposal
                                    </a>
                                    <a href="{{ route('sales.quotations.pdf', $quotation->id) }}" target="_blank" class="btn btn-outline-secondary btn-sm rounded-3 fw-semibold w-100 text-center">
                                        📄 Download PDF
                                    </a>
                                </div>

                                <button type="button" class="btn btn-light btn-sm border rounded-3 text-muted w-100 mt-1" data-bs-toggle="modal" data-bs-target="#cceTimelineModal">
                                    📜 View Communication History ({{ $cceHistory->count() }} Records)
                                </button>
                            </div>
                        @else
                            <div class="text-center text-muted py-3 small">
                                Communication Record will automatically generate when quotation is generated.
                            </div>
                        @endif
                    </div>
                </div>

                <!-- LINKED ENTERPRISE ERP DOCUMENTS & LIVE ORDER TRACKING STATUS CENTER -->
                <div class="card rounded-4 border-translucent shadow-sm bg-body mb-4">
                    <div class="card-header bg-transparent border-bottom border-translucent p-3">
                        <h6 class="fw-bold text-body mb-0">🔗 Linked Enterprise ERP Status Center</h6>
                    </div>
                    <div class="card-body p-3">

                        <!-- 1. Sales Order Module Card -->
                        <div class="p-3 bg-body-tertiary rounded-3 border border-translucent mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="fw-bold small text-body">Commercial Sales Order</span>
                                @if($quotation->salesOrder)
                                    <span class="badge bg-purple text-white">SO #{{ $quotation->salesOrder->order_number }}</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Pending Conversion</span>
                                @endif
                            </div>
                            <div class="small text-muted mb-2">
                                Status: <strong class="text-body">{{ ucfirst($quotation->salesOrder->status ?? 'Not Generated') }}</strong>
                            </div>
                            @if($quotation->sales_order_id)
                                <a href="{{ route('sales.orders.show', $quotation->sales_order_id) }}" class="btn btn-xs btn-purple text-white rounded-3 fw-bold w-100 py-1.5 text-center">
                                    View Sales Order &rarr;
                                </a>
                            @else
                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-3 w-100 py-1.5" disabled>
                                    Pending Commercial Conversion
                                </button>
                            @endif
                        </div>

                        <!-- 2. Warehouse Fulfillment Module Card (Single Source of Truth) -->
                        <div class="p-3 bg-body-tertiary rounded-3 border border-translucent mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="fw-bold small text-body">Warehouse Execution Task</span>
                                @if($pickingTask)
                                    <span class="badge bg-info-subtle text-info-emphasis font-monospace" id="wh-card-tasknum">{{ $pickingTask->task_number }}</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Awaiting Order</span>
                                @endif
                            </div>
                            <div class="small text-muted mb-2">
                                Current Status: 
                                @if($pickingTask)
                                    <span class="badge bg-warning-subtle text-warning-emphasis text-capitalize" id="wh-card-status">{{ ucfirst(str_replace('_', ' ', $pickingTask->status)) }}</span>
                                @else
                                    <span class="text-muted fw-semibold">Pending Order Creation</span>
                                @endif
                            </div>

                            @if($pickingTask)
                                <!-- Live Progress Bar Barcode Single Source of Truth -->
                                <div class="mb-2">
                                    <div class="d-flex align-items-center justify-content-between mb-1" style="font-size: 0.75rem;">
                                        <span class="text-muted fw-semibold">Fulfillment Progress</span>
                                        <span class="fw-bold text-body" id="wh-card-count">{{ $pickingTask->verified_items_count }} / {{ $pickingTask->total_items_count }} Verified ({{ $pickingTask->completion_percentage }}%)</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar {{ $pickingTask->progress_color_class }}" id="wh-card-progressbar" role="progressbar" style="width: {{ $pickingTask->completion_percentage }}%; transition: width 0.4s ease;" aria-valuenow="{{ $pickingTask->completion_percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            @endif
                            <button type="button" class="btn btn-xs btn-outline-primary rounded-3 fw-bold w-100 py-1.5" data-bs-toggle="modal" data-bs-target="#warehouseStatusModal">
                                Warehouse Status
                            </button>
                        </div>

                        <!-- 3. Transport & Logistics Module Card (Single Source of Truth) -->
                        <div class="p-3 bg-body-tertiary rounded-3 border border-translucent">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="fw-bold small text-body">Transport & Logistics</span>
                                @if($transportRequest)
                                    <span class="badge bg-rose-subtle text-rose font-monospace" id="trp-card-reqnum">{{ $transportRequest->request_number }}</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Awaiting Dispatch</span>
                                @endif
                            </div>
                            <div class="small text-muted mb-2">
                                Current Status: 
                                @if($transportRequest)
                                    <span class="badge {{ $transportRequest->status === 'ready_for_dispatch' ? 'bg-success text-white' : 'bg-secondary-subtle text-secondary' }} text-capitalize" id="trp-card-status">
                                        {{ ucfirst(str_replace('_', ' ', $transportRequest->status)) }}
                                    </span>
                                @else
                                    <span class="text-muted fw-semibold">Pending Packaging</span>
                                @endif
                            </div>

                            @if($transportRequest)
                                <!-- Transport Progress Bar -->
                                <div class="mb-2">
                                    <div class="d-flex align-items-center justify-content-between mb-1" style="font-size: 0.75rem;">
                                        <span class="text-muted fw-semibold">Logistics Progress</span>
                                        <span class="fw-bold text-body" id="trp-card-pct">{{ $transportRequest->completion_percentage }}%</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar {{ $transportRequest->progress_color_class }}" id="trp-card-progressbar" role="progressbar" style="width: {{ $transportRequest->completion_percentage }}%; transition: width 0.4s ease;" aria-valuenow="{{ $transportRequest->completion_percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            @endif
                            <button type="button" class="btn btn-xs btn-outline-rose text-dark rounded-3 fw-bold w-100 py-1.5" data-bs-toggle="modal" data-bs-target="#transportStatusModal">
                                Transport Status
                            </button>
                        </div>

                    </div>
                </div>

                <!-- Complete Document Audit Timeline -->
                <div class="card rounded-4 border-translucent shadow-sm bg-body">
                    <div class="card-header bg-transparent border-bottom border-translucent p-3">
                        <h6 class="fw-bold text-body mb-0">🕒 End-to-End Audit & Event History</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="timeline position-relative">

                            <!-- Event 1: Quotation Creation -->
                            <div class="d-flex gap-3 mb-4 position-relative">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 32px; height: 32px; flex-shrink: 0; font-size: 0.75rem;">1</div>
                                <div>
                                    <strong class="text-body d-block small">Quotation Created</strong>
                                    <span class="text-muted small">Issued by: <strong>{{ $quotation->salesperson->name ?? 'Sales Exec' }}</strong></span>
                                    <div class="text-muted" style="font-size: 0.75rem;">{{ $quotation->created_at ? $quotation->created_at->format('d M Y, h:i A') : 'N/A' }}</div>
                                    <span class="badge bg-primary-subtle text-primary mt-1" style="font-size: 0.65rem;">Commercial Sales Dept</span>
                                </div>
                            </div>

                            <!-- Event 2: Approval Status -->
                            <div class="d-flex gap-3 mb-4 position-relative">
                                <div class="{{ in_array($quotation->status, ['approved', 'customer_accepted', 'converted']) ? 'bg-success text-white' : 'bg-warning text-dark' }} rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 32px; height: 32px; flex-shrink: 0; font-size: 0.75rem;">2</div>
                                <div>
                                    <strong class="text-body d-block small">Manager Approval</strong>
                                    @if(in_array($quotation->status, ['approved', 'customer_accepted', 'converted']))
                                        <span class="text-success small fw-semibold">Approved by: {{ $quotation->approvedBy->name ?? 'Commercial Manager' }}</span>
                                    @else
                                        <span class="text-warning-emphasis small fw-semibold">Pending Manager Review</span>
                                    @endif
                                    <span class="badge bg-secondary-subtle text-secondary d-block mt-1 w-fit" style="font-size: 0.65rem;">Sales Management</span>
                                </div>
                            </div>

                            <!-- Event 3: Conversion Status -->
                            <div class="d-flex gap-3 position-relative">
                                <div class="{{ $quotation->status === 'converted' ? 'bg-purple text-white' : 'bg-secondary text-white' }} rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 32px; height: 32px; flex-shrink: 0; font-size: 0.75rem;">3</div>
                                <div>
                                    <strong class="text-body d-block small">Sales Order Conversion</strong>
                                    @if($quotation->status === 'converted')
                                        <span class="text-purple small fw-bold">Converted to SO #{{ $quotation->salesOrder->order_number ?? 'SO-LINKED' }}</span>
                                        <div class="text-muted" style="font-size: 0.75rem;">{{ $quotation->converted_at ? $quotation->converted_at->format('d M Y, h:i A') : 'N/A' }}</div>
                                    @else
                                        <span class="text-muted small">Awaiting Conversion</span>
                                    @endif
                                    <span class="badge bg-secondary-subtle text-secondary d-block mt-1 w-fit" style="font-size: 0.65rem;">Enterprise ERP Connector</span>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<!-- ======================================================================= -->
<!-- CCE SIMPLIFIED MVP: COMMUNICATION TIMELINE & AUDIT HISTORY MODAL -->
<!-- ======================================================================= -->
@if($cceRecord)
<div class="modal fade" id="cceTimelineModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-translucent shadow-lg rounded-4">
            <div class="modal-header border-bottom border-translucent p-4">
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-4">📜</span>
                    <div>
                        <h5 class="modal-title fw-bold text-body mb-0">Customer Communication Timeline & Audit History</h5>
                        <span class="small text-muted">CCE Record: <code class="text-success fw-bold">{{ $cceRecord->communication_number }}</code></span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="table-responsive border border-translucent rounded-3">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Timestamp</th>
                                <th>Event Name</th>
                                <th>Stage</th>
                                <th>User / System Notes</th>
                            </tr>
                        </thead>
                        <tbody id="cce-timeline-tbody">
                            @foreach($cceRecord->timelines as $t)
                                <tr>
                                    <td class="ps-3 small text-muted">{{ $t->created_at ? $t->created_at->format('d M Y, h:i A') : 'N/A' }}</td>
                                    <td class="fw-bold text-body">{{ $t->event_name }}</td>
                                    <td><span class="badge bg-secondary-subtle text-secondary font-monospace">{{ $t->to_state ?? $t->from_state ?? 'log' }}</span></td>
                                    <td class="small text-muted">{{ $t->notes ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-top border-translucent p-3">
                <button type="button" class="btn btn-secondary btn-sm rounded-3 fw-semibold" data-bs-dismiss="modal">Close Window</button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Manager Rejection Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-translucent shadow-lg rounded-4">
            <form method="POST" action="{{ route('sales.quotations.reject', $quotation->id) }}">
                @csrf
                <div class="modal-header border-bottom border-translucent p-4">
                    <h5 class="modal-title fw-bold text-body">Reject Quotation Proposal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <label class="form-label fw-bold text-body small">Reason for Rejection:</label>
                    <textarea name="reason" class="form-control rounded-3" rows="3" required placeholder="Specify reason for commercial proposal rejection..."></textarea>
                </div>
                <div class="modal-footer border-top border-translucent p-3">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger rounded-3 fw-bold">Reject Quotation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- LINKED WAREHOUSE & TRANSPORT LIVE STATUS MODALS -->
<x-linked-status-modals
    :pickingTask="$pickingTask"
    :transportRequest="$transportRequest"
    :orderRef="$quotation->salesOrder->order_number ?? $quotation->quotation_number"
    :liveStatusUrl="route('sales.quotations.live-status', $quotation->id)"
/>

@push('scripts')
<script>
function launchCce(channel) {
    var quotationId = {{ $quotation->id }};
    var alertBox = document.getElementById('cceGlobalAlert');

    fetch("{{ route('sales.cce.launch') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            quotation_id: quotationId,
            channel: channel
        })
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (!data.success) {
            if (alertBox) {
                alertBox.className = 'alert alert-danger border-0 rounded-3 shadow-sm mb-4';
                alertBox.textContent = '⚠️ ' + (data.message || 'Launch failed.');
                alertBox.classList.remove('d-none');
            }
            return;
        }

        // Show Global Alert
        if (alertBox) {
            alertBox.className = 'alert alert-success border-0 rounded-3 shadow-sm mb-4';
            alertBox.textContent = data.message + " Opening " + channel.toUpperCase() + "...";
            alertBox.classList.remove('d-none');
        }

        // Update CCE Badge and Last Sent timestamp
        var badgeStatus = document.getElementById('cce-badge-status');
        if (badgeStatus) {
            badgeStatus.textContent = 'OPENED';
            badgeStatus.className = 'badge bg-success text-white text-uppercase';
        }

        var channelBadge = document.getElementById('cce-badge-channel');
        if (channelBadge) channelBadge.textContent = channel.toUpperCase();

        var timeSent = document.getElementById('cce-time-sent');
        if (timeSent) timeSent.textContent = new Date().toLocaleString();

        // Open target mailto: or wa.me URL
        if (data.payload && data.payload.launch_url) {
            window.open(data.payload.launch_url, '_blank');
        }
    })
    .catch(function(err) {
        if (alertBox) {
            alertBox.className = 'alert alert-danger border-0 rounded-3 shadow-sm mb-4';
            alertBox.textContent = '⚠️ Network error during launcher execution.';
            alertBox.classList.remove('d-none');
        }
        console.error('CCE launch error:', err);
    });
}
</script>
@endpush

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteQuotationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom bg-danger-subtle text-danger rounded-top-4 py-3">
                <h5 class="modal-title fw-bold">🗑️ Confirm Quotation Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="display-6 text-danger mb-3">⚠️</div>
                <h6 class="fw-bold text-body">Are you sure you want to delete this quotation?</h6>
                <p class="text-muted small mb-0">
                    Quotation proposal <strong class="text-dark">{{ $quotation->quotation_number }}</strong> will be permanently removed.
                    This action cannot be undone.
                </p>
            </div>
            <div class="modal-footer border-top bg-light rounded-bottom-4 py-2.5">
                <button type="button" class="btn btn-secondary rounded-3 px-3 fw-bold" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="{{ route('sales.quotations.destroy', $quotation->id) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger rounded-3 px-4 fw-bold">Delete Quotation &rarr;</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
