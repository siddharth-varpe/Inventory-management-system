@extends('layouts.app')

@section('title', 'Warehouse Location Manager - Organize Stock')

@section('header', 'Warehouse Facilities & Infrastructure')
@section('subheader', 'Configure 5-tier warehouse facility infrastructure (Warehouse -> Zone -> Aisle -> Rack -> Shelf -> Bin).')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('organize.dashboard') }}" class="text-decoration-none">Organize Stock</a></li>
    <li class="breadcrumb-item active" aria-current="page">Location Manager</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Left Column: Sidebar -->
    <div class="col-12 col-lg-3">
        <x-organize-sidebar />
    </div>

    <!-- Right Column: Location Manager Workspace -->
    <div class="col-12 col-lg-9">
        <!-- Create Warehouse Modal -->
        <div class="modal fade" id="newWarehouseModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 p-2">
                    <form action="{{ route('organize.locations.store-warehouse') }}" method="POST">
                        @csrf
                        <div class="modal-header border-0">
                            <h5 class="modal-title fw-bold">Register New Warehouse Facility</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Warehouse Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control rounded-3" placeholder="e.g. Central Distribution Depot" required>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <label class="form-label fw-semibold">Code <span class="text-danger">*</span></label>
                                    <input type="text" name="code" class="form-control rounded-3" placeholder="WH02" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold">Type</label>
                                    <select name="type" class="form-select rounded-3">
                                        <option value="distribution_center">Distribution Center</option>
                                        <option value="storage">Storage Warehouse</option>
                                        <option value="cold_storage">Cold Storage</option>
                                        <option value="transit">Transit Hub</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <label class="form-label fw-semibold">Capacity</label>
                                    <input type="number" name="total_capacity" class="form-control rounded-3" value="10000" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold">Unit</label>
                                    <select name="capacity_unit" class="form-select rounded-3">
                                        <option value="sqft">Sq. Ft.</option>
                                        <option value="m3">Cubic Meters</option>
                                        <option value="pallets">Pallet Slots</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">City</label>
                                <input type="text" name="city" class="form-control rounded-3" placeholder="e.g. Mumbai">
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary rounded-3 fw-bold">Create Facility</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Add Storage Bin Modal -->
        <div class="modal fade" id="newBinModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 p-2">
                    <form action="{{ route('organize.locations.store-bin') }}" method="POST">
                        @csrf
                        <div class="modal-header border-0">
                            <h5 class="modal-title fw-bold">Add Storage Bin Location</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Select Rack <span class="text-danger">*</span></label>
                                <select name="warehouse_rack_id" class="form-select rounded-3" required>
                                    @foreach($racks as $r)
                                        <option value="{{ $r->id }}">{{ $r->aisle->zone->warehouse->name ?? 'WH' }} &rarr; Zone {{ $r->aisle->zone->code ?? '' }} &rarr; {{ $r->name }} ({{ $r->code }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <label class="form-label fw-semibold">Shelf Number <span class="text-danger">*</span></label>
                                    <input type="text" name="shelf_number" class="form-control rounded-3" value="S02" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold">Bin Number <span class="text-danger">*</span></label>
                                    <input type="text" name="bin_number" class="form-control rounded-3" value="B04" required>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary rounded-3 fw-bold">Create Bin</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Facilities WMS Explorer Component -->
        <x-warehouse-tree :warehouses="$warehouses" />
    </div>
</div>
@endsection
