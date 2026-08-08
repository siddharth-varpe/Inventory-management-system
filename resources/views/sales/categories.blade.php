@extends('layouts.app')

@section('title', 'Customer Categories - Sales & CRM Portal')

@section('content')
<div class="d-flex gap-4">
    <x-sales-sidebar activeTab="categories" />

    <div class="flex-grow-1">
        <div class="card p-4 rounded-4 shadow-sm border-translucent mb-4 bg-body">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h3 class="fw-bold text-body mb-0">Customer Categories</h3>
                    <p class="text-muted small mb-0 mt-1">Tier Accounts by Service Level (Premium, Key Account, Standard, Wholesale, Strategic)</p>
                </div>
                <button type="button" class="btn btn-warning rounded-3 fw-bold text-dark" data-bs-toggle="modal" data-bs-target="#createCategoryModal">+ New Category</button>
            </div>
        </div>

        <div class="row g-3">
            @forelse($categories as $c)
                <div class="col-md-4">
                    <div class="card p-3 rounded-4 border-translucent shadow-sm bg-body h-100">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge bg-info fw-mono">{{ $c->code }}</span>
                            <span class="badge bg-info-subtle text-info rounded-pill">{{ $c->customers_count }} Accounts</span>
                        </div>
                        <h5 class="fw-bold text-body mb-1">{{ $c->name }}</h5>
                        <p class="small text-muted mb-0">{{ $c->description ?? 'No description provided.' }}</p>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5 text-muted">No Customer Categories registered. Click "+ New Category" to configure.</div>
            @endforelse
        </div>
    </div>
</div>

<div class="modal fade" id="createCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-translucent">
            <form method="POST" action="{{ route('sales.categories.store') }}">
                @csrf
                <div class="modal-header border-bottom p-3"><h6 class="modal-title fw-bold">Register Customer Category</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body p-3">
                    <div class="mb-2"><label class="form-label small fw-bold">Category Name</label><input type="text" name="name" class="form-control" required placeholder="e.g. Key Account"></div>
                    <div class="mb-2"><label class="form-label small fw-bold">Category Code</label><input type="text" name="code" class="form-control" required placeholder="e.g. CAT-KEY"></div>
                    <div class="mb-2"><label class="form-label small fw-bold">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                </div>
                <div class="modal-footer p-3"><button type="submit" class="btn btn-warning rounded-3 fw-bold text-dark">Save Category</button></div>
            </form>
        </div>
    </div>
</div>
@endsection
