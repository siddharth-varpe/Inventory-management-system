@extends('layouts.app')

@section('title', 'Put-Away Storage Requests - Organize Stock')

@section('header', 'Storage Requests (Put-Away)')
@section('subheader', 'Assign warehouse coordinates to received stock and complete physical put-away storage.')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('organize.dashboard') }}" class="text-decoration-none">Organize Stock</a></li>
    <li class="breadcrumb-item active" aria-current="page">Put-Away</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Left Column: Sidebar -->
    <div class="col-12 col-lg-3">
        <x-organize-sidebar />
    </div>

    <!-- Right Column: Master-Detail Workspace -->
    <div class="col-12 col-lg-9">
        <x-master-detail-layout queueTitle="Storage Requests Queue" queueSubtitle="Showing pending put-away tasks">
            <x-slot:queueContent>
                @if($requests->isEmpty())
                    <div class="text-center text-muted py-4">No pending put-away requests found.</div>
                @else
                    @foreach($requests as $req)
                        <x-task-card 
                            :title="$req->product->name ?? 'Product Request'"
                            :subtitle="'Ref: ' . $req->request_number . ' | Qty: ' . $req->quantity"
                            :priority="$req->priority"
                            :status="$req->status"
                            :active="($selectedRequest->id ?? 0) === $req->id"
                            :url="route('organize.putaway.index', ['request_id' => $req->id])"
                        />
                    @endforeach
                @endif
            </x-slot:queueContent>

            <x-slot:canvasContent>
                @if($selectedRequest)
                    <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
                        <div>
                            <span class="badge bg-primary-subtle text-primary mb-1">Put-Away Request #{{ $selectedRequest->request_number }}</span>
                            <h4 class="fw-bold text-body mb-0">{{ $selectedRequest->product->name ?? 'N/A' }}</h4>
                            <code class="small text-muted">SKU: {{ $selectedRequest->product->sku ?? 'N/A' }}</code>
                        </div>
                        <x-priority-badge :priority="$selectedRequest->priority" />
                    </div>

                    <!-- Request Summary -->
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-sm-4">
                            <div class="p-3 rounded-3 bg-body-tertiary border text-center">
                                <div class="text-muted small">Quantity to Store</div>
                                <div class="fs-4 fw-bold text-primary">{{ $selectedRequest->quantity }} Units</div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-4">
                            <div class="p-3 rounded-3 bg-body-tertiary border text-center">
                                <div class="text-muted small">Batch Lot Number</div>
                                <div class="fs-5 fw-bold font-monospace text-body">{{ $selectedRequest->batch_number ?? 'DEFAULT' }}</div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-4">
                            <div class="p-3 rounded-3 bg-body-tertiary border text-center">
                                <div class="text-muted small">Request Timestamp</div>
                                <div class="fw-bold text-body mt-1">{{ $selectedRequest->created_at->format('Y-m-d H:i') }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Coordinate Assignment Form -->
                    <div class="card p-4 rounded-4 border bg-body">
                        <h6 class="fw-bold text-body mb-3">Assign Warehouse 5-Tier Coordinates</h6>
                        
                        <form action="{{ route('organize.putaway.assign', $selectedRequest->id) }}" method="POST">
                            @csrf
                            
                            <div class="mb-4">
                                <x-location-selector :warehouses="$warehouses" />
                            </div>

                            <div class="p-3 rounded-3 bg-primary-subtle text-primary border border-primary-subtle mb-4">
                                <div class="fw-bold small mb-1">Generated Location Coordinate String:</div>
                                <code class="fw-bold fs-6 text-primary" id="coordPreview">Main Depot / Rack A-01 / Shelf 1 / Bin 01</code>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-success rounded-3 px-4 py-2 fw-bold">Confirm Physical Storage & Complete Request</button>
                            </div>
                        </form>
                    </div>
                @else
                    <div class="text-center text-muted py-5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-inbox-fill text-muted mb-2" viewBox="0 0 16 16"><path d="M4.98 4a.5.5 0 0 0-.39.188L1.54 8H6a.5.5 0 0 1 .5.5 1.5 1.5 0 1 0 3 0A.5.5 0 0 1 10 8h4.46l-3.05-3.812A.5.5 0 0 0 11.02 4zm9.954 5H10.45a2.5 2.5 0 0 1-4.9 0H1.066l.32 2.562a.5.5 0 0 0 .497.438h12.234a.5.5 0 0 0 .496-.438z"/></svg>
                        <p class="mb-0">Select a pending put-away storage request from the left queue panel to assign coordinates.</p>
                    </div>
                @endif
            </x-slot:canvasContent>
        </x-master-detail-layout>
    </div>
</div>
@endsection
