@extends('layouts.app')

@section('title', 'Territories & Sales Zones - Sales & CRM Portal')

@section('content')
<div class="d-flex gap-4">
    <x-sales-sidebar activeTab="territories" />

    <div class="flex-grow-1">
        <div class="card p-4 rounded-4 shadow-sm border-translucent mb-4 bg-body">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h3 class="fw-bold text-body mb-0">Territories & Sales Zones</h3>
                    <p class="text-muted small mb-0 mt-1">Geographic Regions, Sales Zones, State/City Mappings & Pin Code Networks</p>
                </div>
                <button type="button" class="btn btn-warning rounded-3 fw-bold text-dark" data-bs-toggle="modal" data-bs-target="#createTerritoryModal">+ New Territory</button>
            </div>
        </div>

        <div class="row g-3">
            @forelse($territories as $t)
                <div class="col-md-4">
                    <div class="card p-3 rounded-4 border-translucent shadow-sm bg-body h-100">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge bg-secondary fw-mono">{{ $t->code }}</span>
                            <span class="badge bg-success-subtle text-success rounded-pill">{{ $t->customers_count }} Accounts</span>
                        </div>
                        <h5 class="fw-bold text-body mb-1">{{ $t->name }}</h5>
                        <div class="small text-muted mb-2">Region: <strong>{{ $t->region }}</strong> | Zone: <strong>{{ $t->sales_zone }}</strong></div>
                        <div class="small text-body-secondary">
                            📍 {{ $t->city ?? 'All Cities' }}, {{ $t->state ?? 'All States' }} ({{ $t->country }})
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5 text-muted">No Territories registered. Click "+ New Territory" to configure.</div>
            @endforelse
        </div>
    </div>
</div>

<div class="modal fade" id="createTerritoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-translucent">
            <form method="POST" action="{{ route('sales.territories.store') }}">
                @csrf
                <div class="modal-header border-bottom p-3"><h6 class="modal-title fw-bold">Register Territory</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body p-3">
                    <div class="mb-2"><label class="form-label small fw-bold">Territory Name</label><input type="text" name="name" class="form-control" required placeholder="e.g. West Coast Metro"></div>
                    <div class="mb-2"><label class="form-label small fw-bold">Territory Code</label><input type="text" name="code" class="form-control" required placeholder="e.g. TER-WCM"></div>
                    <div class="row g-2 mb-2">
                        <div class="col-6"><label class="form-label small fw-bold">Region</label><input type="text" name="region" class="form-control" value="West" required></div>
                        <div class="col-6"><label class="form-label small fw-bold">Sales Zone</label><input type="text" name="sales_zone" class="form-control" value="Zone 1" required></div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6"><label class="form-label small fw-bold">State</label><input type="text" name="state" class="form-control" placeholder="Maharashtra"></div>
                        <div class="col-6"><label class="form-label small fw-bold">City</label><input type="text" name="city" class="form-control" placeholder="Mumbai"></div>
                    </div>
                    <div class="mb-2"><label class="form-label small fw-bold">Country</label><input type="text" name="country" class="form-control" value="India" required></div>
                </div>
                <div class="modal-footer p-3"><button type="submit" class="btn btn-warning rounded-3 fw-bold text-dark">Save Territory</button></div>
            </form>
        </div>
    </div>
</div>
@endsection
