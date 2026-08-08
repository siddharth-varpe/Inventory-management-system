<div class="card p-3 rounded-4 shadow-sm border-translucent bg-body">
    <div class="d-flex align-items-center gap-2 mb-3 px-2 border-bottom pb-2">
        <div class="p-2 bg-primary-subtle text-primary rounded-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-box-seam-fill" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M15.528 3.673a1 1 0 0 1 .472.827v8a1 1 0 0 1-.472.827l-7 4a1 1 0 0 1-.944 0l-7-4A1 1 0 0 1 0 12.5v-8a1 1 0 0 1 .472-.827l7-4a1 1 0 0 1 .944 0l7 4zm-7.5 4.97v6.626l6-3.428V5.378l-6 3.265zM2.5 5.378v6.463l6 3.428V8.643l-6-3.265zM8 1.733 3.327 4.4 8 6.947l4.673-2.547L8 1.733z"/></svg>
        </div>
        <div>
            <h6 class="fw-bold text-body mb-0">Organize Stock</h6>
            <span class="text-muted small">WMS Operational Workspace</span>
        </div>
    </div>

    <div class="nav flex-column nav-pills gap-1">
        <a href="{{ route('organize.dashboard') }}" class="nav-link rounded-3 fw-semibold d-flex align-items-center justify-content-between {{ request()->routeIs('organize.dashboard') ? 'active' : '' }}">
            <span>🏠 Workspace</span>
        </a>

        <a href="{{ route('organize.fulfillment.index') }}" class="nav-link rounded-3 fw-semibold d-flex align-items-center justify-content-between {{ request()->routeIs('organize.fulfillment.*') ? 'active' : '' }}">
            <span>📦 Pick & Pack Station</span>
            <span class="badge bg-warning text-dark font-monospace" style="font-size: 0.65rem;">LIVE</span>
        </a>

        <a href="{{ route('organize.putaway.index') }}" class="nav-link rounded-3 fw-semibold d-flex align-items-center justify-content-between {{ request()->routeIs('organize.putaway.*') ? 'active' : '' }}">
            <span>📥 Put-Away Tasks</span>
        </a>

        <a href="{{ route('organize.locations.index') }}" class="nav-link rounded-3 fw-semibold d-flex align-items-center justify-content-between {{ request()->routeIs('organize.locations.*') ? 'active' : '' }}">
            <span>🗺️ Warehouse Explorer</span>
        </a>

        <a href="{{ route('organize.transfers.index') }}" class="nav-link rounded-3 fw-semibold d-flex align-items-center justify-content-between {{ request()->routeIs('organize.transfers.*') ? 'active' : '' }}">
            <span>🔄 Transfer Center</span>
        </a>

        <a href="{{ route('organize.exceptions.index') }}" class="nav-link rounded-3 fw-semibold d-flex align-items-center justify-content-between {{ request()->routeIs('organize.exceptions.*') ? 'active' : '' }}">
            <span>⚠️ Exception Center</span>
        </a>

        <a href="{{ route('organize.reports.index') }}" class="nav-link rounded-3 fw-semibold d-flex align-items-center justify-content-between {{ request()->routeIs('organize.reports.*') ? 'active' : '' }}">
            <span>📊 Operational Reports</span>
        </a>
    </div>
</div>
