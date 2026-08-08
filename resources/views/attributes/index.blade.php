@extends('layouts.app')

@section('title', 'Product Attributes - StockManager ERP')

@section('header', 'Product Attributes')
@section('subheader', 'Define custom specifications (Color, Size, Weight, Voltage, Material, Serial Number).')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('stock.dashboard') }}" class="text-decoration-none">Manage Stock</a></li>
    <li class="breadcrumb-item active" aria-current="page">Product Attributes</li>
@endsection

@section('header-actions')
    <button type="button" class="btn btn-primary btn-sm rounded-3 d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#createAttributeModal">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/>
        </svg>
        <span>Add Attribute</span>
    </button>
@endsection

@section('content')
<div class="card p-3 mb-4">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
        <x-search-bar placeholder="Search attribute code or name..." />
    </div>

    @if($attributes->isEmpty())
        <x-empty-state title="No Attributes Configured" message="Create product attributes (Color, Size, Material, Weight)." actionText="Add Attribute" actionUrl="#" />
    @else
        <x-data-table :headers="['Attr Code', 'Attribute Name', 'Type', 'Options / Values', 'Required', 'Searchable', 'Status', 'Actions']">
            @foreach($attributes as $attr)
            <tr>
                <td class="fw-semibold"><code>{{ $attr->code }}</code></td>
                <td class="fw-bold text-body">{{ $attr->name }}</td>
                <td><span class="badge bg-secondary-subtle text-secondary border rounded-pill">{{ strtoupper($attr->type) }}</span></td>
                <td class="text-muted small">
                    @if(is_array($attr->options) && count($attr->options) > 0)
                        {{ implode(', ', array_slice($attr->options, 0, 3)) }}{{ count($attr->options) > 3 ? '...' : '' }}
                    @else
                        -
                    @endif
                </td>
                <td>
                    @if($attr->is_required)
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">REQUIRED</span>
                    @else
                        <span class="text-muted small">Optional</span>
                    @endif
                </td>
                <td>
                    @if($attr->is_searchable)
                        <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill">YES</span>
                    @else
                        <span class="text-muted small">No</span>
                    @endif
                </td>
                <td><x-status-badge :status="$attr->status" /></td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editAttributeModal{{ $attr->id }}">Edit</button>
                        <form action="{{ route('attributes.destroy', $attr) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this attribute?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>

            <!-- Edit Attribute Modal -->
            <div class="modal fade" id="editAttributeModal{{ $attr->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content border-translucent shadow">
                        <div class="modal-header border-bottom border-translucent">
                            <h5 class="modal-title fw-bold text-body">Edit Attribute: {{ $attr->name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{ route('attributes.update', $attr) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-body py-3">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small">Attribute Name *</label>
                                        <input type="text" name="name" class="form-control" value="{{ $attr->name }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small">Attribute Code *</label>
                                        <input type="text" name="code" class="form-control" value="{{ $attr->code }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small">Attribute Type *</label>
                                        <select name="type" class="form-select">
                                            <option value="text" {{ $attr->type == 'text' ? 'selected' : '' }}>Text</option>
                                            <option value="number" {{ $attr->type == 'number' ? 'selected' : '' }}>Number</option>
                                            <option value="decimal" {{ $attr->type == 'decimal' ? 'selected' : '' }}>Decimal</option>
                                            <option value="date" {{ $attr->type == 'date' ? 'selected' : '' }}>Date</option>
                                            <option value="boolean" {{ $attr->type == 'boolean' ? 'selected' : '' }}>Boolean (Yes/No)</option>
                                            <option value="dropdown" {{ $attr->type == 'dropdown' ? 'selected' : '' }}>Dropdown</option>
                                            <option value="multi_select" {{ $attr->type == 'multi_select' ? 'selected' : '' }}>Multi Select</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small">Options (Comma Separated)</label>
                                        <input type="text" name="options" class="form-control" value="{{ is_array($attr->options) ? implode(', ', $attr->options) : '' }}" placeholder="Red, Blue, Green">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small">Status *</label>
                                        <select name="status" class="form-select">
                                            <option value="active" {{ $attr->status == 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="inactive" {{ $attr->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
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
            {{ $attributes->links() }}
        </div>
    @endif
</div>

<!-- Create Attribute Modal -->
<div class="modal fade" id="createAttributeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-translucent shadow">
            <div class="modal-header border-bottom border-translucent">
                <h5 class="modal-title fw-bold text-body">Create New Attribute</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('attributes.store') }}" method="POST">
                @csrf
                <div class="modal-body py-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Attribute Name *</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Color, Size, Material" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Attribute Code *</label>
                            <input type="text" name="code" class="form-control" placeholder="e.g. ATTR-COLOR" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Attribute Type *</label>
                            <select name="type" class="form-select">
                                <option value="text" selected>Text</option>
                                <option value="number">Number</option>
                                <option value="decimal">Decimal</option>
                                <option value="date">Date</option>
                                <option value="boolean">Boolean (Yes/No)</option>
                                <option value="dropdown">Dropdown</option>
                                <option value="multi_select">Multi Select</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Options (For Dropdown / Multi Select)</label>
                            <input type="text" name="options" class="form-control" placeholder="e.g. Red, Blue, Green, Yellow">
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
                    <button type="submit" class="btn btn-primary btn-sm fw-semibold">Create Attribute</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
