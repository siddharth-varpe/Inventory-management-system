@extends('layouts.app')

@section('title', 'Branch Management - StockManager ERP')

@section('header', 'Enterprise Branches')
@section('subheader', 'Manage company branch locations, branch codes, managers, and contacts.')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Branches</li>
@endsection

@section('header-actions')
    <button type="button" class="btn btn-primary btn-sm rounded-3 d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#createBranchModal">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/>
        </svg>
        <span>Add Branch</span>
    </button>
@endsection

@section('content')
<div class="card p-3 mb-4">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
        <x-search-bar placeholder="Search branch code or name..." />
    </div>

    @if($branches->isEmpty())
        <x-empty-state title="No Branches Configured" message="Create your first organization branch location." actionText="Add Branch" actionUrl="#" />
    @else
        <x-data-table :headers="['Branch Code', 'Branch Name', 'Manager', 'Phone', 'City / Country', 'Status', 'Actions']">
            @foreach($branches as $branch)
            <tr>
                <td class="fw-semibold"><code>{{ $branch->code }}</code></td>
                <td class="fw-bold text-body">{{ $branch->name }}</td>
                <td class="text-muted">{{ $branch->manager_name ?? 'N/A' }}</td>
                <td class="text-muted">{{ $branch->phone ?? 'N/A' }}</td>
                <td class="text-muted">{{ $branch->city ? $branch->city.', '.$branch->country : $branch->country }}</td>
                <td><x-status-badge :status="$branch->status" /></td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editBranchModal{{ $branch->id }}">Edit</button>
                        <form action="{{ route('branches.destroy', $branch) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this branch?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>

            <!-- Edit Branch Modal -->
            <div class="modal fade" id="editBranchModal{{ $branch->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content border-translucent shadow">
                        <div class="modal-header border-bottom border-translucent">
                            <h5 class="modal-title fw-bold text-body">Edit Branch: {{ $branch->name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{ route('branches.update', $branch) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-body py-3">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small">Branch Name *</label>
                                        <input type="text" name="name" class="form-control" value="{{ $branch->name }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small">Branch Code *</label>
                                        <input type="text" name="code" class="form-control" value="{{ $branch->code }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small">Manager Name</label>
                                        <input type="text" name="manager_name" class="form-control" value="{{ $branch->manager_name }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small">Status *</label>
                                        <select name="status" class="form-select">
                                            <option value="active" {{ $branch->status == 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="inactive" {{ $branch->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
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
            {{ $branches->links() }}
        </div>
    @endif
</div>

<!-- Create Branch Modal -->
<div class="modal fade" id="createBranchModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-translucent shadow">
            <div class="modal-header border-bottom border-translucent">
                <h5 class="modal-title fw-bold text-body">Create New Branch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('branches.store') }}" method="POST">
                @csrf
                <div class="modal-body py-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Branch Name *</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Head Office Branch" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Branch Code *</label>
                            <input type="text" name="code" class="form-control" placeholder="e.g. BR-HQ-01" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Manager Name</label>
                            <input type="text" name="manager_name" class="form-control" placeholder="John Manager">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Phone</label>
                            <input type="text" name="phone" class="form-control" placeholder="+1 555-0192">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="branch@company.com">
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
                    <button type="submit" class="btn btn-primary btn-sm fw-semibold">Create Branch</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
