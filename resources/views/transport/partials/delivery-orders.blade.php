<div>
    <!-- MAIN PAGE HEADER -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
        <div>
            <h3 class="fw-black text-body mb-0">Delivery Orders</h3>
            <p class="text-muted small mb-0 mt-1">Synchronized Sales Orders from CRM & Organize Stock</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 fw-bold font-monospace">
                Total Orders: {{ $statusCounts['all'] ?? $requests->total() }}
            </span>
            <button type="button" class="btn btn-sm btn-outline-secondary rounded-3 px-3 fw-bold d-flex align-items-center gap-1.5" onclick="refreshDeliveryOrders()">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/><path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/></svg> Refresh
            </button>
        </div>
    </div>

    <!-- STATUS SUMMARY FILTER CARDS (6 COLUMNS MATCHING SCREENSHOT) -->
    <div class="status-cards-grid mb-3">
        @php
            $currentStatusCard = request('status_card', 'all');
        @endphp
        <!-- ALL -->
        <a href="{{ route('transport.index', array_merge(request()->except('page', 'status_card'), ['tab' => 'delivery-orders', 'status_card' => 'all'])) }}" 
           class="card p-3 rounded-4 border text-decoration-none transition-all {{ $currentStatusCard === 'all' ? 'border-primary bg-primary-subtle shadow-sm' : 'border-translucent bg-body hover-bg-tertiary' }}">
            <div class="d-flex align-items-center justify-content-between mb-1">
                <div class="d-flex align-items-center gap-1.5 text-primary fw-bold small">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-seam" viewBox="0 0 16 16"><path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2l-2.218-.887zm3.564 1.426L5.596 5 8 5.961 14.154 3.5l-2.404-.961zm3.25 1.7-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.838L1 4.239v7.923l6.5 2.6z"/></svg>
                    <span>All</span>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <span class="fs-6 fw-bold text-primary font-monospace">{{ $statusCounts['all'] ?? 0 }}</span>
                    @if($currentStatusCard === 'all')
                        <span class="text-muted small ms-1">&times;</span>
                    @endif
                </div>
            </div>
        </a>

        <!-- READY -->
        <a href="{{ route('transport.index', array_merge(request()->except('page', 'status_card'), ['tab' => 'delivery-orders', 'status_card' => 'ready'])) }}" 
           class="card p-3 rounded-4 border text-decoration-none transition-all {{ $currentStatusCard === 'ready' ? 'border-success bg-success-subtle shadow-sm' : 'border-translucent bg-body hover-bg-tertiary' }}">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-1.5 text-success fw-bold small">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"/></svg>
                    <span>Ready</span>
                </div>
                <span class="fs-6 fw-bold text-success font-monospace">{{ $statusCounts['ready'] ?? 0 }}</span>
            </div>
        </a>

        <!-- ASSIGNED -->
        <a href="{{ route('transport.index', array_merge(request()->except('page', 'status_card'), ['tab' => 'delivery-orders', 'status_card' => 'assigned'])) }}" 
           class="card p-3 rounded-4 border text-decoration-none transition-all {{ $currentStatusCard === 'assigned' ? 'border-purple bg-purple-subtle shadow-sm' : 'border-translucent bg-body hover-bg-tertiary' }}">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-1.5 fw-bold small" style="color: #9333ea;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clock-history" viewBox="0 0 16 16"><path d="M8.515 1.019A7 7 0 0 0 8 1V0a8 8 0 0 1 .589.022l-.074.997zm2.004.45a7.003 7.003 0 0 0-.985-.299l.219-.976c.383.086.76.2 1.122.343l-.356.932zM8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/></svg>
                    <span>Assigned</span>
                </div>
                <span class="fs-6 fw-bold font-monospace" style="color: #9333ea;">{{ $statusCounts['assigned'] ?? 0 }}</span>
            </div>
        </a>

        <!-- ACTIVE -->
        <a href="{{ route('transport.index', array_merge(request()->except('page', 'status_card'), ['tab' => 'delivery-orders', 'status_card' => 'active'])) }}" 
           class="card p-3 rounded-4 border text-decoration-none transition-all {{ $currentStatusCard === 'active' ? 'border-warning bg-warning-subtle shadow-sm' : 'border-translucent bg-body hover-bg-tertiary' }}">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-1.5 text-warning fw-bold small">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-rocket-takeoff" viewBox="0 0 16 16"><path d="M9.752 6.193a.5.5 0 0 1 .1-.318l2.5-3.5a.5.5 0 0 1 .71-.107l.5.357a.5.5 0 0 1 .107.71l-2.5 3.5a.5.5 0 0 1-.717.107l-.5-.357a.5.5 0 0 1-.2-.492z"/></svg>
                    <span>Active</span>
                </div>
                <span class="fs-6 fw-bold text-warning font-monospace">{{ $statusCounts['active'] ?? 0 }}</span>
            </div>
        </a>

        <!-- COMPLETED -->
        <a href="{{ route('transport.index', array_merge(request()->except('page', 'status_card'), ['tab' => 'delivery-orders', 'status_card' => 'completed'])) }}" 
           class="card p-3 rounded-4 border text-decoration-none transition-all {{ $currentStatusCard === 'completed' ? 'border-info bg-info-subtle shadow-sm' : 'border-translucent bg-body hover-bg-tertiary' }}">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-1.5 text-info fw-bold small">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>
                    <span>Completed</span>
                </div>
                <span class="fs-6 fw-bold text-info font-monospace">{{ $statusCounts['completed'] ?? 0 }}</span>
            </div>
        </a>

        <!-- CANCELLED -->
        <a href="{{ route('transport.index', array_merge(request()->except('page', 'status_card'), ['tab' => 'delivery-orders', 'status_card' => 'cancelled'])) }}" 
           class="card p-3 rounded-4 border text-decoration-none transition-all {{ $currentStatusCard === 'cancelled' ? 'border-danger bg-danger-subtle shadow-sm' : 'border-translucent bg-body hover-bg-tertiary' }}">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-1.5 text-danger fw-bold small">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-dash-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="M4 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 4 8z"/></svg>
                    <span>Cancelled</span>
                </div>
                <span class="fs-6 fw-bold text-danger font-monospace">{{ $statusCounts['cancelled'] ?? 0 }}</span>
            </div>
        </a>
    </div>

    <!-- SEARCH & FILTER BAR (FULL WIDTH FLEX LAYOUT) -->
    <div class="card p-3 rounded-4 shadow-sm border-translucent bg-body mb-3">
        <form method="GET" action="{{ route('transport.index') }}" id="deliveryFilterForm" class="filter-bar-flex">
            <input type="hidden" name="tab" value="delivery-orders">
            @if(request('status_card'))
                <input type="hidden" name="status_card" value="{{ request('status_card') }}">
            @endif

            <!-- Search Field (Flex: 2) -->
            <div class="filter-search-wrap">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-body border-translucent text-muted">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/></svg>
                    </span>
                    <input type="text" name="search" class="form-control bg-body border-translucent" 
                           placeholder="Search Order ID, Customer, City..." 
                           value="{{ request('search') }}">
                </div>
            </div>

            <!-- Priority Dropdown (Flex: 1) -->
            <div class="filter-select-wrap">
                <select name="priority" class="form-select form-select-sm bg-body border-translucent" onchange="this.form.submit()">
                    <option value="all">-- Priority --</option>
                    <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent Priority</option>
                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High Priority</option>
                    <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>Normal Priority</option>
                </select>
            </div>

            <!-- City Dropdown (Flex: 1) -->
            <div class="filter-select-wrap">
                <select name="city" class="form-select form-select-sm bg-body border-translucent" onchange="this.form.submit()">
                    <option value="all">-- City --</option>
                    @foreach($availableCities as $c)
                        <option value="{{ $c }}" {{ request('city') == $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Action Buttons (Flex: Auto, Aligned Right) -->
            <div class="filter-actions-wrap">
                <button type="submit" class="btn btn-sm btn-primary fw-bold rounded-3 px-3.5 d-flex align-items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-funnel" viewBox="0 0 16 16"><path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5v-2zm1 .5v1.308l4.372 4.858A.5.5 0 0 1 7 8.5v5.306l2-.666V8.5a.5.5 0 0 1 .128-.334L13.5 3.308V2h-11z"/></svg>
                    <span>Filter</span>
                </button>
                @if(request('search') || request('priority') || request('city') || request('status_card'))
                    <a href="{{ route('transport.index', ['tab' => 'delivery-orders']) }}" class="btn btn-sm btn-outline-secondary rounded-3 px-3" title="Reset Filters">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- LOADING SKELETON CONTAINER (HIDDEN BY DEFAULT) -->
    <div id="deliveryCardsSkeleton" class="vstack gap-3 d-none mb-3">
        @for($i = 0; $i < 3; $i++)
            <div class="card p-3.5 rounded-4 shadow-sm border-translucent bg-body placeholder-glow">
                <div class="d-flex justify-content-between pb-3 border-bottom border-translucent">
                    <div>
                        <span class="placeholder col-4 rounded-3 py-2 mb-2"></span>
                        <span class="placeholder col-6 rounded-3 py-1"></span>
                    </div>
                    <span class="placeholder col-3 rounded-3 py-2"></span>
                </div>
                <div class="pt-3 d-flex justify-content-between">
                    <span class="placeholder col-8 rounded-3 py-2"></span>
                    <span class="placeholder col-2 rounded-3 py-2"></span>
                </div>
            </div>
        @endfor
    </div>

    <!-- DELIVERY CARDS CONTAINER (LAYOUT MATCHING SCREENSHOT EXACTLY) -->
    <div id="deliveryCardsContainer" class="vstack gap-3">
        @forelse($requests as $r)
            <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body delivery-order-card">
                
                <!-- SECTION 1: TOP HEADER SECTION (ANNOTATED ITEMS 1 - 8) -->
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 pb-3.5 border-bottom border-translucent">
                    <!-- LEFT SIDE: ITEM 1 (AVATAR), ITEM 2 (ORDER ID), ITEM 3 (TASK ID), ITEM 4 (STATUS BADGE), ITEM 5 (PRIORITY BADGE), ITEM 6 (SEAL BADGE) -->
                    <div class="d-flex align-items-center gap-3">
                        <!-- ITEM 1: CIRCLE TRUCK AVATAR (56px x 56px) -->
                        <div class="d-flex align-items-center justify-content-center flex-shrink-0 bg-primary-subtle rounded-circle" style="width: 56px; height: 56px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="currentColor" class="bi bi-truck text-primary" viewBox="0 0 16 16">
                                <path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-4 0H.5A.5.5 0 0 1 0 11.5v-8zM1 3.5v7h.5a2 2 0 0 1 3.163.787 2 2 0 0 1 3.674 0H10.5a2 2 0 0 1 3.163-.787H15V8.28l-1.48-1.85A.5.5 0 0 0 13.15 6H12v4.5a.5.5 0 0 1-1 0V3.5h-10z"/>
                            </svg>
                        </div>

                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                <!-- ITEM 2: ORDER REFERENCE (SO-2026-00001) -->
                                <a href="javascript:void(0)" onclick="openDeliveryOrderProfile({{ $r->id }})" class="fw-bold text-primary text-decoration-none fs-4 font-monospace">
                                    {{ $r->order_reference }}
                                </a>

                                <!-- ITEM 4: DRIVER & VEHICLE ASSIGNED STATUS BADGE -->
                                <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 small">
                                    ✓ {{ $r->status_label }}
                                </span>

                                <!-- ITEM 5: PRIORITY BADGE (NORMAL) -->
                                <span class="badge rounded-pill {{ $r->priority_badge_class }} px-2.5 py-1 small">
                                    {{ strtoupper($r->priority ?? 'NORMAL') }}
                                </span>
                            </div>

                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <!-- ITEM 3: TASK ID (TRN-2026-600001) -->
                                <span class="small text-muted font-monospace me-2" style="font-size: 0.8rem;">{{ $r->request_number }}</span>

                                <!-- ITEM 6: WAREHOUSE SEAL STATUS BADGE WITH TIMESTAMP -->
                                @if(!empty($r->warehouse_completed_at) || $r->warehouse_status === 'completed')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small d-inline-flex align-items-center gap-1" style="font-size: 0.75rem; font-weight: 600;">
                                        ✓ Seal & Ready to Dispatch {{ $r->warehouse_completed_at ? '('.$r->warehouse_completed_at->format('d M Y, H:i').')' : '(08 Aug 2026, 09:54)' }}
                                    </span>
                                @elseif($r->status === 'awaiting_warehouse')
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1 small d-inline-flex align-items-center gap-1" style="font-size: 0.75rem; font-weight: 600;">
                                        ⏳ Awaiting Warehouse Pick & Pack
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-1 small d-inline-flex align-items-center gap-1" style="font-size: 0.75rem; font-weight: 600;">
                                        Warehouse In Progress
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT SIDE: ITEM 7 (CUSTOMER NAME), ITEM 8 (ADDRESS & CITY WITH RED PIN) -->
                    <div class="text-md-end">
                        <!-- ITEM 7: CUSTOMER NAME (opstech solution) -->
                        <div class="fw-bold text-body fs-6 mb-1">{{ $r->customer_name }}</div>
                        
                        <!-- ITEM 8: CUSTOMER ADDRESS & CITY WITH RED PIN -->
                        <div class="small text-muted d-flex align-items-center justify-content-md-end gap-1">
                            <span class="text-danger">📍</span>
                            <span>{{ $r->delivery_address }} — <strong class="text-body-secondary">{{ $r->city }}</strong></span>
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: MIDDLE METADATA GRID SECTION (ANNOTATED ITEMS 9 - 12) -->
                <div class="py-3.5 border-bottom border-translucent">
                    <div class="row g-3 align-items-center">
                        <!-- ITEM 9: CUSTOMER -->
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="text-muted small font-monospace fw-semibold mb-1" style="font-size: 0.75rem;">Customer</div>
                            <div class="fw-bold text-body fs-6">{{ $r->customer_name }}</div>
                            <div class="small text-muted font-monospace" style="font-size: 0.78rem;">{{ $r->customer_phone ?? '888888888888' }}</div>
                        </div>

                        <!-- ITEM 10: DESTINATION -->
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="text-muted small font-monospace fw-semibold mb-1" style="font-size: 0.75rem;">Destination</div>
                            <div class="fw-bold text-body fs-6">{{ $r->delivery_address }}</div>
                            <div class="small text-muted" style="font-size: 0.78rem;">{{ $r->city }}</div>
                        </div>

                        <!-- ITEM 11: PRIORITY -->
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="text-muted small font-monospace fw-semibold mb-1" style="font-size: 0.75rem;">Priority</div>
                            <div>
                                <span class="badge rounded-pill {{ $r->priority_badge_class }} px-3 py-1.5 small" style="font-size: 0.75rem;">
                                    {{ strtoupper($r->priority ?? 'NORMAL') }}
                                </span>
                            </div>
                        </div>

                        <!-- ITEM 12: STATUS -->
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="text-muted small font-monospace fw-semibold mb-1" style="font-size: 0.75rem;">Status</div>
                            <div class="fw-bold text-primary fs-6">
                                {{ $r->status_label }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 3: BOTTOM OPERATIONAL ASSIGNMENT & ACTIONS SECTION (ANNOTATED ITEMS 13 - 17) -->
                <div class="pt-3.5 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    
                    <div class="d-flex flex-wrap align-items-center gap-4 flex-grow-1">
                        
                        <!-- ITEM 13: ASSIGNED TO -->
                        <div class="d-flex align-items-start gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person text-muted mt-1" viewBox="0 0 16 16"><path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/></svg>
                            <div>
                                <div class="text-muted small" style="font-size: 0.75rem;">Assigned To</div>
                                @if($r->driver)
                                    <div class="fw-bold text-body small">{{ $r->driver->driver_name }}</div>
                                    <div class="text-muted font-monospace" style="font-size: 0.7rem;">{{ $r->driver->driver_code }}</div>
                                @else
                                    <div class="text-muted small fst-italic">Not Assigned</div>
                                @endif
                            </div>
                        </div>

                        <!-- ITEM 14: VEHICLE -->
                        <div class="d-flex align-items-start gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-truck text-muted mt-1" viewBox="0 0 16 16"><path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-4 0H.5A.5.5 0 0 1 0 11.5v-8zM1 3.5v7h.5a2 2 0 0 1 3.163.787 2 2 0 0 1 3.674 0H10.5a2 2 0 0 1 3.163-.787H15V8.28l-1.48-1.85A.5.5 0 0 0 13.15 6H12v4.5a.5.5 0 0 1-1 0V3.5h-10z"/></svg>
                            <div>
                                <div class="text-muted small" style="font-size: 0.75rem;">Vehicle</div>
                                @if($r->vehicle)
                                    <div class="fw-bold text-body font-monospace small">{{ $r->vehicle->vehicle_number }}</div>
                                    <div class="text-muted" style="font-size: 0.7rem;">{{ $r->vehicle->vehicle_type }}</div>
                                @else
                                    <div class="text-muted small fst-italic">Not Assigned</div>
                                @endif
                            </div>
                        </div>

                        <!-- ITEM 15: EXPECTED DELIVERY -->
                        <div class="d-flex align-items-start gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar-event text-muted mt-1" viewBox="0 0 16 16"><path d="M11 6.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1z"/><path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z"/></svg>
                            <div>
                                <div class="text-muted small" style="font-size: 0.75rem;">Expected Delivery</div>
                                <div class="fw-bold text-body small font-monospace">
                                    {{ $r->expected_delivery_date ? $r->expected_delivery_date->format('d M Y') : '10 Aug 2026' }}
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- ITEM 16 & 17: PRIMARY ACTION BUTTON & OPTIONS MENU -->
                    <div class="d-flex align-items-center gap-2 justify-content-end">
                        <!-- ITEM 16: PRIMARY ACTION BUTTON -->
                        <button type="button" class="btn btn-primary px-4 py-2 fw-bold rounded-3 shadow-sm" onclick="openDeliveryOrderProfile({{ $r->id }})">
                            @if(in_array($r->status, ['dispatched', 'in_transit']))
                                View Delivery
                            @elseif(in_array($r->status, ['driver_vehicle_assigned', 'assigned']))
                                View Delivery
                            @else
                                View / Assign
                            @endif
                        </button>

                        <!-- ITEM 17: THREE-DOTS OPTIONS DROPDOWN MENU -->
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary rounded-3 px-2.5 py-2" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="More Actions">
                                ⋮
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-translucent">
                                <li>
                                    <a class="dropdown-item small" href="javascript:void(0)" onclick="openDeliveryOrderProfile({{ $r->id }})">
                                        View Profile
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item small" href="javascript:void(0)" onclick="window.print()">
                                        Print Delivery Slip
                                    </a>
                                </li>
                                @if(!in_array($r->status, ['dispatched', 'in_transit', 'delivered', 'completed', 'cancelled']))
                                    <li>
                                        <a class="dropdown-item small" href="javascript:void(0)" onclick="openDeliveryOrderProfile({{ $r->id }})">
                                            Assign Driver & Vehicle
                                        </a>
                                    </li>
                                @endif
                                @if(in_array($r->status, ['driver_vehicle_assigned', 'assigned', 'ready_for_dispatch']))
                                    <li>
                                        <a class="dropdown-item small text-success fw-bold" href="javascript:void(0)" onclick="openDeliveryOrderProfile({{ $r->id }})">
                                            Confirm Dispatch
                                        </a>
                                    </li>
                                @endif
                                @if(in_array($r->status, ['dispatched', 'in_transit']))
                                    <li>
                                        <a class="dropdown-item small text-danger" href="javascript:void(0)" onclick="openCancelDispatchModal({{ $r->id }}, '{{ $r->order_reference }}', '{{ $r->dispatch_number }}')">
                                            Cancel Dispatch
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <!-- EMPTY STATE CARD (FULL WIDTH & CENTERED) -->
            <div class="card p-5 rounded-4 border-translucent bg-body text-center d-flex flex-column align-items-center justify-content-center" style="min-height: 320px;">
                <div class="mb-3 text-muted">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-box-seam" viewBox="0 0 16 16"><path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l.24.462 6.27-2.308 6.27 2.308.24-.462-6.34-2.387zM15 4.239l-6.5 2.6-6.5-2.6V12.5a.5.5 0 0 0 .25.433l6 3.5a.5.5 0 0 0 .5 0l6-3.5a.5.5 0 0 0 .25-.433V4.239z"/></svg>
                </div>
                <h5 class="fw-bold text-body mb-1">No delivery orders found</h5>
                <p class="text-muted small mb-3">Orders synchronized from CRM and Warehouse will appear here.</p>
                @if(request('search') || request('priority') || request('city') || request('status_card'))
                    <div>
                        <a href="{{ route('transport.index', ['tab' => 'delivery-orders']) }}" class="btn btn-sm btn-outline-primary px-4 rounded-3 fw-bold">
                            Reset Filters
                        </a>
                    </div>
                @endif
            </div>
        @endforelse
    </div>

    <!-- SERVER-SIDE PAGINATION -->
    @if($requests->hasPages())
        <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 mt-4">
            <div class="text-muted small font-monospace">
                Showing {{ $requests->firstItem() }}–{{ $requests->lastItem() }} of {{ $requests->total() }} delivery orders
            </div>
            <div>
                {{ $requests->appends(request()->query())->links() }}
            </div>
        </div>
    @endif
</div>

<!-- ========================================================================= -->
<!-- DEDICATED DELIVERY ORDER PROFILE & ASSIGNMENT MODAL -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalDeliveryOrderProfile" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 shadow-lg border-translucent bg-body">
            <div class="modal-header border-bottom border-translucent p-3 bg-body-tertiary rounded-top-4">
                <div class="d-flex align-items-center gap-3">
                    <span class="fs-4">📦</span>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="modal-title fw-black text-body mb-0" id="profOrderReference">SO-2026-000001</h5>
                            <span class="badge bg-primary font-monospace px-2.5 py-1" id="profRequestNumber">TRN-000001</span>
                            <span class="badge rounded-pill" id="profPriorityBadge">NORMAL</span>
                        </div>
                        <div class="small text-muted" id="profCustomerName">Customer Company Name</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <!-- Order Overview Section -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="p-3 bg-body-tertiary rounded-3 border border-translucent h-100">
                            <span class="text-muted small d-block">Destination City</span>
                            <strong class="text-body fs-6" id="profCity">Mumbai</strong>
                            <div class="small text-muted mt-1" id="profAddress">123 Street Address</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-body-tertiary rounded-3 border border-translucent h-100">
                            <span class="text-muted small d-block">Package & Weight</span>
                            <strong class="text-body fs-6" id="profPackages">5 Cartons</strong>
                            <div class="small text-muted mt-1" id="profWeight">120.0 kg | 1.5 m³</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-body-tertiary rounded-3 border border-translucent h-100">
                            <span class="text-muted small d-block">Expected Delivery</span>
                            <strong class="text-body fs-6" id="profExpectedDate">10 Aug 2026</strong>
                            <div class="small text-muted mt-1" id="profSourceModule">CRM Sales Order</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-body-tertiary rounded-3 border border-translucent h-100">
                            <span class="text-muted small d-block">Warehouse Fulfillment</span>
                            <span class="badge rounded-pill mt-1" id="profWarehouseBadge">Completed</span>
                            <div class="small text-muted mt-1" id="profWarehouseTime">H:i, d M Y</div>
                        </div>
                    </div>
                </div>

                <!-- Assignment Card Section -->
                <div class="card border-translucent rounded-3 mb-4 bg-body-tertiary" id="profAssignmentCard">
                    <div class="card-header bg-body border-bottom border-translucent d-flex align-items-center justify-content-between p-3">
                        <h6 class="fw-bold text-body mb-0">👨‍✈️ Driver & Vehicle Resource Assignment</h6>
                        <span class="badge rounded-pill font-monospace px-2.5 py-1" id="profAssignmentStatusBadge">READY FOR ASSIGNMENT</span>
                    </div>
                    <div class="card-body p-3">
                        <div class="alert alert-warning border-warning-subtle small mb-3 d-none" id="profAssignmentLockAlert">
                            🔒 <strong>Assignment Locked:</strong> <span id="profLockReason">Resource assignment locked until Organize Stock completes Pick & Pack.</span>
                        </div>

                        <div id="profActiveAssignmentContainer" class="d-none">
                            <div class="p-3 bg-body rounded-3 border border-translucent mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="badge bg-primary font-monospace" id="profAssignmentNumber">ASN-000001</span>
                                    <span class="small text-muted" id="profAssignedTime">Assigned: H:i</span>
                                </div>
                                <div class="row g-2 small">
                                    <div class="col-md-6">
                                        <div class="p-2 bg-body-tertiary rounded-2 border border-translucent">
                                            <strong>👤 Assigned Driver:</strong>
                                            <div class="fw-bold text-body fs-6" id="profAssignedDriverName">John Doe</div>
                                            <div class="text-muted" id="profAssignedDriverPhone">9876543210</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-2 bg-body-tertiary rounded-2 border border-translucent">
                                            <strong>🚛 Assigned Vehicle:</strong>
                                            <div class="fw-bold text-body fs-6" id="profAssignedVehicleReg">MH12AB1234</div>
                                            <div class="text-muted" id="profAssignedVehicleType">Van (1,000 kg capacity)</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-2 small text-muted text-end">
                                    Assigned by: <span id="profAssignedByName">Transport Manager</span>
                                </div>
                            </div>

                            <button type="button" class="btn btn-sm btn-outline-warning w-100 fw-bold rounded-3" onclick="toggleReassignForm()">
                                🔄 Reassign Driver / Vehicle
                            </button>
                        </div>

                        <form id="profAssignmentForm" onsubmit="submitAssignmentForm(event)" class="mt-2">
                            <input type="hidden" id="profTaskId" name="task_id" value="">
                            <input type="hidden" id="profIsReassign" value="0">

                            <div class="mb-3 d-none" id="profReassignReasonGroup">
                                <label class="form-label small fw-bold text-warning">Reassignment Reason <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm bg-body border-translucent" 
                                       id="profReassignReason" placeholder="Enter reason for driver/vehicle replacement...">
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-body">Select Eligible Driver <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm bg-body border-translucent" id="profSelectDriver" required>
                                        <option value="">-- Choose Eligible Driver --</option>
                                    </select>
                                    <div class="form-text small text-muted" id="profDriverHelp">Only active, available drivers with valid licenses are displayed.</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-body">Select Eligible Vehicle <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm bg-body border-translucent" id="profSelectVehicle" onchange="validateVehicleCapacity()" required>
                                        <option value="">-- Choose Eligible Vehicle --</option>
                                    </select>
                                    <div class="form-text small text-muted" id="profVehicleHelp">Only available, operational vehicles are displayed.</div>
                                </div>
                            </div>

                            <div class="alert alert-danger border-danger-subtle small mt-3 mb-0 d-none" id="profCapacityWarning">
                                🚫 <strong>Capacity Validation Failed:</strong> Selected vehicle does not have sufficient capacity for this order.
                            </div>

                            <div class="mt-3 text-end">
                                <button type="submit" class="btn btn-sm btn-primary px-4 fw-bold rounded-3" id="profSubmitAssignBtn">
                                    Confirm Assignment
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- PHASE 5 DISPATCH ACTION SECTION -->
                <div class="card border-primary-subtle rounded-3 mb-4 bg-body-tertiary d-none" id="profDispatchCard">
                    <div class="card-header bg-primary-subtle border-bottom border-primary-subtle d-flex align-items-center justify-content-between p-3">
                        <h6 class="fw-bold text-primary mb-0">🚀 Operational Dispatch Control</h6>
                        <span class="badge bg-primary text-white font-monospace px-2.5 py-1" id="profDispatchBadge">READY FOR DISPATCH</span>
                    </div>
                    <div class="card-body p-3">
                        <div class="alert alert-warning border-warning-subtle small mb-3 d-none" id="profDispatchBlockedAlert">
                            ⚠️ <strong>Dispatch Unavailable:</strong> <span id="profDispatchBlockedReason">Warehouse fulfillment or resource assignment incomplete.</span>
                        </div>

                        <div class="alert alert-success border-success-subtle small mb-0 d-none" id="profDispatchedBanner">
                            🚀 <strong>Shipment Dispatched:</strong> Released under Dispatch ID <strong id="profDispatchedNumber">DSP-2026-000001</strong> on <span id="profDispatchedTime">N/A</span>.
                        </div>

                        <div class="text-end d-none" id="profDispatchActionGroup">
                            <button type="button" class="btn btn-success px-4 fw-bold rounded-3 shadow-sm" id="profTriggerDispatchBtn" onclick="openDispatchConfirmModal()">
                                🚀 Confirm Dispatch
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Real-Event Timeline -->
                <div class="p-3 bg-body rounded-3 border border-translucent">
                    <h6 class="fw-bold text-body mb-3 border-bottom pb-2">📜 Real-Event Transport Timeline</h6>
                    <div class="timeline-container ps-3" id="profTimeline">
                        <!-- Rendered via JavaScript -->
                    </div>
                </div>
            </div>

            <div class="modal-footer border-top border-translucent p-3">
                <button type="button" class="btn btn-secondary rounded-3 px-4 fw-bold" data-bs-dismiss="modal">Close Profile</button>
            </div>
        </div>
    </div>
</div>

<!-- DISPATCH CONFIRMATION MODAL (#modalConfirmDispatch) -->
<div class="modal fade" id="modalConfirmDispatch" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow-lg border-translucent bg-body">
            <div class="modal-header border-bottom border-translucent p-3 bg-primary-subtle text-primary rounded-top-4">
                <h5 class="modal-title fw-black mb-0">🚀 Confirm Shipment Dispatch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-body mb-3">Are you sure this vehicle is being released for live delivery?</p>
                <div class="p-3 bg-body-tertiary rounded-3 border border-translucent mb-3">
                    <div class="small mb-2"><strong>Order Reference:</strong> <span class="fw-bold font-monospace text-primary" id="confirmOrderRef">SO-2026-000123</span></div>
                    <div class="small mb-2"><strong>Assigned Driver:</strong> <span class="fw-bold text-body" id="confirmDriver">Rahul Sharma</span></div>
                    <div class="small mb-2"><strong>Assigned Vehicle:</strong> <span class="fw-bold text-body font-monospace" id="confirmVehicle">MH12AB1234</span></div>
                    <div class="small mb-0"><strong>Destination:</strong> <span class="fw-bold text-body" id="confirmDestination">Mumbai</span></div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-body">Dispatch Notes (Optional)</label>
                    <input type="text" class="form-control form-control-sm bg-body border-translucent" id="confirmDispatchNotes" placeholder="Enter gate pass notes, seal tag numbers, or route remarks...">
                </div>
                <div class="alert alert-info small mb-0">
                    ℹ️ <strong>Operational Action:</strong> This releases the shipment to active delivery, updates driver status to <strong>ON DELIVERY</strong>, and vehicle status to <strong>ON TRIP</strong>.
                </div>
            </div>
            <div class="modal-footer border-top border-translucent p-3">
                <button type="button" class="btn btn-secondary rounded-3 px-4 fw-bold" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success rounded-3 px-4 fw-bold shadow-sm" id="confirmDispatchBtn" onclick="executeDispatchOrder()">
                    Confirm Dispatch
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentOrderData = null;

function refreshDeliveryOrders() {
    window.location.reload();
}

function openDeliveryOrderProfile(id) {
    fetch(`/transport/delivery-orders/${id}`, {
        headers: { 'Accept': 'application/json' }
    })
        .then(response => response.json())
        .then(data => {
            currentOrderData = data;

            document.getElementById('profTaskId').value = data.id;
            document.getElementById('profIsReassign').value = '0';
            document.getElementById('profOrderReference').textContent = data.order_reference;
            document.getElementById('profRequestNumber').textContent = data.request_number;
            document.getElementById('profCustomerName').textContent = data.customer_name;
            document.getElementById('profCity').textContent = data.delivery_city;
            document.getElementById('profAddress').textContent = data.delivery_address || 'No specific address provided';
            document.getElementById('profPackages').textContent = (data.package_count || 1) + ' Cartons';
            document.getElementById('profWeight').textContent = `${data.weight_kg} kg | ${data.volume_m3} m³`;
            document.getElementById('profExpectedDate').textContent = data.expected_delivery_date || 'N/A';
            document.getElementById('profSourceModule').textContent = data.source_module;

            const priorityBadge = document.getElementById('profPriorityBadge');
            priorityBadge.className = `badge rounded-pill ${data.priority_badge_class}`;
            priorityBadge.textContent = (data.priority || 'normal').toUpperCase();

            const warehouseBadge = document.getElementById('profWarehouseBadge');
            warehouseBadge.className = `badge rounded-pill ${data.warehouse_status_badge_class}`;
            warehouseBadge.textContent = data.warehouse_status_label;
            document.getElementById('profWarehouseTime').textContent = data.warehouse_completed_at ? `Completed: ${data.warehouse_completed_at}` : 'Fulfillment In Progress';

            const assignStatusBadge = document.getElementById('profAssignmentStatusBadge');
            assignStatusBadge.className = `badge rounded-pill font-monospace ${data.status_badge_class}`;
            assignStatusBadge.textContent = data.status_label;

            const lockAlert = document.getElementById('profAssignmentLockAlert');
            const assignForm = document.getElementById('profAssignmentForm');
            const activeContainer = document.getElementById('profActiveAssignmentContainer');
            const reassignReasonGroup = document.getElementById('profReassignReasonGroup');
            const submitBtn = document.getElementById('profSubmitAssignBtn');

            lockAlert.classList.add('d-none');
            activeContainer.classList.add('d-none');
            assignForm.classList.remove('d-none');
            reassignReasonGroup.classList.add('d-none');
            document.getElementById('profReassignReason').required = false;

            submitBtn.textContent = 'Confirm Assignment';
            submitBtn.className = 'btn btn-sm btn-primary px-4 fw-bold rounded-3';

            const driverSelect = document.getElementById('profSelectDriver');
            driverSelect.innerHTML = '<option value="">-- Choose Eligible Driver --</option>';
            if (data.eligible_drivers && data.eligible_drivers.length > 0) {
                data.eligible_drivers.forEach(drv => {
                    const opt = document.createElement('option');
                    opt.value = drv.id;
                    opt.textContent = `👤 ${drv.driver_name} (${drv.employee_id}) — ${drv.license_class || 'LMN'}`;
                    driverSelect.appendChild(opt);
                });
            }

            const vehicleSelect = document.getElementById('profSelectVehicle');
            vehicleSelect.innerHTML = '<option value="">-- Choose Eligible Vehicle --</option>';
            if (data.eligible_vehicles && data.eligible_vehicles.length > 0) {
                data.eligible_vehicles.forEach(veh => {
                    const opt = document.createElement('option');
                    opt.value = veh.id;
                    opt.setAttribute('data-capacity', veh.load_capacity_kg || 0);
                    opt.textContent = `🚛 ${veh.vehicle_number} (${veh.vehicle_type}) — Cap: ${veh.load_capacity_kg} kg`;
                    vehicleSelect.appendChild(opt);
                });
            }

            if (data.status === 'awaiting_warehouse') {
                assignForm.classList.add('d-none');
                lockAlert.classList.remove('d-none');
                document.getElementById('profLockReason').textContent = 'Resource assignment locked until Organize Stock completes Pick & Pack and seals the shipment.';
            } else if (data.status === 'cancelled') {
                assignForm.classList.add('d-none');
                lockAlert.classList.remove('d-none');
                document.getElementById('profLockReason').textContent = 'Order is cancelled. Resource assignment is unavailable.';
            } else if (data.active_assignment) {
                activeContainer.classList.remove('d-none');
                assignForm.classList.add('d-none');

                document.getElementById('profAssignmentNumber').textContent = data.active_assignment.assignment_number;
                document.getElementById('profAssignedTime').textContent = 'Assigned: ' + data.active_assignment.assigned_at;
                document.getElementById('profAssignedDriverName').textContent = data.active_assignment.driver_name || 'N/A';
                document.getElementById('profAssignedDriverPhone').textContent = data.active_assignment.driver_phone || 'N/A';
                document.getElementById('profAssignedVehicleReg').textContent = data.active_assignment.vehicle_number || 'N/A';
                document.getElementById('profAssignedVehicleType').textContent = (data.active_assignment.vehicle_type || 'Vehicle') + ' (Assigned)';
                document.getElementById('profAssignedByName').textContent = data.active_assignment.assigned_by_name || 'Transport Manager';
            }

            const dispatchCard = document.getElementById('profDispatchCard');
            const dispatchBlockedAlert = document.getElementById('profDispatchBlockedAlert');
            const dispatchedBanner = document.getElementById('profDispatchedBanner');
            const dispatchActionGroup = document.getElementById('profDispatchActionGroup');

            if (dispatchCard) {
                dispatchCard.classList.remove('d-none');
                dispatchBlockedAlert.classList.add('d-none');
                dispatchedBanner.classList.add('d-none');
                dispatchActionGroup.classList.add('d-none');

                if (data.status === 'dispatched' || data.status === 'in_transit') {
                    dispatchedBanner.classList.remove('d-none');
                    document.getElementById('profDispatchedNumber').textContent = data.dispatch_number || 'DSP-ACTIVE';
                    document.getElementById('profDispatchedTime').textContent = data.dispatched_at || 'Dispatched';
                } else {
                    const elig = data.dispatch_eligibility || { eligible: false, reason: 'Dispatch conditions not met.' };
                    if (elig.eligible) {
                        dispatchActionGroup.classList.remove('d-none');
                    } else {
                        dispatchBlockedAlert.classList.remove('d-none');
                        document.getElementById('profDispatchBlockedReason').textContent = elig.reason;
                    }
                }
            }

            const timelineContainer = document.getElementById('profTimeline');
            timelineContainer.innerHTML = '';

            if (data.timeline && data.timeline.length > 0) {
                data.timeline.forEach(item => {
                    const eventDiv = document.createElement('div');
                    eventDiv.className = 'd-flex align-items-start gap-3 mb-3';
                    eventDiv.innerHTML = `
                        <div class="fs-5">${item.icon || '📌'}</div>
                        <div class="flex-grow-1 border-bottom border-translucent pb-2">
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="fw-bold text-body small">${item.title}</span>
                                <span class="small text-muted font-monospace" style="font-size: 0.75rem;">${item.timestamp || 'Pending'}</span>
                            </div>
                            <div class="small text-muted mt-0.5">${item.description}</div>
                        </div>
                    `;
                    timelineContainer.appendChild(eventDiv);
                });
            } else {
                timelineContainer.innerHTML = '<div class="text-muted small">No timeline events recorded yet.</div>';
            }

            const modal = new bootstrap.Modal(document.getElementById('modalDeliveryOrderProfile'));
            modal.show();
        })
        .catch(error => {
            console.error('Error fetching delivery order profile:', error);
            alert('Failed to fetch delivery order profile details.');
        });
}

function openDispatchConfirmModal() {
    if (!currentOrderData) return;
    document.getElementById('confirmOrderRef').textContent = currentOrderData.order_reference;
    document.getElementById('confirmDriver').textContent = (currentOrderData.driver?.driver_name || 'Driver') + ' (' + (currentOrderData.driver?.driver_code || '') + ')';
    document.getElementById('confirmVehicle').textContent = (currentOrderData.vehicle?.vehicle_number || 'Vehicle') + ' (' + (currentOrderData.vehicle?.vehicle_code || '') + ')';
    document.getElementById('confirmDestination').textContent = currentOrderData.delivery_city || currentOrderData.delivery_address || 'Destination';

    const modal = new bootstrap.Modal(document.getElementById('modalConfirmDispatch'));
    modal.show();
}

function executeDispatchOrder() {
    if (!currentOrderData || !currentOrderData.id) return;

    const confirmBtn = document.getElementById('confirmDispatchBtn');
    confirmBtn.disabled = true;
    confirmBtn.textContent = 'Processing Dispatch...';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const notes = document.getElementById('confirmDispatchNotes')?.value || '';

    fetch(`/transport/delivery-orders/${currentOrderData.id}/dispatch`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({ dispatch_notes: notes }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.reload();
        } else {
            alert('Dispatch Failed: ' + (data.message || 'Validation Error'));
            confirmBtn.disabled = false;
            confirmBtn.textContent = 'Confirm Dispatch';
        }
    })
    .catch(error => {
        console.error('Dispatch execution error:', error);
        alert('Dispatch could not be completed. Refresh the order and try again.');
        confirmBtn.disabled = false;
        confirmBtn.textContent = 'Confirm Dispatch';
    });
}

function validateVehicleCapacity() {
    const vehicleSelect = document.getElementById('profSelectVehicle');
    const warning = document.getElementById('profCapacityWarning');
    const submitBtn = document.getElementById('profSubmitAssignBtn');

    if (!vehicleSelect.value || !currentOrderData) {
        warning.classList.add('d-none');
        submitBtn.disabled = false;
        return;
    }

    const selectedOpt = vehicleSelect.options[vehicleSelect.selectedIndex];
    const capacity = parseFloat(selectedOpt.getAttribute('data-capacity') || 0);
    const orderWeight = parseFloat(currentOrderData.weight_kg || 0);

    if (orderWeight > 0 && capacity > 0 && orderWeight > capacity) {
        warning.innerHTML = `🚫 <strong>Capacity Validation Failed:</strong> Selected vehicle capacity (${capacity} kg) is insufficient for shipment weight (${orderWeight} kg).`;
        warning.classList.remove('d-none');
        submitBtn.disabled = true;
    } else {
        warning.classList.add('d-none');
        submitBtn.disabled = false;
    }
}

function toggleReassignForm() {
    const assignForm = document.getElementById('profAssignmentForm');
    const reassignReasonGroup = document.getElementById('profReassignReasonGroup');
    const submitBtn = document.getElementById('profSubmitAssignBtn');
    const isReassign = document.getElementById('profIsReassign');

    assignForm.classList.remove('d-none');
    reassignReasonGroup.classList.remove('d-none');
    document.getElementById('profReassignReason').required = true;
    isReassign.value = '1';
    submitBtn.textContent = 'Confirm Reassignment';
    submitBtn.className = 'btn btn-sm btn-warning px-4 fw-bold rounded-3';
}

function submitAssignmentForm(event) {
    event.preventDefault();

    const taskId = document.getElementById('profTaskId').value;
    const driverId = document.getElementById('profSelectDriver').value;
    const vehicleId = document.getElementById('profSelectVehicle').value;
    const isReassign = document.getElementById('profIsReassign').value === '1';
    const reassignReason = document.getElementById('profReassignReason').value;

    if (!driverId || !vehicleId) {
        alert('Please select both an eligible driver and an eligible vehicle.');
        return;
    }

    const endpoint = isReassign ? `/transport/delivery-orders/${taskId}/reassign` : `/transport/delivery-orders/${taskId}/assign`;
    const payload = {
        driver_id: driverId,
        vehicle_id: vehicleId,
        reassignment_reason: isReassign ? reassignReason : null,
    };

    const submitBtn = document.getElementById('profSubmitAssignBtn');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Processing Assignment...';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    fetch(endpoint, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify(payload),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.reload();
        } else {
            alert('Assignment Failed: ' + (data.message || 'Validation error'));
            submitBtn.disabled = false;
            submitBtn.textContent = isReassign ? 'Confirm Reassignment' : 'Confirm Assignment';
        }
    })
    .catch(error => {
        console.error('Error submitting assignment:', error);
        alert('Assignment failed. Please check inputs and try again.');
        submitBtn.disabled = false;
        submitBtn.textContent = isReassign ? 'Confirm Reassignment' : 'Confirm Assignment';
    });
}
</script>
