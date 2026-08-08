@extends('layouts.app')

@section('title', 'Units of Measurement - StockManager ERP')

@section('header', 'Units of Measurement (UOM)')
@section('subheader', 'Configure inventory measurement units, short codes, symbols, and decimal precision.')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('stock.dashboard') }}" class="text-decoration-none">Manage Stock</a></li>
    <li class="breadcrumb-item active" aria-current="page">Units of Measurement</li>
@endsection

@section('header-actions')
    <button type="button" class="btn btn-primary btn-sm rounded-3 d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#createUnitModal">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/>
        </svg>
        <span>Add Unit</span>
    </button>
@endsection

@section('content')
<div class="card p-3 mb-4">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
        <x-search-bar placeholder="Search unit name or short code..." />
    </div>

    @if($units->isEmpty())
        <x-empty-state title="No Units Configured" message="Define measurement units (Piece, Kg, Box, Litre, Meter)." actionText="Add Unit" actionUrl="#" />
    @else
        <x-data-table :headers="['Unit Name', 'Short Name', 'Symbol', 'Decimal Precision', 'Default Unit', 'Status', 'Actions']">
            @foreach($units as $unit)
            <tr>
                <td class="fw-bold text-body">{{ $unit->name }}</td>
                <td class="fw-semibold"><code>{{ $unit->short_name }}</code></td>
                <td><span class="badge bg-secondary-subtle text-secondary border rounded">{{ $unit->symbol ?? '-' }}</span></td>
                <td>{{ $unit->decimal_precision }} Decimals</td>
                <td>
                    @if($unit->is_default)
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">DEFAULT</span>
                    @else
                        <span class="text-muted small">Standard</span>
                    @endif
                </td>
                <td><x-status-badge :status="$unit->status" /></td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editUnitModal{{ $unit->id }}">Edit</button>
                        <form action="{{ route('units.destroy', $unit) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this unit?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>

            <!-- Edit Unit Modal -->
            <div class="modal fade" id="editUnitModal{{ $unit->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-translucent shadow">
                        <div class="modal-header border-bottom border-translucent">
                            <h5 class="modal-title fw-bold text-body">Edit Unit: {{ $unit->name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{ route('units.update', $unit) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-body py-3">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small">Unit Name *</label>
                                    <input type="text" name="name" class="form-control" value="{{ $unit->name }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small">Short Name *</label>
                                    <input type="text" name="short_name" class="form-control" value="{{ $unit->short_name }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small">Symbol</label>
                                    <input type="text" name="symbol" class="form-control" value="{{ $unit->symbol }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small">Decimal Precision *</label>
                                    <input type="number" name="decimal_precision" class="form-control" value="{{ $unit->decimal_precision }}" min="0" max="6" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small">Status *</label>
                                    <select name="status" class="form-select">
                                        <option value="active" {{ $unit->status == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ $unit->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
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
            {{ $units->links() }}
        </div>
    @endif
</div>

<!-- Create Unit Modal -->
<div class="modal fade" id="createUnitModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-translucent shadow">
            <div class="modal-header border-bottom border-translucent">
                <h5 class="modal-title fw-bold text-body">Create New Unit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('units.store') }}" method="POST">
                @csrf
                <div class="modal-body py-3">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Unit Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Kilogram" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Short Name *</label>
                        <input type="text" name="short_name" class="form-control" placeholder="e.g. kg" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Symbol</label>
                        <input type="text" name="symbol" class="form-control" placeholder="e.g. KG">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Decimal Precision *</label>
                        <input type="number" name="decimal_precision" class="form-control" value="2" min="0" max="6" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Status *</label>
                        <select name="status" class="form-select">
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top border-translucent">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-semibold">Create Unit</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
