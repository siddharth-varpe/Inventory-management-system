@extends('layouts.app')

@section('title', 'GST & Tax Configuration - StockManager ERP')

@section('header', 'GST & Tax Configuration')
@section('subheader', 'Configure tax rates, GST, CGST, SGST, IGST, CESS rules, and effective validity periods.')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('stock.dashboard') }}" class="text-decoration-none">Manage Stock</a></li>
    <li class="breadcrumb-item active" aria-current="page">Tax Configuration</li>
@endsection

@section('header-actions')
    <button type="button" class="btn btn-primary btn-sm rounded-3 d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#createTaxModal">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/>
        </svg>
        <span>Add Tax Rule</span>
    </button>
@endsection

@section('content')
<div class="card p-3 mb-4">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
        <x-search-bar placeholder="Search tax code, name, or type..." />
    </div>

    @if($taxes->isEmpty())
        <x-empty-state title="No Tax Rules Found" message="Create GST, CGST, SGST, IGST, or CESS tax rate configurations." actionText="Add Tax Rule" actionUrl="#" />
    @else
        <x-data-table :headers="['Tax Code', 'Tax Name', 'Type', 'Rate %', 'Effective Dates', 'Status', 'Actions']">
            @foreach($taxes as $tax)
            <tr>
                <td class="fw-semibold"><code>{{ $tax->code }}</code></td>
                <td class="fw-bold text-body">{{ $tax->name }}</td>
                <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">{{ strtoupper($tax->type) }}</span></td>
                <td class="fw-bold text-success">{{ number_format((float)$tax->rate, 2) }}%</td>
                <td class="text-muted small">
                    @if($tax->effective_from || $tax->effective_to)
                        {{ $tax->effective_from ? $tax->effective_from->format('Y-m-d') : 'Immediate' }} &rarr; {{ $tax->effective_to ? $tax->effective_to->format('Y-m-d') : 'Indefinite' }}
                    @else
                        Always Active
                    @endif
                </td>
                <td><x-status-badge :status="$tax->status" /></td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editTaxModal{{ $tax->id }}">Edit</button>
                        <form action="{{ route('taxes.destroy', $tax) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this tax rule?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>

            <!-- Edit Tax Modal -->
            <div class="modal fade" id="editTaxModal{{ $tax->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content border-translucent shadow">
                        <div class="modal-header border-bottom border-translucent">
                            <h5 class="modal-title fw-bold text-body">Edit Tax Rule: {{ $tax->name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{ route('taxes.update', $tax) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-body py-3">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small">Tax Name *</label>
                                        <input type="text" name="name" class="form-control" value="{{ $tax->name }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small">Tax Code *</label>
                                        <input type="text" name="code" class="form-control" value="{{ $tax->code }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small">Tax Type *</label>
                                        <select name="type" class="form-select">
                                            <option value="gst" {{ $tax->type == 'gst' ? 'selected' : '' }}>GST</option>
                                            <option value="cgst" {{ $tax->type == 'cgst' ? 'selected' : '' }}>CGST</option>
                                            <option value="sgst" {{ $tax->type == 'sgst' ? 'selected' : '' }}>SGST</option>
                                            <option value="igst" {{ $tax->type == 'igst' ? 'selected' : '' }}>IGST</option>
                                            <option value="cess" {{ $tax->type == 'cess' ? 'selected' : '' }}>CESS</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small">Tax Rate % *</label>
                                        <input type="number" step="0.01" name="rate" class="form-control" value="{{ $tax->rate }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small">Effective From</label>
                                        <input type="date" name="effective_from" class="form-control" value="{{ $tax->effective_from ? $tax->effective_from->format('Y-m-d') : '' }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small">Effective To</label>
                                        <input type="date" name="effective_to" class="form-control" value="{{ $tax->effective_to ? $tax->effective_to->format('Y-m-d') : '' }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small">Status *</label>
                                        <select name="status" class="form-select">
                                            <option value="active" {{ $tax->status == 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="inactive" {{ $tax->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
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
            {{ $taxes->links() }}
        </div>
    @endif
</div>

<!-- Create Tax Modal -->
<div class="modal fade" id="createTaxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-translucent shadow">
            <div class="modal-header border-bottom border-translucent">
                <h5 class="modal-title fw-bold text-body">Create New Tax Rule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('taxes.store') }}" method="POST">
                @csrf
                <div class="modal-body py-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Tax Name *</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. GST 18%" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Tax Code *</label>
                            <input type="text" name="code" class="form-control" placeholder="e.g. GST-18" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Tax Type *</label>
                            <select name="type" class="form-select">
                                <option value="gst" selected>GST</option>
                                <option value="cgst">CGST</option>
                                <option value="sgst">SGST</option>
                                <option value="igst">IGST</option>
                                <option value="cess">CESS</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Tax Rate % *</label>
                            <input type="number" step="0.01" name="rate" class="form-control" placeholder="18.00" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Effective From</label>
                            <input type="date" name="effective_from" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Effective To</label>
                            <input type="date" name="effective_to" class="form-control">
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
                    <button type="submit" class="btn btn-primary btn-sm fw-semibold">Create Tax Rule</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
