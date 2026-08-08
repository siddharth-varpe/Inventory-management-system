@extends('layouts.app')

@section('title', 'Sales Workspace - Live Product Catalog & Cart')

@section('content')
<div class="d-flex gap-3">
    <!-- Sales Workspace Sidebar -->
    <x-sales-sidebar activeTab="workspace" />

    <!-- Main 3-Panel Sales Workspace Grid -->
    <div class="flex-grow-1">

        <!-- Header -->
        <div class="card p-3 rounded-4 shadow-sm border-translucent mb-3 bg-body">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="fw-bold text-body mb-0">Sales Workspace & Quotation Builder</h4>
                    <p class="text-muted small mb-0">Live Product Catalog, Customer Pricing Tier Calculator & Auto-GST Math Engine</p>
                </div>
                <div>
                    <a href="{{ route('sales.quotations.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">Quotations Queue &rarr;</a>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <!-- LEFT PANEL (30%): Product Search & Filters -->
            <div class="col-lg-3">
                <div class="card p-3 rounded-4 border-translucent shadow-sm bg-body sticky-top" style="top: 80px; z-index: 10;">
                    <h6 class="fw-bold text-body mb-3 border-bottom pb-2">Catalog Filters</h6>
                    
                    <form method="GET" action="{{ route('sales.workspace') }}">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Instant Search</label>
                            <input type="text" name="search" class="form-control form-control-sm rounded-3" placeholder="SKU, Name, Barcode..." value="{{ request('search') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Category</label>
                            <select name="category_id" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                                <option value="">All Categories</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Brand</label>
                            <select name="brand_id" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                                <option value="">All Brands</option>
                                @foreach($brands as $b)
                                    <option value="{{ $b->id }}" {{ request('brand_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-warning btn-sm w-100 rounded-3 fw-bold text-dark">Apply Filters</button>
                            @if(request()->hasAny(['search', 'category_id', 'brand_id']))
                                <a href="{{ route('sales.workspace') }}" class="btn btn-outline-danger btn-sm rounded-3">Reset</a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- CENTER PANEL (40%): Live Product Cards -->
            <div class="col-lg-5">
                <div class="row g-3">
                    @forelse($products as $p)
                        @php
                            $availableStock = max(0, $p->physical_stock - ($p->reserved_stock ?? 0));
                        @endphp
                        <div class="col-md-6">
                            <div class="card p-3 rounded-4 border-translucent shadow-sm bg-body h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="badge bg-secondary font-monospace" style="font-size: 0.65rem;">{{ $p->sku }}</span>
                                        @if($availableStock > $p->reorder_level)
                                            <span class="badge bg-success-subtle text-success">Stock: {{ $availableStock }}</span>
                                        @elseif($availableStock > 0)
                                            <span class="badge bg-warning-subtle text-warning">Low: {{ $availableStock }}</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger">Out of Stock</span>
                                        @endif
                                    </div>
                                    <h6 class="fw-bold text-body mb-1 text-truncate" title="{{ $p->name }}">{{ $p->name }}</h6>
                                    <div class="small text-muted mb-2">{{ $p->category->name ?? 'General' }} | {{ $p->brand->name ?? 'Generic' }}</div>
                                    
                                    <div class="d-flex align-items-baseline gap-2 mb-3">
                                        <span class="fs-5 fw-black text-primary">₹{{ number_format((float)$p->selling_price, 2) }}</span>
                                        <span class="small text-muted">/ {{ $p->unit->code ?? 'Unit' }}</span>
                                    </div>
                                </div>

                                <button type="button" class="btn btn-outline-warning btn-sm rounded-3 fw-bold text-dark w-100 d-flex align-items-center justify-content-center gap-1"
                                        onclick="addToCart({{ $p->id }}, '{{ addslashes($p->name) }}', '{{ $p->sku }}', {{ $p->selling_price }}, {{ $p->tax->rate ?? 18.0 }})">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-cart-plus" viewBox="0 0 16 16">
                                        <path d="M9 5.5a.5.5 0 0 0-1 0V7H6.5a.5.5 0 0 0 0 1H8v1.5a.5.5 0 0 0 1 0V8h1.5a.5.5 0 0 0 0-1H9V5.5z"/>
                                        <path d="M.5 1a.5.5 0 0 0 0 1h1.11l.401 1.607 1.498 7.985A.5.5 0 0 0 4 12h1a2 2 0 1 0 0 4 2 2 0 0 0 0-4h7a2 2 0 1 0 0 4 2 2 0 0 0 0-4h1a.5.5 0 0 0 .491-.408l1.5-8A.5.5 0 0 0 14.5 3H3.89l-.371-1.485A.5.5 0 0 0 3 1H.5z"/>
                                    </svg>
                                    <span>Add to Cart</span>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-5 text-muted">No active products found matching filter criteria.</div>
                    @endforelse
                </div>

                @if($products->hasPages())
                    <div class="mt-3">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>

            <!-- RIGHT PANEL (30%): Customer Selector, Live Customer Master Card, Cart & Math Engine -->
            <div class="col-lg-4">
                <div class="card p-3 rounded-4 border-translucent shadow-sm bg-body sticky-top" style="top: 80px; z-index: 10;">
                    <h6 class="fw-bold text-body mb-3 border-bottom pb-2">Sales Cart & Quotation Summary</h6>

                    <form method="POST" action="{{ route('sales.quotations.store') }}" id="quotationForm">
                        @csrf
                        <input type="hidden" name="cart_data" id="cartDataInput">

                        <!-- Customer Selector -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Select Customer <span class="text-danger">*</span></label>
                            <select name="customer_id" id="customerSelect" class="form-select form-select-sm rounded-3" required onchange="onCustomerSelectChange()">
                                <option value="">-- Choose Account --</option>
                                @foreach($customers as $cust)
                                    @php
                                        $billing = $cust->addresses->where('address_type', 'billing')->first();
                                        $shipping = $cust->addresses->where('address_type', 'shipping')->first() ?? $billing;
                                        $addrStr = $billing ? "{$billing->address_line1}, {$billing->city}, {$billing->state}" : 'Primary Commercial Site';
                                    @endphp
                                    <option value="{{ $cust->id }}"
                                            data-name="{{ $cust->company_name }}"
                                            data-code="{{ $cust->customer_code ?? 'CUST-'.$cust->id }}"
                                            data-person="{{ $cust->contact_person ?? 'N/A' }}"
                                            data-email="{{ $cust->email ?? 'N/A' }}"
                                            data-phone="{{ $cust->phone ?? 'N/A' }}"
                                            data-gst="{{ $cust->gst_number ?? 'UNREGISTERED' }}"
                                            data-type="{{ $cust->customer_type }}"
                                            data-payment="{{ $cust->payment_term ?? 'Net 30 Days' }}"
                                            data-channel="{{ strtoupper($cust->preferred_communication_channel ?? 'EMAIL') }}"
                                            data-address="{{ $addrStr }}">
                                        {{ $cust->company_name }} ({{ strtoupper($cust->customer_type) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Customer Master Live Resolved Communication Profile Card -->
                        <div id="customerProfileCard" class="p-3 bg-body-tertiary rounded-3 border border-translucent mb-3 d-none">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="fw-bold small text-body" id="cp-company">Company Name</span>
                                <span class="badge bg-success-subtle text-success" id="cp-channel">EMAIL</span>
                            </div>
                            <div class="small text-muted" id="cp-person">Contact: Person</div>
                            <div class="small text-muted" id="cp-email">✉️ email@example.com</div>
                            <div class="small text-muted mb-2" id="cp-phone">📞 +91-0000000000</div>
                            <div class="p-2 bg-body rounded border border-translucent text-muted" style="font-size: 0.72rem;">
                                GST: <code class="text-dark fw-bold" id="cp-gst">GSTIN</code> | Terms: <strong class="text-dark" id="cp-payment">Net 30</strong><br>
                                Address: <span id="cp-address">Address</span>
                            </div>
                        </div>

                        <!-- Cart Line Items Table -->
                        <div class="table-responsive mb-3" style="max-height: 250px; overflow-y: auto;">
                            <table class="table table-sm table-borderless align-middle mb-0" id="cartTable">
                                <thead class="table-light border-bottom">
                                    <tr class="small text-muted">
                                        <th>Product</th>
                                        <th style="width: 60px;">Qty</th>
                                        <th style="width: 70px;">Price</th>
                                        <th style="width: 30px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="cartItemsBody">
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted small">Cart is empty. Click "Add to Cart" on products.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Math Summary Box -->
                        <div class="p-3 bg-body-tertiary rounded-3 border border-translucent mb-3">
                            <div class="d-flex justify-content-between small mb-1"><span class="text-muted">Subtotal:</span><span class="fw-bold" id="lblSubtotal">₹0.00</span></div>
                            <div class="d-flex justify-content-between small mb-1"><span class="text-muted">GST Tax (18% Avg):</span><span class="fw-bold text-info" id="lblGst">₹0.00</span></div>
                            <hr class="my-2 border-translucent">
                            <div class="d-flex justify-content-between align-items-center"><span class="fw-bold text-body">Grand Total:</span><span class="fs-4 fw-black text-success" id="lblGrandTotal">₹0.00</span></div>
                        </div>

                        <button type="submit" class="btn btn-warning w-100 rounded-3 fw-bold text-dark py-2" id="btnGenerateQuotation" disabled>
                            Generate Official Quotation &rarr;
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
let cart = [];

function onCustomerSelectChange() {
    let select = document.getElementById('customerSelect');
    let card = document.getElementById('customerProfileCard');

    if (select.value) {
        let opt = select.options[select.selectedIndex];
        document.getElementById('cp-company').textContent = opt.getAttribute('data-name');
        document.getElementById('cp-channel').textContent = opt.getAttribute('data-channel');
        document.getElementById('cp-person').textContent = "Contact: " + opt.getAttribute('data-person');
        document.getElementById('cp-email').textContent = "✉️ " + opt.getAttribute('data-email');
        document.getElementById('cp-phone').textContent = "📞 " + opt.getAttribute('data-phone');
        document.getElementById('cp-gst').textContent = opt.getAttribute('data-gst');
        document.getElementById('cp-payment').textContent = opt.getAttribute('data-payment');
        document.getElementById('cp-address').textContent = opt.getAttribute('data-address');
        card.classList.remove('d-none');
    } else {
        card.classList.add('d-none');
    }

    renderCart();
}

function addToCart(id, name, sku, basePrice, gstRate) {
    let existing = cart.find(item => item.product_id === id);
    if (existing) {
        existing.quantity += 1;
    } else {
        cart.push({
            product_id: id,
            name: name,
            sku: sku,
            unit_price: basePrice,
            quantity: 1,
            discount_amount: 0,
            discount_type: 'percentage',
            gst_rate: gstRate
        });
    }
    renderCart();
}

function removeFromCart(id) {
    cart = cart.filter(item => item.product_id !== id);
    renderCart();
}

function updateQty(id, qty) {
    let item = cart.find(i => i.product_id === id);
    if (item) {
        item.quantity = Math.max(1, parseInt(qty) || 1);
    }
    renderCart();
}

function renderCart() {
    let body = document.getElementById('cartItemsBody');
    let input = document.getElementById('cartDataInput');
    let btn = document.getElementById('btnGenerateQuotation');

    if (cart.length === 0) {
        body.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted small">Cart is empty. Click "Add to Cart" on products.</td></tr>';
        document.getElementById('lblSubtotal').textContent = '₹0.00';
        document.getElementById('lblGst').textContent = '₹0.00';
        document.getElementById('lblGrandTotal').textContent = '₹0.00';
        input.value = '';
        btn.disabled = true;
        return;
    }

    let html = '';
    let subtotal = 0;
    let gstTotal = 0;

    cart.forEach(item => {
        let lineSub = item.unit_price * item.quantity;
        let lineGst = (lineSub * item.gst_rate) / 100.0;
        subtotal += lineSub;
        gstTotal += lineGst;

        html += `
            <tr class="border-bottom">
                <td>
                    <div class="fw-bold text-body small text-truncate" style="max-width: 110px;">${item.name}</div>
                    <div class="small text-muted font-monospace">${item.sku}</div>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm px-1 py-0 text-center" value="${item.quantity}" min="1" onchange="updateQty(${item.product_id}, this.value)">
                </td>
                <td class="fw-semibold small">₹${lineSub.toFixed(2)}</td>
                <td>
                    <button type="button" class="btn btn-link text-danger p-0 border-0" onclick="removeFromCart(${item.product_id})">&times;</button>
                </td>
            </tr>
        `;
    });

    body.innerHTML = html;
    let grand = subtotal + gstTotal;

    document.getElementById('lblSubtotal').textContent = '₹' + subtotal.toFixed(2);
    document.getElementById('lblGst').textContent = '₹' + gstTotal.toFixed(2);
    document.getElementById('lblGrandTotal').textContent = '₹' + grand.toFixed(2);

    input.value = JSON.stringify(cart);
    btn.disabled = (document.getElementById('customerSelect').value === '');
}
</script>
@endpush
@endsection
