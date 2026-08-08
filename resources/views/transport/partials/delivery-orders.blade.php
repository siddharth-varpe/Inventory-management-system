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
    <div id="deliveryCardsSkeleton" class="vstack gap-3 d-none mb-3">
        @for($i = 0; $i < 3; $i++)
            <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body placeholder-glow">
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

    <!-- DELIVERY CARDS CONTAINER (MATCHING "DELIVERY ORDER CARD - PERFECT LAYOUT" STRICTLY) -->
    <div id="deliveryCardsContainer" class="vstack gap-3">
        @forelse($requests as $r)
            <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body delivery-order-card">
                
                <!-- ZONE 1: ORDER IDENTITY STATUS & READINESS (CARD HEADER) -->
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 pb-3.5 border-bottom border-translucent">
                    
                    <!-- LEFT SIDE: AVATAR, SO NUMBER, STATUS BADGE, PRIORITY BADGE, TRN CODE, SEAL BADGE, TIMESTAMP -->
                    <div class="d-flex align-items-center gap-3">
                        <!-- CIRCLE TRUCK AVATAR (48px x 48px) -->
                        <div class="d-flex align-items-center justify-content-center flex-shrink-0 bg-primary-subtle text-primary rounded-circle" style="width: 48px; height: 48px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-truck" viewBox="0 0 16 16">
                                <path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-4 0H.5A.5.5 0 0 1 0 11.5v-8zM1 3.5v7h.5a2 2 0 0 1 3.163.787 2 2 0 0 1 3.674 0H10.5a2 2 0 0 1 3.163-.787H15V8.28l-1.48-1.85A.5.5 0 0 0 13.15 6H12v4.5a.5.5 0 0 1-1 0V3.5h-10z"/>
                            </svg>
                        </div>

                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                <!-- SO NUMBER (SO-2026-00001) -->
                                <a href="javascript:void(0)" onclick="openDeliveryOrderProfile({{ $r->id }})" class="fw-bold text-primary text-decoration-none fs-4 font-monospace">
                                    {{ $r->order_reference }}
                                </a>

                                <!-- STATUS BADGE (In Transit) -->
                                <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 small fw-bold">
                                    {{ $r->status_label }}
                                </span>

                                <!-- PRIORITY BADGE (NORMAL) -->
                                <span class="badge rounded-pill {{ $r->priority_badge_class }} px-3 py-1.5 small fw-bold">
                                    {{ strtoupper($r->priority ?? 'NORMAL') }}
                                </span>
                            </div>

                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <!-- TRN CODE (TRN-2026-600001) -->
                                <span class="small text-muted font-monospace me-2" style="font-size: 0.825rem;">{{ $r->request_number }}</span>

                                <!-- SEAL BADGE & TIMESTAMP -->
                                @if(!empty($r->warehouse_completed_at) || $r->warehouse_status === 'completed')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small d-inline-flex align-items-center gap-1" style="font-size: 0.75rem; font-weight: 600;">
                                        ✓ Seal & Ready to Dispatch
                                    </span>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small font-monospace" style="font-size: 0.75rem;">
                                        {{ $r->warehouse_completed_at ? $r->warehouse_completed_at->format('d M Y, H:i') : '08 Aug 2026, 09:54' }}
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

                    <!-- RIGHT SIDE OF ZONE 1: CUSTOMER NAME & ADDRESS -->
                    <div class="text-md-end">
                        <!-- CUSTOMER NAME (opstech solution) -->
                        <div class="fw-bold text-body fs-6 mb-0.5">{{ $r->customer_name }}</div>
                        
                        <!-- CUSTOMER ADDRESS WITH RED PIN -->
                        <div class="small text-muted d-flex align-items-center justify-content-md-end gap-1">
                            <span class="text-danger flex-shrink-0">📍</span>
                            <span class="line-clamp-1">{{ $r->delivery_address }} — <strong class="text-body-secondary">{{ $r->city }}</strong></span>
                        </div>
                    </div>
                </div>

                <!-- ZONE 2: CUSTOMER, DESTINATION, PRIORITY & STATUS GRID -->
                <div class="py-3.5 border-bottom border-translucent">
                    <div class="row g-3 align-items-center">
                        <!-- COLUMN 1: CUSTOMER -->
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="d-flex align-items-center gap-1.5 text-muted small font-monospace fw-semibold mb-1" style="font-size: 0.75rem;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16"><path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/></svg>
                                <span>Customer</span>
                            </div>
                            <div class="fw-bold text-body fs-6">{{ $r->customer_name }}</div>
                            <div class="small text-muted font-monospace" style="font-size: 0.78rem;">{{ $r->customer_phone ?? '888888888888' }}</div>
                        </div>

                        <!-- COLUMN 2: DESTINATION -->
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="d-flex align-items-center gap-1.5 text-muted small font-monospace fw-semibold mb-1" style="font-size: 0.75rem;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" class="bi bi-geo-alt" viewBox="0 0 16 16"><path d="M12.166 8.94c-.524 1.062-1.234 2.12-1.96 3.07A31.493 31.493 0 0 1 8 14.58a31.481 31.481 0 0 1-2.206-2.57c-.726-.95-1.436-2.008-1.96-3.07C3.304 7.867 3 6.862 3 6a5 5 0 0 1 10 0c0 .862-.305 1.867-.834 2.94zM8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10z"/><path d="M8 8a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm0 1a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/></svg>
                                <span>Destination</span>
                            </div>
                            <div class="fw-bold text-body fs-6 line-clamp-1">{{ $r->delivery_address }}</div>
                            <div class="small text-muted" style="font-size: 0.78rem;">{{ $r->city }}</div>
                        </div>

                        <!-- COLUMN 3: PRIORITY -->
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="d-flex align-items-center gap-1.5 text-muted small font-monospace fw-semibold mb-1" style="font-size: 0.75rem;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" class="bi bi-flag" viewBox="0 0 16 16"><path d="M14.778.085A.5.5 0 0 1 15 .5V14a.5.5 0 0 1-.854.354l-1.942-1.942-2.14 2.14a.5.5 0 0 1-.707 0L7.425 12.6l-2.14 2.14a.5.5 0 0 1-.707 0L2.636 12.8 1.354 14.08A.5.5 0 0 1 .5 13.725V.5a.5.5 0 0 1 .854-.354l1.942 1.942 2.14-2.14a.5.5 0 0 1 .707 0L8.075 1.9l2.14-2.14a.5.5 0 0 1 .707 0l1.942 1.942 1.914-1.914z"/></svg>
                                <span>Priority</span>
                            </div>
                            <div>
                                <span class="badge rounded-pill {{ $r->priority_badge_class }} px-3 py-1.5 small fw-bold" style="font-size: 0.75rem;">
                                    {{ strtoupper($r->priority ?? 'NORMAL') }}
                                </span>
                            </div>
                        </div>

                        <!-- COLUMN 4: STATUS -->
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="d-flex align-items-center gap-1.5 text-muted small font-monospace fw-semibold mb-1" style="font-size: 0.75rem;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" class="bi bi-graph-up-arrow" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M0 0h1v15h15v1H0V0zm10 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-1 0V4.707l-4.146 4.147a.5.5 0 0 1-.708 0L7 6.707l-4.646 4.647a.5.5 0 0 1-.708-.708l5-5a.5.5 0 0 1 .708 0L9.5 7.293l3.646-3.647H10.5a.5.5 0 0 1-.5-.5z"/></svg>
                                <span>Status</span>
                            </div>
                            <div class="fw-bold text-primary fs-6">
                                {{ $r->status_label }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ZONE 3: ASSIGNMENT & ACTIONS ROW -->
                <div class="pt-3.5 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    
                    <div class="d-flex flex-wrap align-items-center gap-4 flex-grow-1">
                        
                        <!-- COLUMN 1: ASSIGNED DRIVER -->
                        <div class="d-flex align-items-start gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person text-muted mt-1" viewBox="0 0 16 16"><path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/></svg>
                            <div>
                                <div class="text-muted small" style="font-size: 0.75rem;">Assigned Driver</div>
                                @if($r->driver)
                                    <div class="fw-bold text-body small">{{ $r->driver->driver_name }}</div>
                                    <div class="text-muted font-monospace" style="font-size: 0.7rem;">{{ $r->driver->driver_code }}</div>
                                @else
                                    <div class="text-muted small fst-italic">Not Assigned</div>
                                @endif
                            </div>
                        </div>

                        <!-- COLUMN 2: VEHICLE -->
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

                        <!-- COLUMN 3: EXPECTED DELIVERY -->
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

                    <!-- COLUMN 4: PRIMARY ACTION BUTTON & THREE DOTS MENU -->
                    <div class="d-flex align-items-center gap-2 justify-content-end">
                        <!-- PRIMARY ACTION BUTTON -->
                        <button type="button" class="btn btn-primary px-4 py-2 fw-bold rounded-3 shadow-xs" onclick="openDeliveryOrderProfile({{ $r->id }})">
                            @if(in_array($r->status, ['dispatched', 'in_transit']))
                                View Delivery
                            @elseif(in_array($r->status, ['driver_vehicle_assigned', 'assigned']))
                                View Delivery
                            @else
                                View / Assign
                            @endif
                        </button>

                        <!-- THREE-DOTS OPTIONS DROPDOWN MENU (40px CIRCULAR BUTTON) -->
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
        @empty
            <!-- EMPTY STATE CARD (MATCHING DIAGRAM) -->
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
        @endforelse
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
