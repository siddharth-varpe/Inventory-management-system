@extends('layouts.app')

@section('title', 'Manage Stock Portal - Product Catalog - StockManager ERP')

@section('header', 'Manage Stock Workspace')
@section('subheader', 'Single Source of Truth for Product Catalog, Master Stock Execution & Live Inventory Balances.')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Manage Stock</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Left Vertical Side Panel (Manage Stock Only) -->
    <div class="col-12 col-lg-3">
        <x-stock-sidebar />
    </div>

    <!-- Right Catalog Area -->
    <div class="col-12 col-lg-9">
        <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                <div>
                    <h5 class="fw-bold text-body mb-1">Master Product Catalog</h5>
                    <p class="text-muted small mb-0">Showing {{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }} of {{ $products->total() }} total catalog records</p>
                </div>
            </div>

            <!-- Filters Bar -->
            <form method="GET" action="{{ route('stock.catalog') }}" class="row g-2 mb-4">
                <div class="col-12 col-md-3">
                    <input type="text" name="search" class="form-control rounded-3" placeholder="Search SKU, Name, Barcode..." value="{{ $filters['search'] ?? '' }}">
                </div>
                <div class="col-12 col-md-2">
                    <select name="category_id" class="form-select rounded-3">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ ($filters['category_id'] ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <select name="brand_id" class="form-select rounded-3">
                        <option value="">All Brands</option>
                        @foreach($brands as $b)
                            <option value="{{ $b->id }}" {{ ($filters['brand_id'] ?? '') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <select name="stock_status" class="form-select rounded-3">
                        <option value="">All Stock</option>
                        <option value="in_stock" {{ ($filters['stock_status'] ?? '') == 'in_stock' ? 'selected' : '' }}>In Stock</option>
                        <option value="low" {{ ($filters['stock_status'] ?? '') == 'low' ? 'selected' : '' }}>Low Stock</option>
                        <option value="out_of_stock" {{ ($filters['stock_status'] ?? '') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                    </select>
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-secondary rounded-3 w-100 fw-semibold">Filter Catalog</button>
                    <a href="{{ route('stock.catalog') }}" class="btn btn-outline-secondary rounded-3">Reset</a>
                </div>
            </form>

            <!-- Table -->
            @if($products->isEmpty())
                <x-empty-state title="No Products Found" message="No products match your active search or filter criteria." />
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-3">
                        <thead>
                            <tr class="text-muted small">
                                <th>Image</th>
                                <th>SKU / Code</th>
                                <th>Name</th>
                                <th>Category / Brand</th>
                                <th>Warehouse Location</th>
                                <th class="text-center">Physical</th>
                                <th class="text-center">Reserved</th>
                                <th class="text-center">Available</th>
                                <th>Cost Price</th>
                                <th>Selling Price</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                            <tr>
                                <td>
                                    @if($product->image_url)
                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="rounded-3 border" style="width: 40px; height: 40px; object-fit: cover;">
                                    @else
                                        <div class="bg-body-secondary rounded-3 d-flex align-items-center justify-content-center border" style="width: 40px; height: 40px;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-box-seam text-muted" viewBox="0 0 16 16"><path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2zm3.564 1.426L5.596 5 8 5.961 14.154 3.5zm3.25 1.7-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.838L1 4.239v7.923l6.5 2.6zM7.443.184a1.5 1.5 0 0 1 1.114 0l7.129 2.852A.5.5 0 0 1 16 3.5v8.662a1.5 1.5 0 0 1-.901 1.37l-7 2.8a1.5 1.5 0 0 1-1.198 0l-7-2.8A1.5 1.5 0 0 1 0 12.162V3.5a.5.5 0 0 1 .314-.464L7.443.184z"/></svg>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('products.show', $product) }}" class="fw-bold text-decoration-none text-body"><code>{{ $product->sku }}</code></a>
                                    <div class="text-muted small" style="font-size: 0.75rem;">{{ $product->code }}</div>
                                </td>
                                <td>
                                    <a href="{{ route('products.show', $product) }}" class="fw-bold text-decoration-none text-body">{{ $product->name }}</a>
                                </td>
                                <td>
                                    <div class="small fw-semibold">{{ $product->category->name ?? 'Uncategorized' }}</div>
                                    <div class="text-muted small" style="font-size: 0.75rem;">{{ $product->brand->name ?? 'Generic' }}</div>
                                </td>
                                <td>
                                    <div class="small fw-semibold">{{ $product->warehouse_location ?? 'Main Warehouse' }}</div>
                                    <div class="text-muted small" style="font-size: 0.75rem;">{{ $product->rack_location ?? 'N/A' }}</div>
                                </td>
                                <td class="text-center fw-bold">{{ $product->physical_stock }}</td>
                                <td class="text-center text-muted">{{ $product->reserved_stock }}</td>
                                <td class="text-center">
                                    @if($product->available_stock <= 0)
                                        <span class="badge bg-danger-subtle text-danger">0 Out</span>
                                    @elseif($product->available_stock <= $product->reorder_level)
                                        <span class="badge bg-warning-subtle text-warning-emphasis">{{ $product->available_stock }} Low</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success">{{ $product->available_stock }}</span>
                                    @endif
                                </td>
                                <td class="fw-semibold">₹{{ number_format((float)$product->cost_price, 2) }}</td>
                                <td class="fw-bold text-success">₹{{ number_format((float)$product->selling_price, 2) }}</td>
                                <td>
                                    @if($product->status === 'active')
                                        <span class="badge bg-success-subtle text-success">Active</span>
                                    @elseif($product->status === 'archived')
                                        <span class="badge bg-secondary-subtle text-secondary">Archived</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button class="btn btn-light btn-sm rounded-circle p-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-three-dots-vertical" viewBox="0 0 16 16"><path d="M9.5 13a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0"/></svg>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-translucent">
                                            <li><a class="dropdown-item small" href="{{ route('products.show', $product) }}">View Details</a></li>
                                            <li><a class="dropdown-item small" href="{{ route('products.edit', $product) }}">Edit Product</a></li>
                                            <li>
                                                <form action="{{ route('products.duplicate', $product) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item small">Duplicate Product</button>
                                                </form>
                                            </li>
                                            @if($product->status === 'active')
                                                <li>
                                                    <form action="{{ route('products.archive', $product) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item small text-warning">Archive Product</button>
                                                    </form>
                                                </li>
                                            @else
                                                <li>
                                                    <form action="{{ route('products.restore', $product->id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item small text-success">Restore Product</button>
                                                    </form>
                                                </li>
                                            @endif
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('products.destroy', $product) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item small text-danger">Delete Product</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted small">
                        Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} results
                    </div>
                    <div>
                        {{ $products->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
