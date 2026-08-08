@extends('layouts.app')

@section('title', 'Product Categories - StockManager ERP')

@section('header', 'Product Categories')
@section('subheader', 'Manage product taxonomy hierarchy and nested category structures.')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('stock.dashboard') }}" class="text-decoration-none">Manage Stock</a></li>
    <li class="breadcrumb-item active" aria-current="page">Categories</li>
@endsection

@section('header-actions')
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('categories.tree') }}" class="btn btn-outline-primary btn-sm rounded-3">Tree View</a>
        <button type="button" class="btn btn-primary btn-sm rounded-3 d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/>
            </svg>
            <span>Add Category</span>
        </button>
    </div>
@endsection

@section('content')
<div class="card p-3 mb-4">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
        <x-search-bar placeholder="Search category code or name..." />
    </div>

    @if($categories->isEmpty())
        <x-empty-state title="No Categories Found" message="Define product categories to organize your inventory." actionText="Add Category" actionUrl="#" />
    @else
        <x-data-table :headers="['Category Code', 'Category Name', 'Parent Category', 'Display Order', 'Status', 'Actions']">
            @foreach($categories as $category)
            <tr>
                <td class="fw-semibold"><code>{{ $category->code }}</code></td>
                <td class="fw-bold text-body">{{ $category->name }}</td>
                <td class="text-muted">{{ $category->parent->name ?? 'Root Category' }}</td>
                <td><span class="badge bg-secondary-subtle text-secondary border rounded-pill">{{ $category->display_order }}</span></td>
                <td><x-status-badge :status="$category->status" /></td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editCategoryModal{{ $category->id }}">Edit</button>
                        <form action="{{ route('categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this category?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>

            <!-- Edit Category Modal -->
            <div class="modal fade" id="editCategoryModal{{ $category->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-translucent shadow">
                        <div class="modal-header border-bottom border-translucent">
                            <h5 class="modal-title fw-bold text-body">Edit Category: {{ $category->name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{ route('categories.update', $category) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-body py-3">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small">Category Name *</label>
                                    <input type="text" name="name" class="form-control" value="{{ $category->name }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small">Category Code *</label>
                                    <input type="text" name="code" class="form-control" value="{{ $category->code }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small">Parent Category</label>
                                    <select name="parent_id" class="form-select">
                                        <option value="">-- None (Root Level) --</option>
                                        @foreach($allCategories as $parentCandidate)
                                            @if($parentCandidate->id !== $category->id)
                                                <option value="{{ $parentCandidate->id }}" {{ $category->parent_id == $parentCandidate->id ? 'selected' : '' }}>
                                                    {{ $parentCandidate->name }} ({{ $parentCandidate->code }})
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small">Display Order</label>
                                    <input type="number" name="display_order" class="form-control" value="{{ $category->display_order }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small">Status *</label>
                                    <select name="status" class="form-select">
                                        <option value="active" {{ $category->status == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ $category->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
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
            {{ $categories->links() }}
        </div>
    @endif
</div>

<!-- Create Category Modal -->
<div class="modal fade" id="createCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-translucent shadow">
            <div class="modal-header border-bottom border-translucent">
                <h5 class="modal-title fw-bold text-body">Create New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('categories.store') }}" method="POST">
                @csrf
                <div class="modal-body py-3">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Category Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Consumer Electronics" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Category Code (Optional)</label>
                        <input type="text" name="code" class="form-control" placeholder="Auto-generated if left blank">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Parent Category</label>
                        <select name="parent_id" class="form-select">
                            <option value="" selected>-- None (Root Level Category) --</option>
                            @foreach($allCategories as $parentCandidate)
                                <option value="{{ $parentCandidate->id }}">{{ $parentCandidate->name }} ({{ $parentCandidate->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Display Order</label>
                        <input type="number" name="display_order" class="form-control" value="0">
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
                    <button type="submit" class="btn btn-primary btn-sm fw-semibold">Create Category</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
