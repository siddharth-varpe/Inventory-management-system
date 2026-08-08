@extends('layouts.app')

@section('title', 'Customer Groups - Sales & CRM Portal')

@section('content')
<div class="d-flex gap-4">
    <x-sales-sidebar activeTab="groups" />

    <div class="flex-grow-1">
        <div class="card p-4 rounded-4 shadow-sm border-translucent mb-4 bg-body">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h3 class="fw-bold text-body mb-0">Customer Groups Configuration</h3>
                    <p class="text-muted small mb-0 mt-1">Categorize B2B Accounts by Commercial Group (Retail, Dealer, Distributor, Corporate, Export)</p>
                </div>
                <button type="button" class="btn btn-warning rounded-3 fw-bold text-dark" data-bs-toggle="modal" data-bs-target="#createGroupModal">+ New Group</button>
            </div>
        </div>

        <div class="row g-3">
            @forelse($groups as $g)
                <div class="col-md-4">
                    <div class="card p-3 rounded-4 border-translucent shadow-sm bg-body h-100">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge bg-primary fw-mono">{{ $g->code }}</span>
                            <span class="badge bg-primary-subtle text-primary rounded-pill">{{ $g->customers_count }} Accounts</span>
                        </div>
                        <h5 class="fw-bold text-body mb-1">{{ $g->name }}</h5>
                        <p class="small text-muted mb-0">{{ $g->description ?? 'No description provided.' }}</p>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5 text-muted">No Customer Groups registered. Click "+ New Group" to configure your first group.</div>
            @endforelse
        </div>
    </div>
</div>

<div class="modal fade" id="createGroupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-translucent">
            <form method="POST" action="{{ route('sales.groups.store') }}">
                @csrf
                <div class="modal-header border-bottom p-3"><h6 class="modal-title fw-bold">Register Customer Group</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body p-3">
                    <div class="mb-2"><label class="form-label small fw-bold">Group Name</label><input type="text" name="name" class="form-control" required placeholder="e.g. Distributors"></div>
                    <div class="mb-2"><label class="form-label small fw-bold">Group Code</label><input type="text" name="code" class="form-control" required placeholder="e.g. GRP-DIST"></div>
                    <div class="mb-2"><label class="form-label small fw-bold">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                </div>
                <div class="modal-footer p-3"><button type="submit" class="btn btn-warning rounded-3 fw-bold text-dark">Save Group</button></div>
            </form>
        </div>
    </div>
</div>
@endsection
