<div>
    <!-- DELIVERY ORDERS SEARCH, FILTER & CONTROL BAR -->
    <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body mb-4">
        <form method="GET" action="{{ route('transport.index') }}" id="formDeliveryOrdersFilter" class="row g-3 align-items-end">
            <input type="hidden" name="tab" value="delivery-orders">

            <!-- SEARCH INPUT FIELD -->
            <div class="col-12 col-md-4">
                <label class="form-label small fw-bold text-muted text-uppercase font-monospace mb-1.5" style="font-size: 0.725rem;">Search Orders</label>
                <div class="input-group input-group-md">
                    <span class="input-group-text bg-body-tertiary border-translucent">🔍</span>
                    <input type="text" name="search" class="form-control bg-body-tertiary border-translucent font-monospace" 
                           placeholder="Search SO, TRN, Customer..." 
                           value="{{ request('search') }}">
                </div>
            </div>

            <!-- PRIORITY FILTER DROPDOWN -->
            <div class="col-6 col-md-2">
                <label class="form-label small fw-bold text-muted text-uppercase font-monospace mb-1.5" style="font-size: 0.725rem;">Priority</label>
                <select name="priority" class="form-select bg-body-tertiary border-translucent" onchange="this.form.submit()">
                    <option value="">All Priorities</option>
                    <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                    <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                </select>
            </div>

            <!-- CITY / REGION FILTER DROPDOWN -->
            <div class="col-6 col-md-3">
                <label class="form-label small fw-bold text-muted text-uppercase font-monospace mb-1.5" style="font-size: 0.725rem;">Destination City</label>
                <select name="city" class="form-select bg-body-tertiary border-translucent" onchange="this.form.submit()">
                    <option value="">All Destination Cities</option>
                    <option value="Pune" {{ request('city') == 'Pune' ? 'selected' : '' }}>Pune</option>
                    <option value="Mumbai" {{ request('city') == 'Mumbai' ? 'selected' : '' }}>Mumbai</option>
                    <option value="Nagpur" {{ request('city') == 'Nagpur' ? 'selected' : '' }}>Nagpur</option>
                    <option value="Nashik" {{ request('city') == 'Nashik' ? 'selected' : '' }}>Nashik</option>
                    <option value="Aurangabad" {{ request('city') == 'Aurangabad' ? 'selected' : '' }}>Aurangabad</option>
                    <option value="Kolhapur" {{ request('city') == 'Kolhapur' ? 'selected' : '' }}>Kolhapur</option>
                </select>
            </div>

            <!-- ACTION BUTTONS: SEARCH & RESET -->
            <div class="col-12 col-md-3 d-flex gap-2 justify-content-md-end">
                <button type="submit" class="btn btn-primary rounded-3 px-3.5 py-2 fw-bold d-inline-flex align-items-center gap-2 flex-grow-1 flex-md-grow-0">
                    <span>Search</span>
                </button>
                @if(request('search') || request('priority') || request('city') || request('status_card'))
                    <a href="{{ route('transport.index', ['tab' => 'delivery-orders']) }}" class="btn btn-outline-secondary rounded-3 px-3 py-2 fw-bold" title="Reset All Filters">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- LOADING SKELETON CONTAINER (HIDDEN BY DEFAULT) -->
    <div id="deliveryCardsSkeleton" class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3.5 d-none mb-4">
        @for($i = 0; $i < 6; $i++)
            <div class="col">
                <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body placeholder-glow h-100">
                    <div class="d-flex justify-content-between pb-3 border-bottom border-translucent">
                        <span class="placeholder col-6 rounded-3 py-2"></span>
                        <span class="placeholder col-3 rounded-3 py-2"></span>
                    </div>
                    <div class="py-3 vstack gap-2">
                        <span class="placeholder col-8 rounded-3 py-1"></span>
                        <span class="placeholder col-10 rounded-3 py-1"></span>
                        <span class="placeholder col-7 rounded-3 py-1"></span>
                    </div>
                    <div class="pt-3">
                        <span class="placeholder col-12 rounded-3 py-3"></span>
                    </div>
                </div>
            </div>
        @endfor
    </div>

    <!-- DELIVERY CARDS CONTAINER (RESPONSIVE 3-COLUMN VERTICAL CARD GRID) -->
    <div id="deliveryCardsContainer">
        @if($requests->isEmpty())
            <!-- EMPTY STATE CARD -->
            <div class="card p-5 rounded-4 border-translucent bg-body text-center d-flex flex-column align-items-center justify-content-center" style="min-height: 320px;">
                <div class="avatar-circle bg-primary-subtle text-primary fs-2 fw-bold d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 64px; height: 64px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-box-seam text-primary" viewBox="0 0 16 16"><path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l.24.462 6.27-2.308 6.27 2.308.24-.462-6.34-2.387zM15 4.239l-6.5 2.6-6.5-2.6V12.5a.5.5 0 0 0 .25.433l6 3.5a.5.5 0 0 0 .5 0l6-3.5a.5.5 0 0 0 .25-.433V4.239z"/></svg>
                </div>
                <h5 class="fw-bold text-body mb-1">No delivery orders found</h5>
                <p class="text-muted small mb-3">Try adjusting your search or filters.</p>
                @if(request('search') || request('priority') || request('city') || request('status_card'))
                    <div>
                        <a href="{{ route('transport.index', ['tab' => 'delivery-orders']) }}" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                            Reset Filters
                        </a>
                    </div>
                @endif
            </div>
        @else
            <!-- 3-COLUMN RESPONSIVE VERTICAL CARD GRID -->
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3.5 mb-4">
                @foreach($requests as $r)
                    <div class="col">
                        <div class="card h-100 p-4 rounded-4 shadow-sm border-translucent bg-body delivery-order-card d-flex flex-column justify-content-between">
                            
                            <div>
                                <!-- CARD HEADER: TRUCK AVATAR + SO NUMBER + STATUS BADGE + PRIORITY BADGE -->
                                <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
                                    <div class="d-flex align-items-center gap-2.5">
                                        <!-- CIRCLE TRUCK AVATAR (48px x 48px) -->
                                        <div class="d-flex align-items-center justify-content-center flex-shrink-0 bg-primary-subtle text-primary rounded-circle" style="width: 48px; height: 48px;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-truck" viewBox="0 0 16 16">
                                                <path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-4 0H.5A.5.5 0 0 1 0 11.5v-8zM1 3.5v7h.5a2 2 0 0 1 3.163.787 2 2 0 0 1 3.674 0H10.5a2 2 0 0 1 3.163-.787H15V8.28l-1.48-1.85A.5.5 0 0 0 13.15 6H12v4.5a.5.5 0 0 1-1 0V3.5h-10z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <a href="javascript:void(0)" onclick="openDeliveryOrderProfile({{ $r->id }})" class="fw-bold text-primary text-decoration-none font-monospace fs-5 d-block line-clamp-1">
                                                {{ $r->order_reference }}
                                            </a>
                                            <span class="small text-muted font-monospace" style="font-size: 0.8rem;">
                                                {{ $r->request_number }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="d-flex flex-column align-items-end gap-1 flex-shrink-0">
                                        <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 small fw-bold">
                                            {{ $r->status_label }}
                                        </span>
                                        <span class="badge rounded-pill {{ $r->priority_badge_class }} px-2 py-0.5" style="font-size: 0.65rem;">
                                            {{ strtoupper($r->priority ?? 'NORMAL') }}
                                        </span>
                                    </div>
                                </div>

                                <!-- WAREHOUSE SEAL & READINESS STRIP -->
                                <div class="mb-3">
                                    @if(!empty($r->warehouse_completed_at) || $r->warehouse_status === 'completed')
                                        <div class="p-2 bg-success-subtle text-success border border-success-subtle rounded-3 small d-flex align-items-center justify-content-between gap-1" style="font-size: 0.75rem;">
                                            <span class="fw-bold">✓ Seal & Ready to Dispatch</span>
                                            <span class="font-monospace">{{ $r->warehouse_completed_at ? $r->warehouse_completed_at->format('d M, H:i') : '08 Aug, 09:54' }}</span>
                                        </div>
                                    @elseif($r->status === 'awaiting_warehouse')
                                        <div class="p-2 bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-3 small fw-bold" style="font-size: 0.75rem;">
                                            ⏳ Awaiting Warehouse Pick & Pack
                                        </div>
                                    @else
                                        <div class="p-2 bg-body-tertiary text-secondary border border-translucent rounded-3 small fw-semibold" style="font-size: 0.75rem;">
                                            Warehouse In Progress
                                        </div>
                                    @endif
                                </div>

                                <!-- CARD BODY DETAILS LIST WITH CLEAN ICONS -->
                                <div class="vstack gap-2 pt-1 pb-2">
                                    <!-- 1. CUSTOMER -->
                                    <div class="d-flex align-items-start gap-2.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" class="bi bi-person text-muted mt-1 flex-shrink-0" viewBox="0 0 16 16"><path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/></svg>
                                        <div>
                                            <span class="text-muted small d-block" style="font-size: 0.725rem;">Customer</span>
                                            <strong class="text-body small">{{ $r->customer_name }}</strong>
                                            <div class="small text-muted font-monospace" style="font-size: 0.75rem;">{{ $r->customer_phone ?? '888888888888' }}</div>
                                        </div>
                                    </div>

                                    <!-- 2. DESTINATION -->
                                    <div class="d-flex align-items-start gap-2.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" class="bi bi-geo-alt text-muted mt-1 flex-shrink-0" viewBox="0 0 16 16"><path d="M12.166 8.94c-.524 1.062-1.234 2.12-1.96 3.07A31.493 31.493 0 0 1 8 14.58a31.481 31.481 0 0 1-2.206-2.57c-.726-.95-1.436-2.008-1.96-3.07C3.304 7.867 3 6.862 3 6a5 5 0 0 1 10 0c0 .862-.305 1.867-.834 2.94zM8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10z"/><path d="M8 8a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm0 1a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/></svg>
                                        <div>
                                            <span class="text-muted small d-block" style="font-size: 0.725rem;">Destination</span>
                                            <strong class="text-body small line-clamp-1">{{ $r->delivery_address }}</strong>
                                            <div class="small text-muted" style="font-size: 0.75rem;">{{ $r->city }}</div>
                                        </div>
                                    </div>

                                    <!-- 3. ASSIGNED DRIVER -->
                                    <div class="d-flex align-items-start gap-2.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" class="bi bi-person-badge text-muted mt-1 flex-shrink-0" viewBox="0 0 16 16"><path d="M6.5 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1h-3zM11 8a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/><path d="M4.5 0A2.5 2.5 0 0 0 2 2.5V14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2.5A2.5 2.5 0 0 0 11.5 0h-7zM3 2.5A1.5 1.5 0 0 1 4.5 1h7A1.5 1.5 0 0 1 13 2.5V14a1 1 0 0 1-1 1h-8a1 1 0 0 1-1-1V2.5z"/></svg>
                                        <div>
                                            <span class="text-muted small d-block" style="font-size: 0.725rem;">Assigned Driver</span>
                                            @if($r->driver)
                                                <strong class="text-body small">{{ $r->driver->driver_name }}</strong>
                                                <span class="small text-muted font-monospace">({{ $r->driver->driver_code }})</span>
                                            @else
                                                <span class="text-muted small fst-italic">Not Assigned</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- 4. VEHICLE -->
                                    <div class="d-flex align-items-start gap-2.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" class="bi bi-truck text-muted mt-1 flex-shrink-0" viewBox="0 0 16 16"><path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-4 0H.5A.5.5 0 0 1 0 11.5v-8zM1 3.5v7h.5a2 2 0 0 1 3.163.787 2 2 0 0 1 3.674 0H10.5a2 2 0 0 1 3.163-.787H15V8.28l-1.48-1.85A.5.5 0 0 0 13.15 6H12v4.5a.5.5 0 0 1-1 0V3.5h-10z"/></svg>
                                        <div>
                                            <span class="text-muted small d-block" style="font-size: 0.725rem;">Vehicle</span>
                                            @if($r->vehicle)
                                                <strong class="text-body font-monospace small">{{ $r->vehicle->vehicle_number }}</strong>
                                                <span class="small text-muted">({{ $r->vehicle->vehicle_type }})</span>
                                            @else
                                                <span class="text-muted small fst-italic">Not Assigned</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- 5. EXPECTED DELIVERY -->
                                    <div class="d-flex align-items-start gap-2.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" class="bi bi-calendar-event text-muted mt-1 flex-shrink-0" viewBox="0 0 16 16"><path d="M11 6.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1z"/><path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z"/></svg>
                                        <div>
                                            <span class="text-muted small d-block" style="font-size: 0.725rem;">Expected Delivery</span>
                                            <strong class="text-body font-monospace small">{{ $r->expected_delivery_date ? $r->expected_delivery_date->format('d M Y') : '10 Aug 2026' }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- CARD FOOTER: DIVIDER + PRIMARY BUTTON & THREE DOTS -->
                            <div>
                                <div class="border-bottom border-translucent my-3"></div>

                                <div class="d-flex align-items-center justify-content-between gap-2">
                                    <button type="button" class="btn btn-primary flex-grow-1 py-2 fw-bold rounded-3 shadow-xs" onclick="openDeliveryOrderProfile({{ $r->id }})">
                                        @if(in_array($r->status, ['dispatched', 'in_transit']))
                                            View Delivery
                                        @elseif(in_array($r->status, ['driver_vehicle_assigned', 'assigned']))
                                            View Delivery
                                        @else
                                            View / Assign
                                        @endif
                                    </button>

                                    <div class="dropdown">
                                        <button class="btn btn-outline-secondary rounded-circle d-inline-flex align-items-center justify-content-center p-0 shadow-xs" 
                                                style="width: 40px; height: 40px;" 
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false" title="More Actions" aria-label="More Actions">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-three-dots-vertical" viewBox="0 0 16 16"><path d="M9.5 13a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z"/></svg>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-translucent rounded-3">
                                            <li>
                                                <a class="dropdown-item small d-flex align-items-center gap-2" href="javascript:void(0)" onclick="openDeliveryOrderProfile({{ $r->id }})">
                                                    <span>👁</span>
                                                    <span>View Order Profile</span>
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item small d-flex align-items-center gap-2" href="javascript:void(0)" onclick="window.print()">
                                                    <span>📄</span>
                                                    <span>Print Delivery Slip</span>
                                                </a>
                                            </li>
                                            @if(!in_array($r->status, ['dispatched', 'in_transit', 'delivered', 'completed', 'cancelled']))
                                                <li>
                                                    <a class="dropdown-item small d-flex align-items-center gap-2" href="javascript:void(0)" onclick="openDeliveryOrderProfile({{ $r->id }})">
                                                        <span>🚛</span>
                                                        <span>Assign Driver & Vehicle</span>
                                                    </a>
                                                </li>
                                            @endif
                                            @if(in_array($r->status, ['driver_vehicle_assigned', 'assigned', 'ready_for_dispatch']))
                                                <li>
                                                    <a class="dropdown-item small text-success fw-bold d-flex align-items-center gap-2" href="javascript:void(0)" onclick="openDeliveryOrderProfile({{ $r->id }})">
                                                        <span>✅</span>
                                                        <span>Confirm Dispatch</span>
                                                    </a>
                                                </li>
                                            @endif
                                            @if(in_array($r->status, ['dispatched', 'in_transit']))
                                                <li>
                                                    <a class="dropdown-item small text-danger d-flex align-items-center gap-2" href="javascript:void(0)" onclick="openCancelDispatchModal({{ $r->id }}, '{{ $r->order_reference }}', '{{ $r->dispatch_number }}')">
                                                        <span>🚫</span>
                                                        <span>Cancel Dispatch</span>
                                                    </a>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- SERVER-SIDE PAGINATION ROW -->
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
