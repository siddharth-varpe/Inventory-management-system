@extends('layouts.app')

@section('title', 'Department Management - StockManager ERP')

@section('header', 'Enterprise Departments')
@section('subheader', 'Manage organizational departments (Inventory, Warehouse, Sales, Purchasing, Accounts, HR, etc.).')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Departments</li>
@endsection

@section('header-actions')
    <button type="button" class="btn btn-primary btn-sm rounded-3 d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#createDepartmentModal">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/>
        </svg>
        <span>Add Department</span>
    </button>
@endsection

@section('content')
<div class="card p-3 mb-4">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
        <x-search-bar placeholder="Search department name or code..." />
    </div>

    @if($departments->isEmpty())
        <x-empty-state title="No Departments Found" message="Define organizational units for your enterprise." actionText="Add Department" actionUrl="#" />
    @else
        <x-data-table :headers="['Dept Code', 'Department Name', 'Description', 'Manager', 'Status', 'Actions']">
            @foreach($departments as $dept)
            <tr>
                <td class="fw-semibold"><code>{{ $dept->code }}</code></td>
                <td class="fw-bold text-body">{{ $dept->name }}</td>
                <td class="text-muted small">{{ Str::limit($dept->description ?? 'N/A', 40) }}</td>
                <td class="text-muted">{{ $dept->manager_name ?? 'N/A' }}</td>
                <td><x-status-badge :status="$dept->status" /></td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editDepartmentModal{{ $dept->id }}">Edit</button>
                        <form action="{{ route('departments.destroy', $dept) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this department?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>

            <!-- Edit Department Modal -->
            <div class="modal fade" id="editDepartmentModal{{ $dept->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-translucent shadow">
                        <div class="modal-header border-bottom border-translucent">
                            <h5 class="modal-title fw-bold text-body">Edit Department: {{ $dept->name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{ route('departments.update', $dept) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-body py-3">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small">Department Name *</label>
                                    <input type="text" name="name" class="form-control" value="{{ $dept->name }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small">Department Code *</label>
                                    <input type="text" name="code" class="form-control" value="{{ $dept->code }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small">Manager Name</label>
                                    <input type="text" name="manager_name" class="form-control" value="{{ $dept->manager_name }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small">Status *</label>
                                    <select name="status" class="form-select">
                                        <option value="active" {{ $dept->status == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ $dept->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
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
            {{ $departments->links() }}
        </div>
    @endif
</div>

<!-- Create Department Modal -->
<div class="modal fade" id="createDepartmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-translucent shadow">
            <div class="modal-header border-bottom border-translucent">
                <h5 class="modal-title fw-bold text-body">Create New Department</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('departments.store') }}" method="POST">
                @csrf
                <div class="modal-body py-3">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Department Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Warehouse & Inventory" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Department Code *</label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. DEPT-WH" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Manager Name</label>
                        <input type="text" name="manager_name" class="form-control" placeholder="Jane Director">
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
                    <button type="submit" class="btn btn-primary btn-sm fw-semibold">Create Department</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
