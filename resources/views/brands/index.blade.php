@extends('layouts.app')

@section('title', 'Brand Management - StockManager ERP')

@section('header', 'Product Brands')
@section('subheader', 'Manage product manufacturers, brand logos, origins, and website references.')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('stock.dashboard') }}" class="text-decoration-none">Manage Stock</a></li>
    <li class="breadcrumb-item active" aria-current="page">Brands</li>
@endsection

@section('header-actions')
    <button type="button" class="btn btn-primary btn-sm rounded-3 d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#createBrandModal">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/>
        </svg>
        <span>Add Brand</span>
    </button>
@endsection

@section('content')
<div class="card p-3 mb-4">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
        <x-search-bar placeholder="Search brand code, name, or manufacturer..." />
    </div>

    @if($brands->isEmpty())
        <x-empty-state title="No Brands Registered" message="Create your first product brand or manufacturer." actionText="Add Brand" actionUrl="#" />
    @else
        <x-data-table :headers="['Logo', 'Brand Code', 'Brand Name', 'Manufacturer', 'Country of Origin', 'Status', 'Actions']">
            @foreach($brands as $brand)
            <tr>
                <td>
                    @if($brand->logo)
                        <img src="{{ Storage::url($brand->logo) }}" alt="Logo" class="rounded border p-1" style="width: 40px; height: 40px; object-fit: contain;">
                    @else
                        <div class="bg-secondary-subtle text-secondary rounded d-flex align-items-center justify-content-center fw-bold small" style="width: 40px; height: 40px;">
                            {{ strtoupper(substr($brand->name, 0, 2)) }}
                        </div>
                    @endif
                </td>
                <td class="fw-semibold"><code>{{ $brand->code }}</code></td>
                <td class="fw-bold text-body">{{ $brand->name }}</td>
                <td class="text-muted">{{ $brand->manufacturer ?? 'N/A' }}</td>
                <td class="text-muted">{{ $brand->country_of_origin ?? 'N/A' }}</td>
                <td><x-status-badge :status="$brand->status" /></td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editBrandModal{{ $brand->id }}">Edit</button>
                        <form action="{{ route('brands.destroy', $brand) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this brand?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>

            <!-- Edit Brand Modal -->
            <div class="modal fade" id="editBrandModal{{ $brand->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content border-translucent shadow">
                        <div class="modal-header border-bottom border-translucent">
                            <h5 class="modal-title fw-bold text-body">Edit Brand: {{ $brand->name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{ route('brands.update', $brand) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="modal-body py-3">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small">Brand Name *</label>
                                        <input type="text" name="name" class="form-control" value="{{ $brand->name }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small">Brand Code *</label>
                                        <input type="text" name="code" class="form-control" value="{{ $brand->code }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small">Manufacturer</label>
                                        <input type="text" name="manufacturer" class="form-control" value="{{ $brand->manufacturer }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small">Country of Origin</label>
                                        <input type="text" name="country_of_origin" class="form-control" value="{{ $brand->country_of_origin }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small">Brand Logo</label>
                                        <input type="file" name="logo" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small">Status *</label>
                                        <select name="status" class="form-select">
                                            <option value="active" {{ $brand->status == 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="inactive" {{ $brand->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer border-top border-translucent">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary btn-sm fw-semibold">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </x-data-table>

        <div class="mt-3">
            {{ $brands->links() }}
        </div>
    @endif
</div>

<!-- Create Brand Modal -->
<div class="modal fade" id="createBrandModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-translucent shadow">
            <div class="modal-header border-bottom border-translucent">
                <h5 class="modal-title fw-bold text-body">Create New Brand</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('brands.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body py-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Brand Name *</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Sony, Samsung, Dell" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Brand Code (Optional)</label>
                            <input type="text" name="code" class="form-control" placeholder="Auto-generated if blank">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Manufacturer</label>
                            <input type="text" name="manufacturer" class="form-control" placeholder="e.g. Sony Corporation">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Country of Origin</label>
                            <input type="text" name="country_of_origin" class="form-control" placeholder="e.g. Japan, South Korea, USA">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Brand Logo</label>
                            <input type="file" name="logo" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Status *</label>
                            <select name="status" class="form-select">
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top border-translucent">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-semibold">Create Brand</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
