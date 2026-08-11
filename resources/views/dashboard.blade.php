@extends('layouts.app')

@section('title', 'Enterprise Operations Hub')

@section('content')
<!-- Page Header Component -->
<div class="mb-4">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fw-bold text-uppercase micro-text">Unified Control Center</span>
            <h2 class="h3 fw-extrabold text-body mb-0 mt-1">Enterprise Operations Hub</h2>
            <p class="text-muted mb-0 small">Select an operational portal below to manage inventory, warehouse, logistics, and sales workflows.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill fw-semibold shadow-none" data-bs-toggle="modal" data-bs-target="#systemGuideModal">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-book me-1" viewBox="0 0 16 16">
                    <path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811V2.828zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492V2.687zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.334-.896 3.896-1.058 1.405-.145 2.805.04 3.397.53.592-.49 1.992-.675 3.397-.53 1.562.162 3.014.658 3.896 1.058A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783z"/>
                </svg>
                System Guide
            </button>
            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-bold font-monospace">
                🟢 Production Mode
            </span>
        </div>
    </div>
</div>

<!-- Responsive Portal Cards Grid -->
<div class="row g-3">

    <!-- Card 1: Organize Stock -->
    <x-portal-card
        title="Organize Stock"
        badge="WAREHOUSE"
        description="Warehouse bin locations, stock movements, rack assignments and physical storage organization."
        theme="blue"
        route="{{ route('organize.dashboard') }}"
        actionCount="{{ $b['organize_stock'] ?? 0 }}"
        icon='<svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="currentColor" class="bi bi-grid-3x3-gap-fill" viewBox="0 0 16 16"><path d="M1 2a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1zm5 0a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1zm5 0a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1zM1 7a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1zm5 0a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1zm5 0a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1zM1 12a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1zm5 0a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1zm5 0a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1z"/></svg>'
    />

    <!-- Card 2: Receive Goods -->
    <x-portal-card
        title="Receive Goods"
        badge="Inbound"
        description="Process inbound PO deliveries, supplier shipments, quality inspection and stock receiving."
        theme="emerald"
        :isComingSoon="true"
        actionCount="{{ $b['receive_goods'] ?? 0 }}"
        icon='<svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="currentColor" class="bi bi-box-arrow-in-down" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M3.5 6a.5.5 0 0 0-.5.5v8a.5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5v-8a.5.5 0 0 0-.5-.5h-2a.5.5 0 0 1 0-1h2A1.5 1.5 0 0 1 14 6.5v8a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 14.5v-8A1.5 1.5 0 0 1 3.5 5h2a.5.5 0 0 1 0 1h-2z"/><path fill-rule="evenodd" d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/></svg>'
    />

    <!-- Card 3: Send Goods -->
    <x-portal-card
        title="Send Goods"
        badge="Fulfillment"
        description="Reserve inventory, prepare dispatches, validate stock availability and coordinate shipments."
        theme="indigo"
        :isComingSoon="true"
        actionCount="{{ $b['send_goods'] ?? 0 }}"
        icon='<svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="currentColor" class="bi bi-send-check" viewBox="0 0 16 16"><path d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855a.75.75 0 0 0-.124 1.329l4.995 3.178 1.531 2.406a.5.5 0 0 0 .844-.026l1.98-3.3 4.97 3.161a.75.75 0 0 0 1.149-.594L15.964.686z"/></svg>'
    />

    <!-- Card 4: Bill Customers -->
    <x-portal-card
        title="Bill Customers"
        badge="Cashier"
        description="Generate GST invoices, receive customer payments and manage billing operations."
        theme="amber"
        :isComingSoon="true"
        actionCount="{{ $b['bill_customers'] ?? 0 }}"
        icon='<svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="currentColor" class="bi bi-receipt" viewBox="0 0 16 16"><path d="M1.92.5a.5.5 0 0 1 .5.5v13.793l1.146-1.147a.5.5 0 0 1 .708 0L5.414 14.793l1.146-1.147a.5.5 0 0 1 .708 0L8.414 14.793l1.146-1.147a.5.5 0 0 1 .708 0L11.414 14.793l1.146-1.147a.5.5 0 0 1 .708 0L14.414 14.793V1a.5.5 0 0 1 1 0v14.5a.5.5 0 0 1-.854.354L13.5 14.707l-1.146 1.147a.5.5 0 0 1-.708 0L10.5 14.707l-1.146 1.147a.5.5 0 0 1-.708 0L7.5 14.707l-1.146 1.147a.5.5 0 0 1-.708 0L4.5 14.707l-1.146 1.147a.5.5 0 0 1-.854-.354V1a.5.5 0 0 1 .5-.5z"/></svg>'
    />

    <!-- Card 5: Order Supplies -->
    <x-portal-card
        title="Order Supplies"
        badge="PROCUREMENT"
        description="Manage suppliers, purchase requisitions, RFQs, purchase orders, goods receipts, and procurement operations."
        theme="teal"
        route="{{ route('procurement.dashboard') }}"
        actionCount="{{ $b['order_supplies'] ?? 0 }}"
        icon='<svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="currentColor" class="bi bi-cart-plus" viewBox="0 0 16 16"><path d="M9 5.5a.5.5 0 0 0-1 0V7H6.5a.5.5 0 0 0 0 1H8v1.5a.5.5 0 0 0 1 0V8h1.5a.5.5 0 0 0 0-1H9V5.5z"/><path d="M.5 1a.5.5 0 0 0 0 1h1.11l.401 1.607 1.498 7.985A.5.5 0 0 0 4 12h1a2 2 0 1 0 0 4 2 2 0 0 0 0-4h7a2 2 0 1 0 0 4 2 2 0 0 0 0-4h1a.5.5 0 0 0 .491-.408l1.5-8A.5.5 0 0 0 14.5 3H3.89l-.371-1.485A.5.5 0 0 0 3 1H.5z"/></svg>'
    />

    <!-- Card 6: Transport Dept -->
    <x-portal-card
        title="Transport Dept"
        badge="Logistics"
        description="Assign vehicles, manage delivery routes and coordinate transport operations."
        theme="rose"
        route="{{ route('transport.index') }}"
        actionCount="{{ $b['transport'] ?? 0 }}"
        icon='<svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="currentColor" class="bi bi-truck" viewBox="0 0 16 16"><path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-4 0H.5A1.5 1.5 0 0 1 0 10.5v-7zm1 0v7a.5.5 0 0 1 .5.5h.582a2 2 0 0 1 3.836 0h4.164a2 2 0 0 1 3.836 0h.582a.5.5 0 0 0 .5-.5V8.35a.5.5 0 0 0-.11-.312l-1.48-1.85A.5.5 0 0 0 12 6H11v-2.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5z"/></svg>'
    />

    <!-- Card 7: Driver Terminal -->
    <x-portal-card
        title="Driver Terminal"
        badge="Mobile"
        description="Delivery checkpoints, trip execution, status updates, and completed delivery history."
        theme="cyan"
        route="{{ route('driver-terminal.login') }}"
        actionCount="{{ $b['driver_terminal'] ?? 0 }}"
        icon='<svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="currentColor" class="bi bi-phone" viewBox="0 0 16 16"><path d="M11 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h6zM5 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H5z"/><path d="M8 14a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/></svg>'
    />

    <!-- Card 8: Admin Center -->
    <x-portal-card
        title="Admin Center"
        badge="Executive"
        description="Analytics, Finance, Audit Logs, User Management, Approvals and System Administration."
        theme="purple"
        route="{{ route('settings.index') }}"
        actionCount="{{ $b['admin'] ?? 0 }}"
        icon='<svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="currentColor" class="bi bi-shield-lock" viewBox="0 0 16 16"><path d="M5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.8 11.8 0 0 1-2.517 2.453 7 7 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7 7 0 0 1-1.048-.625 11.8 11.8 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 63 63 0 0 1 5.072.56z"/></svg>'
    />

    <!-- Card 9: Sales & CRM -->
    <x-portal-card
        title="Sales & CRM"
        badge="B2B SALES"
        description="Manage customers, customer groups, categories, territories, salespersons, credit profiles, and customer relationships."
        theme="orange"
        route="{{ route('sales.dashboard') }}"
        actionCount="{{ $b['sales_crm'] ?? 0 }}"
        icon='<svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="currentColor" class="bi bi-graph-up-arrow" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M0 0h1v15h15v1H0zm10 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-1 0V4.707l-4.146 4.147a.5.5 0 0 1-.708 0L7 6.707l-2.646 2.647a.5.5 0 0 1-.708-.708l3-3a.5.5 0 0 1 .708 0L9.5 7.293l3.646-3.647H10.5a.5.5 0 0 1-.5-.5z"/></svg>'
    />

</div>

<!-- Refined Footer -->
<footer class="mt-5 pt-3 pb-4 border-top border-translucent">
    <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 text-muted small">
        <div class="d-flex align-items-center gap-3">
            <span><strong>Version</strong> v3.5.0</span>
            <span>•</span>
            <span><strong>Enterprise Edition</strong></span>
        </div>
        <div>
            Operational Status: <span class="text-success fw-semibold">🟢 Online</span>
        </div>
    </div>
</footer>

<!-- System Guide Modal -->
<div class="modal fade" id="systemGuideModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-translucent shadow-lg rounded-4">
            <div class="modal-header border-bottom border-translucent p-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="p-2 bg-primary-subtle text-primary rounded-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-book" viewBox="0 0 16 16">
                            <path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811V2.828zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492V2.687zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.334-.896 3.896-1.058 1.405-.145 2.805.04 3.397.53.592-.49 1.992-.675 3.397-.53 1.562.162 3.014.658 3.896 1.058A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783z"/>
                        </svg>
                    </div>
                    <h5 class="modal-title fw-bold text-body mb-0">StockManager Enterprise ERP System Guide</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <h6 class="fw-bold text-body mb-3">Operational Workflow Architecture</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="p-3 bg-body-tertiary rounded-3 border border-translucent h-100">
                            <span class="badge bg-primary text-white mb-2">Step 1</span>
                            <h6 class="fw-bold text-body mb-1">Manage Stock</h6>
                            <p class="small text-muted mb-0">Master Data setup: Categories, Brands, UOMs, GST Tax rates, and Product Attributes.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-body-tertiary rounded-3 border border-translucent h-100">
                            <span class="badge bg-info text-white mb-2">Step 2</span>
                            <h6 class="fw-bold text-body mb-1">Warehouse Ops</h6>
                            <p class="small text-muted mb-0">Multi-warehouse stock receiving, bin placement, and internal transfers.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-body-tertiary rounded-3 border border-translucent h-100">
                            <span class="badge bg-success text-white mb-2">Step 3</span>
                            <h6 class="fw-bold text-body mb-1">Business Ops</h6>
                            <p class="small text-muted mb-0">B2B sales orders, dispatch fulfillment, customer invoicing, and logistics tracking.</p>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info border-info-subtle bg-info-subtle text-info p-3 rounded-3 small mb-0">
                    ℹ️ Select any portal card from the Unified Control Center to begin executing operations.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
