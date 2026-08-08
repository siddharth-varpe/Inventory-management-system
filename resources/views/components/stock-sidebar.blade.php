<!-- Manage Stock Portal Vertical Side Panel -->
<div class="card p-3 rounded-4 shadow-sm border-translucent bg-body sticky-top" style="top: 80px; z-index: 10;">
    <div class="d-flex align-items-center gap-2 mb-3 pb-3 border-bottom border-translucent px-2">
        <div class="p-2 bg-primary text-white rounded-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-box-seam" viewBox="0 0 16 16">
                <path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2zm3.564 1.426L5.596 5 8 5.961 14.154 3.5zm3.25 1.7-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.838L1 4.239v7.923l6.5 2.6zM7.443.184a1.5 1.5 0 0 1 1.114 0l7.129 2.852A.5.5 0 0 1 16 3.5v8.662a1.5 1.5 0 0 1-.901 1.37l-7 2.8a1.5 1.5 0 0 1-1.198 0l-7-2.8A1.5 1.5 0 0 1 0 12.162V3.5a.5.5 0 0 1 .314-.464L7.443.184z"/>
            </svg>
        </div>
        <div>
            <h6 class="fw-bold mb-0 text-body">Manage Stock Portal</h6>
            <span class="text-muted small" style="font-size: 0.75rem;">Inventory Execution</span>
        </div>
    </div>

    <!-- Prominent Add New Product Action Button -->
    <a href="{{ route('products.create') }}" class="btn btn-primary rounded-3 w-100 py-2 mb-3 fw-bold d-flex align-items-center justify-content-center gap-2 shadow-sm">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-plus-circle-fill" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3z"/></svg>
        <span>+ Add New Product</span>
    </a>

    <!-- Vertical Navigation Options -->
    <div class="nav flex-column nav-pills gap-1">
        <!-- 1. Product Catalog -->
        <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.index') || request()->routeIs('stock.catalog') ? 'active' : '' }} d-flex align-items-center gap-2 rounded-3 py-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-box-seam-fill" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M15.528 3.673a.5.5 0 0 1 .472.527v8.6a.5.5 0 0 1-.472.527l-6.75 1.5a.5.5 0 0 1-.556-.246l-4.5-7.5a.5.5 0 0 1 .139-.68l6.75-4.5a.5.5 0 0 1 .667.132l4.25 6.375zM8.5 4.5a.5.5 0 0 0-1 0v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 0-1H9V4.5z"/></svg>
            <span>Product Catalog</span>
        </a>

        <!-- 2. Receive Stock -->
        <a href="{{ route('stock.receive.index') }}" class="nav-link {{ request()->routeIs('stock.receive.*') ? 'active' : '' }} d-flex align-items-center gap-2 rounded-3 py-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-box-arrow-in-down" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M3.5 6a.5.5 0 0 0-.5.5v8a.5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5v-8a.5.5 0 0 0-.5-.5h-2a.5.5 0 0 1 0-1h2A1.5 1.5 0 0 1 14 6.5v8a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 14.5v-8A1.5 1.5 0 0 1 3.5 5h2a.5.5 0 0 1 0 1z"/><path fill-rule="evenodd" d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/></svg>
            <span>Receive Stock</span>
        </a>

        <!-- 3. Opening Stock -->
        <a href="{{ route('stock.opening-stock.index') }}" class="nav-link {{ request()->routeIs('stock.opening-stock.*') ? 'active' : '' }} d-flex align-items-center gap-2 rounded-3 py-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-archive-fill" viewBox="0 0 16 16"><path d="M12.643 15C13.979 15 15 13.845 15 12.5V5H1v7.5C1 13.845 2.021 15 3.357 15zM5.5 7h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1 0-1M.8 1a.8.8 0 0 0-.8.8V3a.8.8 0 0 0 .8.8h14.4A.8.8 0 0 0 16 3V1.8a.8.8 0 0 0-.8-.8z"/></svg>
            <span>Opening Stock</span>
        </a>

        <!-- 4. Stock Adjustment -->
        <a href="{{ route('stock.adjustments.index') }}" class="nav-link {{ request()->routeIs('stock.adjustments.*') ? 'active' : '' }} d-flex align-items-center gap-2 rounded-3 py-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-sliders" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M11.5 2a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M9.05 3a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0V3zM4.5 7a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M2.05 8a2.5 2.5 0 0 1 4.9 0H16v1H6.95a2.5 2.5 0 0 1-4.9 0H0V8zM11.5 12a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3m-2.45 1a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0v-1z"/></svg>
            <span>Stock Adjustment</span>
        </a>

        <!-- 5. Barcode Center -->
        <a href="{{ route('stock.barcodes.index') }}" class="nav-link {{ request()->routeIs('stock.barcodes.*') ? 'active' : '' }} d-flex align-items-center gap-2 rounded-3 py-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-qr-code-scan" viewBox="0 0 16 16"><path d="M0.5 0.5A.5.5 0 0 0 0 1v3.5a.5.5 0 0 0 1 0V1h3.5a.5.5 0 0 0 0-1zm15 0A.5.5 0 0 0 15 1v3.5a.5.5 0 0 0 1 0V1h-3.5a.5.5 0 0 0 0-1zM0.5 15.5A.5.5 0 0 1 0 15v-3.5a.5.5 0 0 1 1 0V15h3.5a.5.5 0 0 1 0 1zm15 0A.5.5 0 0 1 15 15v-3.5a.5.5 0 0 1 1 0V15h-3.5a.5.5 0 0 1 0 1z"/></svg>
            <span>Barcodes</span>
        </a>

        <!-- 6. Import & Export -->
        <a href="{{ route('stock.import-export.index') }}" class="nav-link {{ request()->routeIs('stock.import-export.*') ? 'active' : '' }} d-flex align-items-center gap-2 rounded-3 py-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-file-earmark-arrow-up" viewBox="0 0 16 16"><path d="M8.5 11.5a.5.5 0 0 1-1 0V7.707L6.354 8.854a.5.5 0 1 1-.708-.708l2-2a.5.5 0 0 1 .708 0l2 2a.5.5 0 0 1-.708.708L8.5 7.707z"/><path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"/></svg>
            <span>Import & Export</span>
        </a>

        <!-- 7. Inventory Reports -->
        <a href="{{ route('stock.reports.index') }}" class="nav-link {{ request()->routeIs('stock.reports.*') ? 'active' : '' }} d-flex align-items-center gap-2 rounded-3 py-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-graph-up" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M0 0h1v15h15v1H0zm14.817 3.113a.5.5 0 0 1 .07.704l-4.5 5.5a.5.5 0 0 1-.74.037L7.06 6.767l-3.656 5.027a.5.5 0 0 1-.808-.588l4-5.5a.5.5 0 0 1 .758-.06l2.609 2.61 4.15-5.073a.5.5 0 0 1 .704-.07"/></svg>
            <span>Reports</span>
        </a>
    </div>
</div>
