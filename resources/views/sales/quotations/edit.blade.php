@extends('layouts.app')

@section('title', 'Edit Quotation #' . $quotation->quotation_number . ' - StockManager ERP')

@section('content')
<div class="d-flex gap-3">
    <!-- Sales Workspace Sidebar -->
    <x-sales-sidebar activeTab="quotations" />

    <!-- Main Workspace Area -->
    <div class="flex-grow-1">

        <!-- Header -->
        <div class="card p-3 rounded-4 shadow-sm border-translucent mb-3 bg-body">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="fw-bold text-body mb-0">✏️ Edit Quotation #{{ $quotation->quotation_number }}</h4>
                    <p class="text-muted small mb-0">Modify products, quantities, customer pricing, and commercial terms</p>
                </div>
                <div>
                    <a href="{{ route('sales.quotations.show', $quotation->id) }}" class="btn btn-outline-secondary btn-sm rounded-3">&larr; Back to Proposal</a>
                </div>
            </div>
        </div>

        @if(session('error'))
            <div class="alert alert-danger border-0 rounded-3 shadow-sm mb-3">
                ⚠️ {{ session('error') }}
            </div>
        @endif

        <div class="row g-3">
            <!-- LEFT PANEL (40%): Canonical Product Search & Selector -->
            <div class="col-lg-5">
                <div class="card p-3 rounded-4 border-translucent shadow-sm bg-body sticky-top" style="top: 80px; z-index: 10;">
                    <h6 class="fw-bold text-body mb-3 border-bottom pb-2">Canonical Product Catalog</h6>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Search Catalog</label>
                        <input type="text" id="catalogSearch" class="form-control form-control-sm rounded-3" placeholder="Search SKU, Name, Code..." onkeyup="filterProducts()">
                    </div>

                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-sm table-hover align-middle mb-0" id="productsTable">
                            <thead class="table-light sticky-top">
                                <tr class="small text-muted">
                                    <th>Product</th>
                                    <th>Stock</th>
                                    <th>Price</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($products as $p)
                                    @php
                                        $available = max(0, (int)$p->physical_stock - (int)($p->reserved_stock ?? 0));
                                    @endphp
                                    <tr class="product-row" data-name="{{ strtolower($p->name) }}" data-sku="{{ strtolower($p->sku) }}" data-code="{{ strtolower($p->code ?? '') }}">
                                        <td>
                                            <div class="fw-bold small text-body text-truncate" style="max-width: 150px;" title="{{ $p->name }}">{{ $p->name }}</div>
                                            <div class="small text-muted font-monospace" style="font-size: 0.7rem;">{{ $p->sku }}</div>
                                        </td>
                                        <td>
                                            @if($available > ($p->reorder_level ?? 5))
                                                <span class="badge bg-success-subtle text-success">Avail: {{ $available }}</span>
                                            @elseif($available > 0)
                                                <span class="badge bg-warning-subtle text-warning">Low: {{ $available }}</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger">Out of Stock</span>
                                            @endif
                                        </td>
                                        <td class="fw-bold small">₹{{ number_format((float)$p->selling_price, 2) }}</td>
                                        <td>
                                            <button type="button" class="btn btn-outline-warning btn-sm py-0 px-2 rounded-2 fw-bold text-dark"
                                                    onclick="addProductToQuotation({{ $p->id }}, '{{ addslashes($p->name) }}', '{{ $p->sku }}', {{ $p->selling_price }}, {{ $p->tax->rate ?? 18.0 }}, {{ $available }})">
                                                + Add
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- RIGHT PANEL (60%): Quotation Form & Items Table -->
            <div class="col-lg-7">
                <div class="card p-3 rounded-4 border-translucent shadow-sm bg-body">
                    <h6 class="fw-bold text-body mb-3 border-bottom pb-2">Quotation Details & Items</h6>

                    <form method="POST" action="{{ route('sales.quotations.update', $quotation->id) }}" id="editQuotationForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="cart_data" id="cartDataInput">

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Customer Account <span class="text-danger">*</span></label>
                                <select name="customer_id" id="customerSelect" class="form-select form-select-sm rounded-3" required>
                                    @foreach($customers as $cust)
                                        <option value="{{ $cust->id }}" {{ $quotation->customer_id == $cust->id ? 'selected' : '' }}>
                                            {{ $cust->company_name }} ({{ strtoupper($cust->customer_type) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Validity Days</label>
                                <input type="number" name="validity_days" class="form-control form-control-sm rounded-3" value="30" min="1">
                            </div>
                        </div>

                        <!-- Quotation Items Table -->
                        <div class="table-responsive mb-3" style="max-height: 350px; overflow-y: auto;">
                            <table class="table table-sm table-borderless align-middle mb-0" id="itemsTable">
                                <thead class="table-light border-bottom">
                                    <tr class="small text-muted">
                                        <th>Product</th>
                                        <th style="width: 80px;">Qty</th>
                                        <th style="width: 90px;">Unit Price</th>
                                        <th style="width: 100px;">Line Total</th>
                                        <th style="width: 35px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsTableBody">
                                    <!-- Populated via JS -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Financial Math Summary Box -->
                        <div class="p-3 bg-body-tertiary rounded-3 border border-translucent mb-3">
                            <div class="d-flex justify-content-between small mb-1"><span class="text-muted">Subtotal:</span><span class="fw-bold" id="lblSubtotal">₹0.00</span></div>
                            <div class="d-flex justify-content-between small mb-1"><span class="text-muted">GST Tax (18% Avg):</span><span class="fw-bold text-info" id="lblGst">₹0.00</span></div>
                            <hr class="my-2 border-translucent">
                            <div class="d-flex justify-content-between align-items-center"><span class="fw-bold text-body">Grand Total:</span><span class="fs-4 fw-black text-success" id="lblGrandTotal">₹0.00</span></div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-warning w-100 rounded-3 fw-bold text-dark py-2" id="btnUpdateQuotation">
                                Save Quotation Changes &rarr;
                            </button>
                            <a href="{{ route('sales.quotations.show', $quotation->id) }}" class="btn btn-outline-secondary rounded-3 px-4 fw-semibold">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stock Violation Warning Modal -->
<div class="modal fade" id="stockWarningModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom bg-danger-subtle text-danger rounded-top-4">
                <h5 class="modal-title fw-bold" id="stockModalTitle">⚠️ Stock Availability Violation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="stockModalBody">
                <!-- Injected via JS -->
            </div>
            <div class="modal-footer border-top bg-light rounded-bottom-4">
                <button type="button" class="btn btn-secondary rounded-3 fw-bold px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let quotationItems = [
    @foreach($quotation->items as $item)
        @php
            $avail = max(0, (int)($item->product->physical_stock ?? 0) - (int)($item->product->reserved_stock ?? 0));
        @endphp
        {
            product_id: {{ $item->product_id }},
            name: "{{ addslashes($item->product->name ?? 'Product') }}",
            sku: "{{ $item->product->sku ?? 'SKU' }}",
            unit_price: {{ $item->unit_price }},
            quantity: {{ $item->quantity }},
            gst_rate: {{ $item->gst_rate ?? 18.0 }},
            available_stock: {{ $avail }}
        },
    @endforeach
];

document.addEventListener('DOMContentLoaded', function() {
    renderQuotationItems();
});

function filterProducts() {
    let q = document.getElementById('catalogSearch').value.toLowerCase().trim();
    let rows = document.querySelectorAll('.product-row');
    rows.forEach(r => {
        let name = r.getAttribute('data-name');
        let sku = r.getAttribute('data-sku');
        let code = r.getAttribute('data-code');
        if (name.includes(q) || sku.includes(q) || code.includes(q)) {
            r.style.display = '';
        } else {
            r.style.display = 'none';
        }
    });
}

function showStockModal(title, bodyHtml) {
    document.getElementById('stockModalTitle').textContent = title;
    document.getElementById('stockModalBody').innerHTML = bodyHtml;
    let modal = new bootstrap.Modal(document.getElementById('stockWarningModal'));
    modal.show();
}

function addProductToQuotation(id, name, sku, price, gstRate, availableStock) {
    if (availableStock <= 0) {
        showStockModal("⚠️ Product Out of Stock", `
            <div class="p-3 bg-danger-subtle text-danger rounded-3 mb-3 border border-danger-subtle">
                <h6 class="fw-bold mb-1">Product Cannot Be Added</h6>
                <div><strong>${name}</strong> (SKU: <code>${sku}</code>) is currently out of stock in inventory master.</div>
            </div>
            <div class="small text-muted"><strong>Available Quantity:</strong> 0 units. Please receive or adjust stock in Inventory before offering to customer.</div>
        `);
        return;
    }

    let existing = quotationItems.find(i => i.product_id === id);
    let targetQty = existing ? existing.quantity + 1 : 1;

    if (targetQty > availableStock) {
        let shortage = targetQty - availableStock;
        showStockModal("⚠️ Insufficient Stock", `
            <div class="p-3 bg-warning-subtle text-dark rounded-3 mb-3 border border-warning-subtle">
                <h6 class="fw-bold mb-1">Insufficient Available Inventory</h6>
                <div>Cannot add requested quantity for <strong>${name}</strong> (SKU: <code>${sku}</code>).</div>
            </div>
            <ul class="list-group list-group-flush small mb-0 rounded-3 border">
                <li class="list-group-item d-flex justify-content-between"><span>Requested Quantity:</span><strong>${targetQty}</strong></li>
                <li class="list-group-item d-flex justify-content-between"><span>Currently Available Stock:</span><strong class="text-success">${availableStock}</strong></li>
                <li class="list-group-item d-flex justify-content-between bg-light"><span>Shortage:</span><strong class="text-danger">${shortage}</strong></li>
            </ul>
        `);
        return;
    }

    if (existing) {
        existing.quantity = targetQty;
    } else {
        quotationItems.push({
            product_id: id,
            name: name,
            sku: sku,
            unit_price: price,
            quantity: 1,
            gst_rate: gstRate,
            available_stock: availableStock
        });
    }

    renderQuotationItems();
}

function updateItemQty(id, newQtyVal) {
    let qty = parseInt(newQtyVal);
    let item = quotationItems.find(i => i.product_id === id);
    if (!item) return;

    if (isNaN(qty) || qty <= 0) {
        showStockModal("⚠️ Invalid Quantity", `
            <div class="p-3 bg-danger-subtle text-danger rounded-3">
                Quantity must be a positive integer greater than zero.
            </div>
        `);
        renderQuotationItems();
        return;
    }

    if (qty > item.available_stock) {
        let shortage = qty - item.available_stock;
        showStockModal("⚠️ Insufficient Stock", `
            <div class="p-3 bg-warning-subtle text-dark rounded-3 mb-3 border border-warning-subtle">
                <h6 class="fw-bold mb-1">Quantity Exceeds Available Inventory</h6>
                <div>Requested quantity for <strong>${item.name}</strong> exceeds live inventory level.</div>
            </div>
            <ul class="list-group list-group-flush small mb-0 rounded-3 border">
                <li class="list-group-item d-flex justify-content-between"><span>Requested Quantity:</span><strong>${qty}</strong></li>
                <li class="list-group-item d-flex justify-content-between"><span>Available Stock:</span><strong class="text-success">${item.available_stock}</strong></li>
                <li class="list-group-item d-flex justify-content-between bg-light"><span>Shortage:</span><strong class="text-danger">${shortage}</strong></li>
            </ul>
        `);
        renderQuotationItems();
        return;
    }

    item.quantity = qty;
    renderQuotationItems();
}

function removeItem(id) {
    quotationItems = quotationItems.filter(i => i.product_id !== id);
    renderQuotationItems();
}

function renderQuotationItems() {
    let body = document.getElementById('itemsTableBody');
    let cartInput = document.getElementById('cartDataInput');
    let btn = document.getElementById('btnUpdateQuotation');

    if (quotationItems.length === 0) {
        body.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted small">No products in quotation. Click "+ Add" on catalog products.</td></tr>';
        document.getElementById('lblSubtotal').textContent = '₹0.00';
        document.getElementById('lblGst').textContent = '₹0.00';
        document.getElementById('lblGrandTotal').textContent = '₹0.00';
        cartInput.value = '';
        btn.disabled = true;
        return;
    }

    let html = '';
    let subtotal = 0;
    let gstTotal = 0;

    quotationItems.forEach(item => {
        let lineSub = item.unit_price * item.quantity;
        let lineGst = (lineSub * item.gst_rate) / 100.0;
        subtotal += lineSub;
        gstTotal += lineGst;

        html += `
            <tr class="border-bottom">
                <td>
                    <div class="fw-bold text-body small text-truncate" style="max-width: 140px;">${item.name}</div>
                    <div class="small text-muted font-monospace" style="font-size: 0.7rem;">${item.sku}</div>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm px-1 py-0 text-center" value="${item.quantity}" min="1" onchange="updateItemQty(${item.product_id}, this.value)">
                </td>
                <td class="small font-monospace">₹${parseFloat(item.unit_price).toFixed(2)}</td>
                <td class="fw-semibold small font-monospace">₹${lineSub.toFixed(2)}</td>
                <td>
                    <button type="button" class="btn btn-link text-danger p-0 border-0 fs-5 lh-1" onclick="removeItem(${item.product_id})">&times;</button>
                </td>
            </tr>
        `;
    });

    body.innerHTML = html;
    let grand = subtotal + gstTotal;

    document.getElementById('lblSubtotal').textContent = '₹' + subtotal.toFixed(2);
    document.getElementById('lblGst').textContent = '₹' + gstTotal.toFixed(2);
    document.getElementById('lblGrandTotal').textContent = '₹' + grand.toFixed(2);

    cartInput.value = JSON.stringify(quotationItems);
    btn.disabled = false;
}
</script>
@endpush
@endsection
