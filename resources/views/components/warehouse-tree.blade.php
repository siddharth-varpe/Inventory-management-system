@props([
    'warehouses' => [],
])

<div class="row g-4" id="warehouseExplorerApp">
    <!-- LEFT PANEL: Warehouse Navigation Directory (35% Width on Desktop) -->
    <div class="col-12 col-lg-4 col-xl-4">
        <div class="card p-3 rounded-4 shadow-sm border-translucent bg-body h-100">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <div>
                    <h6 class="fw-bold text-body mb-0">Facilities Directory</h6>
                    <span class="text-muted small">Select facility to inspect structure</span>
                </div>
                <span class="badge bg-primary-subtle text-primary rounded-pill px-2.5 py-1 fw-bold">{{ count($warehouses) }} Facilities</span>
            </div>

            <!-- Warehouse Search Bar -->
            <div class="mb-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-body-tertiary border-end-0 rounded-start-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-search text-muted" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/></svg>
                    </span>
                    <input type="text" class="form-control form-control-sm border-start-0 rounded-end-3" id="whDirectorySearch" placeholder="Search facilities..." onkeyup="filterWarehouseDirectory(this.value)">
                </div>
            </div>

            <!-- Warehouse Selectable List Cards -->
            <div class="overflow-auto pe-1" style="max-height: 680px;" id="whListContainer">
                @foreach($warehouses as $index => $wh)
                    @php
                        $activeClass = $index === 0 ? 'border-primary bg-primary-subtle border-2' : 'border-translucent bg-body hover-shadow';
                        $activeAttr = $index === 0 ? 'data-active="true"' : 'data-active="false"';
                        $totalBins = 0;
                        $totalRacks = 0;
                        foreach($wh->zones as $z) {
                            foreach($z->aisles as $a) {
                                $totalRacks += count($a->racks);
                                foreach($a->racks as $r) {
                                    $totalBins += count($r->bins);
                                }
                            }
                        }
                    @endphp
                    <div class="wh-nav-card card p-3 rounded-4 mb-2 cursor-pointer transition-all {{ $activeClass }}" 
                         id="wh-nav-{{ $wh->id }}" 
                         onclick="selectWarehouse({{ $wh->id }})"
                         {{ $activeAttr }}>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="fs-5">🏭</span>
                                <div>
                                    <h6 class="fw-bold text-body mb-0 wh-title">{{ $wh->name }}</h6>
                                    <code class="small text-muted">{{ $wh->code }}</code>
                                </div>
                            </div>
                            <span class="badge bg-body text-body border small">{{ ucfirst(str_replace('_', ' ', $wh->type ?? 'Facility')) }}</span>
                        </div>

                        <!-- Occupancy Progress Bar -->
                        <div class="mb-2">
                            <div class="d-flex justify-content-between align-items-center small text-muted mb-1" style="font-size: 0.75rem;">
                                <span>Occupancy</span>
                                <span class="fw-bold {{ $wh->occupancy_percentage >= 80 ? 'text-danger' : ($wh->occupancy_percentage >= 50 ? 'text-warning-emphasis' : 'text-success') }}">
                                    {{ $wh->occupancy_percentage }}%
                                </span>
                            </div>
                            <div class="progress rounded-pill" style="height: 6px;">
                                <div class="progress-bar {{ $wh->occupancy_percentage >= 80 ? 'bg-danger' : ($wh->occupancy_percentage >= 50 ? 'bg-warning' : 'bg-success') }}" role="progressbar" style="width: {{ min($wh->occupancy_percentage, 100) }}%;"></div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between text-muted small" style="font-size: 0.78rem;">
                            <span>📍 {{ count($wh->zones) }} Zones</span>
                            <span>📦 {{ $totalRacks }} Racks</span>
                            <span>🏷️ {{ $totalBins }} Bins</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- RIGHT PANEL: Active Warehouse Structure Workspace (65% Width on Desktop) -->
    <div class="col-12 col-lg-8 col-xl-8">
        @foreach($warehouses as $index => $wh)
            @php
                $displayStyle = $index === 0 ? '' : 'display: none;';
                $totalBins = 0;
                $occupiedBins = 0;
                $totalRacks = 0;
                $totalAisles = 0;
                foreach($wh->zones as $z) {
                    $totalAisles += count($z->aisles);
                    foreach($z->aisles as $a) {
                        $totalRacks += count($a->racks);
                        foreach($a->racks as $r) {
                            foreach($r->bins as $b) {
                                $totalBins++;
                                if(($b->current_occupied_qty ?? 0) > 0) {
                                    $occupiedBins++;
                                }
                            }
                        }
                    }
                }
            @endphp
            <div class="wh-workspace-panel" id="wh-workspace-{{ $wh->id }}" style="{{ $displayStyle }}">
                <!-- Warehouse Workspace Header -->
                <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body mb-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="p-3 bg-primary-subtle text-primary rounded-4 fs-3">🏢</div>
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <h4 class="fw-bold text-body mb-0">{{ $wh->name }}</h4>
                                    <span class="badge bg-success-subtle text-success rounded-pill">Active WMS Facility</span>
                                </div>
                                <div class="text-muted small mt-1">
                                    <span class="me-3">Code: <code>{{ $wh->code }}</code></span>
                                    <span class="me-3">Type: <strong>{{ ucfirst(str_replace('_', ' ', $wh->type ?? 'Facility')) }}</strong></span>
                                    <span>Location: <strong>{{ $wh->city ?? 'Central Hub' }}</strong></span>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Action Buttons -->
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-primary rounded-3 fw-bold" data-bs-toggle="modal" data-bs-target="#newWarehouseModal">+ Create Warehouse</button>
                            <button class="btn btn-sm btn-outline-primary rounded-3 fw-bold" data-bs-toggle="modal" data-bs-target="#newBinModal">+ Add Storage Bin</button>
                        </div>
                    </div>

                    <!-- Quick Metrics Bar -->
                    <div class="row g-3 pt-3 border-top">
                        <div class="col-6 col-sm-3">
                            <div class="p-2.5 rounded-3 bg-body-tertiary border text-center">
                                <div class="text-muted small">Total Capacity</div>
                                <div class="fw-bold fs-6 text-body">{{ number_format($wh->total_capacity) }} {{ $wh->capacity_unit ?? 'units' }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-3">
                            <div class="p-2.5 rounded-3 bg-body-tertiary border text-center">
                                <div class="text-muted small">Occupancy Rate</div>
                                <div class="fw-bold fs-6 {{ $wh->occupancy_percentage >= 80 ? 'text-danger' : 'text-success' }}">{{ $wh->occupancy_percentage }}%</div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-3">
                            <div class="p-2.5 rounded-3 bg-body-tertiary border text-center">
                                <div class="text-muted small">Infrastructure</div>
                                <div class="fw-bold fs-6 text-body">{{ count($wh->zones) }} Zones / {{ $totalRacks }} Racks</div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-3">
                            <div class="p-2.5 rounded-3 bg-body-tertiary border text-center">
                                <div class="text-muted small">Active Bins</div>
                                <div class="fw-bold fs-6 text-primary">{{ $occupiedBins }} / {{ $totalBins }} Occupied</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Instant Search & Hierarchy Filter Bar -->
                <div class="card p-3 rounded-4 shadow-sm border-translucent bg-body mb-4">
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-md-7">
                            <div class="input-group">
                                <span class="input-group-text bg-body-tertiary border-end-0 rounded-start-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search text-muted" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/></svg>
                                </span>
                                <input type="text" class="form-control border-start-0 rounded-end-3" placeholder="Search Zone, Aisle, Rack, Bin, Location Code..." onkeyup="filterWarehouseTreeHierarchy({{ $wh->id }}, this.value)">
                            </div>
                        </div>
                        <div class="col-12 col-md-5 d-flex gap-1 justify-content-md-end">
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-3 active fw-semibold" onclick="filterBinStatus({{ $wh->id }}, 'all', this)">All Bins</button>
                            <button type="button" class="btn btn-sm btn-outline-success rounded-3 fw-semibold" onclick="filterBinStatus({{ $wh->id }}, 'available', this)">Available</button>
                            <button type="button" class="btn btn-sm btn-outline-warning rounded-3 fw-semibold" onclick="filterBinStatus({{ $wh->id }}, 'partial', this)">Partial</button>
                            <button type="button" class="btn btn-sm btn-outline-danger rounded-3 fw-semibold" onclick="filterBinStatus({{ $wh->id }}, 'full', this)">Full</button>
                        </div>
                    </div>
                </div>

                <!-- Progressive Drill-Down Hierarchy Tree -->
                <div class="wh-tree-hierarchy" id="tree-hierarchy-{{ $wh->id }}">
                    @if(count($wh->zones) === 0)
                        <div class="card p-5 text-center rounded-4 border-translucent bg-body">
                            <div class="fs-1 mb-2">📍</div>
                            <h6 class="fw-bold text-body">No Storage Zones Registered</h6>
                            <p class="text-muted small">Create storage zones to organize aisles, racks, and storage bins for this facility.</p>
                            <div>
                                <button class="btn btn-sm btn-primary rounded-3 fw-bold" data-bs-toggle="modal" data-bs-target="#newWarehouseModal">+ Configure Zones</button>
                            </div>
                        </div>
                    @else
                        @foreach($wh->zones as $zIdx => $zone)
                            <!-- ZONE LEVEL CARD -->
                            <div class="card rounded-4 border-translucent shadow-xs bg-body mb-3 zone-node-card" data-zone-id="{{ $zone->id }}">
                                <div class="card-header bg-body p-3 border-0 d-flex align-items-center justify-content-between cursor-pointer" onclick="toggleHierarchyCollapse('zone-collapse-{{ $zone->id }}')">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="p-2 bg-primary-subtle text-primary rounded-3 fw-bold">📍</div>
                                        <div>
                                            <div class="d-flex align-items-center gap-2">
                                                <h6 class="fw-bold text-body mb-0">{{ $zone->name }}</h6>
                                                <code class="small text-muted">({{ $zone->code }})</code>
                                                <span class="badge bg-secondary-subtle text-secondary small">{{ ucfirst($zone->type ?? 'Storage') }}</span>
                                            </div>
                                            <div class="text-muted small" style="font-size: 0.78rem;">
                                                <span>{{ count($zone->aisles) }} Aisles</span> • <span>Capacity: {{ number_format($zone->capacity ?? 0) }} units</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                                        <button type="button" class="btn btn-sm btn-light border-0 rounded-circle p-1 rotate-chevron" id="chevron-zone-collapse-{{ $zone->id }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-down" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z"/></svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- AISLES CONTAINER -->
                                <div class="collapse p-3 pt-0 border-top border-translucent bg-body-tertiary rounded-bottom-4" id="zone-collapse-{{ $zone->id }}">
                                    <div class="ps-3 border-start border-2 border-primary-subtle mt-3">
                                        @foreach($zone->aisles as $aisle)
                                            <!-- AISLE LEVEL -->
                                            <div class="card rounded-3 border-translucent shadow-xs bg-body mb-2 aisle-node-card">
                                                <div class="card-header bg-body p-2.5 border-0 d-flex align-items-center justify-content-between cursor-pointer" onclick="toggleHierarchyCollapse('aisle-collapse-{{ $aisle->id }}')">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span>🚪</span>
                                                        <strong class="text-body small">Aisle: {{ $aisle->name }}</strong>
                                                        <code class="small text-muted">({{ $aisle->code }})</code>
                                                        <span class="badge bg-light text-dark border small">{{ count($aisle->racks) }} Racks</span>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-light border-0 rounded-circle p-1 rotate-chevron" id="chevron-aisle-collapse-{{ $aisle->id }}">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-chevron-down" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z"/></svg>
                                                    </button>
                                                </div>

                                                <!-- RACKS CONTAINER -->
                                                <div class="collapse p-3 pt-0 border-top border-translucent bg-body" id="aisle-collapse-{{ $aisle->id }}">
                                                    <div class="row g-3 mt-1">
                                                        @foreach($aisle->racks as $rack)
                                                            @php
                                                                $rackBinCount = count($rack->bins);
                                                                $rackOccupiedCount = 0;
                                                                foreach($rack->bins as $rb) {
                                                                    if(($rb->current_occupied_qty ?? 0) > 0) $rackOccupiedCount++;
                                                                }
                                                                $rackOccupancy = $rackBinCount > 0 ? round(($rackOccupiedCount / $rackBinCount) * 100) : 0;
                                                            @endphp
                                                            <!-- RACK CARD (Compact Modern Card) -->
                                                            <div class="col-12 col-md-6 rack-node-card">
                                                                <div class="card p-3 rounded-3 border-translucent bg-body hover-shadow transition-all">
                                                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                                                        <div class="d-flex align-items-center gap-2">
                                                                            <span class="fs-6">📦</span>
                                                                            <strong class="text-body small">Rack {{ $rack->name }}</strong>
                                                                            <code class="small text-muted">{{ $rack->code }}</code>
                                                                        </div>
                                                                        <span class="badge {{ $rackOccupancy >= 80 ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }} small">
                                                                            {{ $rackOccupancy }}% Occupied
                                                                        </span>
                                                                    </div>

                                                                    <div class="text-muted small mb-2 d-flex justify-content-between" style="font-size: 0.75rem;">
                                                                        <span>Shelves: {{ $rack->total_shelves }}</span>
                                                                        <span>Binned: {{ $rackOccupiedCount }} / {{ $rackBinCount }}</span>
                                                                    </div>

                                                                    <!-- VISUAL BIN CHIPS MATRIX -->
                                                                    <div class="pt-2 border-top">
                                                                        <div class="text-muted small mb-1.5 fw-semibold" style="font-size: 0.7rem;">STORAGE BINS VISUAL MATRIX:</div>
                                                                        @if($rackBinCount === 0)
                                                                            <span class="text-muted small" style="font-size: 0.75rem;">No bins assigned to this rack.</span>
                                                                        @else
                                                                            <div class="d-flex flex-wrap gap-1.5">
                                                                                @foreach($rack->bins as $bin)
                                                                                    @php
                                                                                        $qty = $bin->current_occupied_qty ?? 0;
                                                                                        if ($bin->status === 'inactive' || $bin->status === 'disabled') {
                                                                                            $binChipClass = 'bg-secondary-subtle text-secondary border-secondary-subtle';
                                                                                            $binStatusLabel = 'Disabled';
                                                                                            $binFilterType = 'disabled';
                                                                                        } elseif ($qty >= 50) {
                                                                                            $binChipClass = 'bg-danger-subtle text-danger border-danger-subtle';
                                                                                            $binStatusLabel = 'Full';
                                                                                            $binFilterType = 'full';
                                                                                        } elseif ($qty > 0) {
                                                                                            $binChipClass = 'bg-warning-subtle text-warning-emphasis border-warning-subtle';
                                                                                            $binStatusLabel = 'Partial';
                                                                                            $binFilterType = 'partial';
                                                                                        } else {
                                                                                            $binChipClass = 'bg-success-subtle text-success border-success-subtle';
                                                                                            $binStatusLabel = 'Available';
                                                                                            $binFilterType = 'available';
                                                                                        }
                                                                                    @endphp
                                                                                    <!-- BIN CHIP BUTTON -->
                                                                                    <button type="button" 
                                                                                            class="bin-chip btn btn-xs border rounded-3 font-monospace px-2 py-0.5 fw-bold shadow-2xs {{ $binChipClass }}" 
                                                                                            data-bin-type="{{ $binFilterType }}"
                                                                                            data-bs-toggle="modal" 
                                                                                            data-bs-target="#binDetailModal{{ $bin->id }}"
                                                                                            title="Bin {{ $bin->location_code }} (Qty: {{ $qty }})">
                                                                                        {{ $bin->bin_number ?: $bin->location_code }}
                                                                                    </button>

                                                                                    <!-- BIN DETAIL QUICK MODAL -->
                                                                                    <div class="modal fade" id="binDetailModal{{ $bin->id }}" tabindex="-1" aria-hidden="true">
                                                                                        <div class="modal-dialog modal-dialog-centered modal-sm">
                                                                                            <div class="modal-content rounded-4 p-2 text-start">
                                                                                                <div class="modal-header border-0 pb-1">
                                                                                                    <h6 class="modal-title fw-bold">Bin {{ $bin->location_code }}</h6>
                                                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                                                </div>
                                                                                                <div class="modal-body py-2">
                                                                                                    <div class="p-2.5 rounded-3 bg-body-tertiary border mb-3">
                                                                                                        <div class="d-flex justify-content-between small text-muted">
                                                                                                            <span>Occupied Qty:</span>
                                                                                                            <strong class="text-body">{{ $qty }} units</strong>
                                                                                                        </div>
                                                                                                        <div class="d-flex justify-content-between small text-muted">
                                                                                                            <span>Shelf:</span>
                                                                                                            <strong class="text-body">{{ $bin->shelf_number }}</strong>
                                                                                                        </div>
                                                                                                        <div class="d-flex justify-content-between small text-muted">
                                                                                                            <span>Status:</span>
                                                                                                            <span class="badge {{ $binChipClass }}">{{ $binStatusLabel }}</span>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                    <div class="d-grid gap-1">
                                                                                                        <a href="{{ route('organize.putaway.index') }}" class="btn btn-primary btn-sm rounded-3 fw-semibold">Execute Put-Away to Bin</a>
                                                                                                        <a href="{{ route('organize.transfers.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">Transfer Bin Stock</a>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                @endforeach
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>

<script>
function selectWarehouse(whId) {
    const navCards = document.querySelectorAll('.wh-nav-card');
    navCards.forEach(card => {
        card.classList.remove('border-primary', 'bg-primary-subtle', 'border-2');
        card.classList.add('border-translucent', 'bg-body', 'hover-shadow');
        card.setAttribute('data-active', 'false');
    });

    const activeNav = document.getElementById('wh-nav-' + whId);
    if (activeNav) {
        activeNav.classList.remove('border-translucent', 'bg-body', 'hover-shadow');
        activeNav.classList.add('border-primary', 'bg-primary-subtle', 'border-2');
        activeNav.setAttribute('data-active', 'true');
    }

    const panels = document.querySelectorAll('.wh-workspace-panel');
    panels.forEach(panel => panel.style.display = 'none');

    const activePanel = document.getElementById('wh-workspace-' + whId);
    if (activePanel) {
        activePanel.style.display = 'block';
    }
}

function toggleHierarchyCollapse(collapseId) {
    const el = document.getElementById(collapseId);
    if (!el) return;
    const bsCollapse = bootstrap.Collapse.getOrCreateInstance(el, { toggle: false });
    bsCollapse.toggle();

    const chevron = document.getElementById('chevron-' + collapseId);
    if (chevron) {
        chevron.classList.toggle('rotate-180');
    }
}

function filterWarehouseDirectory(query) {
    const term = query.toLowerCase().trim();
    const navCards = document.querySelectorAll('.wh-nav-card');
    navCards.forEach(card => {
        const text = card.querySelector('.wh-title').textContent.toLowerCase();
        if (text.includes(term)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

function filterWarehouseTreeHierarchy(whId, query) {
    const term = query.toLowerCase().trim();
    const container = document.getElementById('tree-hierarchy-' + whId);
    if (!container) return;

    const zoneCards = container.querySelectorAll('.zone-node-card');
    zoneCards.forEach(zoneCard => {
        const text = zoneCard.textContent.toLowerCase();
        if (text.includes(term)) {
            zoneCard.style.display = 'block';
            if (term.length > 1) {
                const collapses = zoneCard.querySelectorAll('.collapse');
                collapses.forEach(col => col.classList.add('show'));
            }
        } else {
            zoneCard.style.display = 'none';
        }
    });
}

function filterBinStatus(whId, statusType, btnEl) {
    const container = document.getElementById('tree-hierarchy-' + whId);
    if (!container) return;

    const parentRow = btnEl.parentElement;
    parentRow.querySelectorAll('button').forEach(b => b.classList.remove('active', 'btn-secondary', 'btn-success', 'btn-warning', 'btn-danger'));
    btnEl.classList.add('active');

    const binChips = container.querySelectorAll('.bin-chip');
    binChips.forEach(chip => {
        const type = chip.getAttribute('data-bin-type');
        if (statusType === 'all' || type === statusType) {
            chip.style.display = 'inline-block';
        } else {
            chip.style.display = 'none';
        }
    });
}
</script>

<style>
.rotate-chevron {
    transition: transform 0.2s ease;
}
.rotate-chevron.rotate-180 {
    transform: rotate(180deg);
}
.cursor-pointer {
    cursor: pointer;
}
.bin-chip {
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.bin-chip:hover {
    transform: translateY(-1px);
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.08);
}
</style>
