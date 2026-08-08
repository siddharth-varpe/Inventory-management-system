@extends('layouts.app')

@section('title', 'Edit Product Master - StockManager ERP')

@section('header', 'Edit Product Master')
@section('subheader', 'Update master attributes, pricing, warehouse locations, and classification.')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('stock.dashboard') }}" class="text-decoration-none">Manage Stock</a></li>
    <li class="breadcrumb-item"><a href="{{ route('products.index') }}" class="text-decoration-none">Product Master</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit {{ $product->name }}</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Left Vertical Side Panel -->
    <div class="col-12 col-lg-3">
        <x-stock-sidebar />
    </div>

    <!-- Main Workspace Area -->
    <div class="col-12 col-lg-9">
        <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row g-4">
                <!-- Main Form Area -->
                <div class="col-12 col-xl-8">
                    <!-- 1. Identity Section -->
                    <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body mb-4">
                        <h5 class="fw-bold text-body mb-3">1. Product Identity</h5>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control rounded-3 @error('name') is-invalid @enderror" value="{{ old('name', $product->name) }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Product Code (System Ref)</label>
                                <input type="text" name="code" class="form-control rounded-3" value="{{ old('code', $product->code) }}">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">SKU (Stock Keeping Unit)</label>
                                <input type="text" name="sku" class="form-control rounded-3" value="{{ old('sku', $product->sku) }}">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Barcode (EAN / UPC)</label>
                                <input type="text" name="barcode" class="form-control rounded-3" value="{{ old('barcode', $product->barcode) }}">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">QR Code Identifier</label>
                                <input type="text" name="qr_code" class="form-control rounded-3" value="{{ old('qr_code', $product->qr_code) }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Product Description</label>
                                <textarea name="description" class="form-control rounded-3" rows="3">{{ old('description', $product->description) }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Product Master Image</label>
                                <div class="row align-items-center g-3">
                                    <div class="col-auto">
                                        <div class="p-2 border border-dashed rounded-4 bg-body-tertiary d-flex flex-column align-items-center justify-content-center" style="width: 100px; height: 100px;">
                                            @if($product->image)
                                                <img id="imagePreview" src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="img-fluid rounded-3" style="max-height: 80px; object-fit: contain;">
                                                <div id="imagePlaceholder" class="text-muted text-center d-none">
                                            @else
                                                <img id="imagePreview" src="" alt="Preview" class="img-fluid rounded-3 d-none" style="max-height: 80px; object-fit: contain;">
                                                <div id="imagePlaceholder" class="text-muted text-center">
                                            @endif
                                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-image text-secondary mb-1" viewBox="0 0 16 16"><path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0"/><path d="M2.002 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2h-12zm12 1a1 1 0 0 1 1 1v6.5l-3.777-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062L1.002 12V3a1 1 0 0 1 1-1h12"/></svg>
                                                <div class="small" style="font-size: 0.7rem;">No Image</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <input type="file" name="image" id="product_image_input" class="form-control rounded-3 @error('image') is-invalid @enderror" accept="image/jpeg,image/png,image/webp,image/jpg" onchange="previewProductImage(this)">
                                        <div class="form-text small">Upload new image to replace current picture (JPEG, PNG, WEBP - max 2MB).</div>
                                        @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                    function previewProductImage(input) {
                        const preview = document.getElementById('imagePreview');
                        const placeholder = document.getElementById('imagePlaceholder');
                        if (input.files && input.files[0]) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                preview.src = e.target.result;
                                preview.classList.remove('d-none');
                                if (placeholder) placeholder.classList.add('d-none');
                            }
                            reader.readAsDataURL(input.files[0]);
                        }
                    }
                    </script>

                    <!-- 2. Pricing Tiers -->
                    <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body mb-4">
                        <h5 class="fw-bold text-body mb-3">2. Pricing Structure & Tiers</h5>
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">Purchase Price (₹) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="purchase_price" class="form-control rounded-3" value="{{ old('purchase_price', $product->purchase_price) }}" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">Cost Price (WAC ₹) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="cost_price" class="form-control rounded-3" value="{{ old('cost_price', $product->cost_price) }}" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">Selling Price (Base ₹) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="selling_price" class="form-control rounded-3" value="{{ old('selling_price', $product->selling_price) }}" required>
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label fw-semibold">MRP (Maximum Retail)</label>
                                <input type="number" step="0.01" name="mrp" class="form-control rounded-3" value="{{ old('mrp', $product->mrp) }}">
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label fw-semibold">Dealer Price (₹)</label>
                                <input type="number" step="0.01" name="dealer_price" class="form-control rounded-3" value="{{ old('dealer_price', $product->dealer_price) }}">
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label fw-semibold">Wholesale Price (₹)</label>
                                <input type="number" step="0.01" name="wholesale_price" class="form-control rounded-3" value="{{ old('wholesale_price', $product->wholesale_price) }}">
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label fw-semibold">Min Selling Price (₹)</label>
                                <input type="number" step="0.01" name="min_selling_price" class="form-control rounded-3" value="{{ old('min_selling_price', $product->min_selling_price) }}">
                            </div>
                        </div>
                    </div>

                    <!-- 3. Storage & Location -->
                    <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body mb-4">
                        <h5 class="fw-bold text-body mb-3">3. Storage & Primary Supplier</h5>
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">Warehouse Location</label>
                                <input type="text" name="warehouse_location" class="form-control rounded-3" value="{{ old('warehouse_location', $product->warehouse_location) }}">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">Rack / Shelf Location</label>
                                <input type="text" name="rack_location" class="form-control rounded-3" value="{{ old('rack_location', $product->rack_location) }}">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">Storage Condition</label>
                                <input type="text" name="storage_condition" class="form-control rounded-3" value="{{ old('storage_condition', $product->storage_condition) }}">
                            </div>
                            <div class="col-12 col-md-8">
                                <label class="form-label fw-semibold">Primary Supplier Name</label>
                                <input type="text" name="primary_supplier" class="form-control rounded-3" value="{{ old('primary_supplier', $product->primary_supplier) }}">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">MOQ (Min Order Qty)</label>
                                <input type="number" name="moq" class="form-control rounded-3" value="{{ old('moq', $product->moq) }}" min="1">
                            </div>
                        </div>
                    </div>

                    <!-- 4. Dynamic Attributes -->
                    @if($attributes->isNotEmpty())
                    <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body mb-4">
                        <h5 class="fw-bold text-body mb-3">4. Dynamic Product Attributes</h5>
                        <div class="row g-3">
                            @foreach($attributes as $attr)
                            @php
                                $currentVal = $product->attributeValues->where('attribute_id', $attr->id)->first()->value ?? '';
                            @endphp
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">{{ $attr->name }} {{ $attr->is_required ? '*' : '' }}</label>
                                @if($attr->type === 'select' && is_array($attr->options))
                                    <select name="attribute_values[{{ $attr->id }}]" class="form-select rounded-3">
                                        <option value="">Select {{ $attr->name }}</option>
                                        @foreach($attr->options as $opt)
                                            <option value="{{ $opt }}" {{ $currentVal == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="{{ $attr->type === 'number' ? 'number' : 'text' }}" name="attribute_values[{{ $attr->id }}]" class="form-control rounded-3" value="{{ $currentVal }}">
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Sidebar Options Column -->
                <div class="col-12 col-xl-4">
                    <!-- Classification Card -->
                    <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body mb-4">
                        <h5 class="fw-bold text-body mb-3">Classification</h5>
                        
                        <!-- Category Field with + Button & Search Bar -->
                        <div class="mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label class="form-label fw-semibold mb-0">Category</label>
                                <button type="button" class="btn btn-outline-primary btn-sm rounded-circle px-2 py-0 fw-bold d-inline-flex align-items-center justify-content-center" style="width: 24px; height: 24px;" data-bs-toggle="modal" data-bs-target="#quickAddCategoryModal" title="Add New Category">+</button>
                            </div>
                            <input type="text" class="form-control form-control-sm mb-1 rounded-3" placeholder="🔍 Search Category..." onkeyup="filterSelectOptions(this, 'category_id_select')">
                            <select name="category_id" id="category_id_select" class="form-select rounded-3">
                                <option value="">Select Category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Brand Field with + Button & Search Bar -->
                        <div class="mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label class="form-label fw-semibold mb-0">Brand</label>
                                <button type="button" class="btn btn-outline-primary btn-sm rounded-circle px-2 py-0 fw-bold d-inline-flex align-items-center justify-content-center" style="width: 24px; height: 24px;" data-bs-toggle="modal" data-bs-target="#quickAddBrandModal" title="Add New Brand">+</button>
                            </div>
                            <input type="text" class="form-control form-control-sm mb-1 rounded-3" placeholder="🔍 Search Brand..." onkeyup="filterSelectOptions(this, 'brand_id_select')">
                            <select name="brand_id" id="brand_id_select" class="form-select rounded-3">
                                <option value="">Select Brand</option>
                                @foreach($brands as $b)
                                    <option value="{{ $b->id }}" {{ old('brand_id', $product->brand_id) == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Measurement Unit</label>
                            <select name="unit_id" class="form-select rounded-3">
                                <option value="">Select Unit</option>
                                @foreach($units as $u)
                                    <option value="{{ $u->id }}" {{ old('unit_id', $product->unit_id) == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->short_name }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tax Slab</label>
                            <select name="tax_id" class="form-select rounded-3">
                                <option value="">Select Tax</option>
                                @foreach($taxes as $t)
                                    <option value="{{ $t->id }}" {{ old('tax_id', $product->tax_id) == $t->id ? 'selected' : '' }}>{{ $t->name }} ({{ $t->rate }}%)</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Product Type</label>
                            <select name="product_type" class="form-select rounded-3">
                                <option value="single" {{ old('product_type', $product->product_type) == 'single' ? 'selected' : '' }}>Single Item</option>
                                <option value="variant" {{ old('product_type', $product->product_type) == 'variant' ? 'selected' : '' }}>Variant Item</option>
                                <option value="combo" {{ old('product_type', $product->product_type) == 'combo' ? 'selected' : '' }}>Combo / Bundle</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" class="form-select rounded-3">
                                <option value="active" {{ old('status', $product->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $product->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="discontinued" {{ old('status', $product->status) == 'discontinued' ? 'selected' : '' }}>Discontinued</option>
                            </select>
                        </div>
                    </div>

                    <!-- Inventory Safety Levels -->
                    <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body mb-4">
                        <h5 class="fw-bold text-body mb-3">Inventory Safety Levels</h5>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Physical Stock Count</label>
                            <input type="number" class="form-control rounded-3 bg-body-tertiary" value="{{ $product->physical_stock }}" readonly disabled>
                            <div class="form-text small">Use Receive Stock or Stock Adjustments to mutate count.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Minimum Safety Stock Level</label>
                            <input type="number" name="min_stock" class="form-control rounded-3" value="{{ old('min_stock', $product->min_stock) }}" min="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Reorder Threshold Level</label>
                            <input type="number" name="reorder_level" class="form-control rounded-3" value="{{ old('reorder_level', $product->reorder_level) }}" min="0">
                        </div>
                    </div>

                    <!-- Submit Actions -->
                    <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body">
                        <button type="submit" class="btn btn-primary rounded-3 w-100 py-3 fw-bold mb-2">Update Product Master</button>
                        <a href="{{ route('products.show', $product) }}" class="btn btn-outline-secondary rounded-3 w-100 py-2">Cancel</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Quick Add Category Modal -->
<div class="modal fade" id="quickAddCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 p-2">
            <form id="quickCategoryForm" onsubmit="submitQuickCategory(event)">
                @csrf
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Create New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                        <input type="text" id="quick_cat_name" name="name" class="form-control rounded-3" placeholder="e.g. Smart Electronics" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category Code</label>
                        <input type="text" id="quick_cat_code" name="code" class="form-control rounded-3" placeholder="Auto-generated if empty">
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-3 fw-bold">Save & Select Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Quick Add Brand Modal -->
<div class="modal fade" id="quickAddBrandModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 p-2">
            <form id="quickBrandForm" onsubmit="submitQuickBrand(event)">
                @csrf
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Create New Brand</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Brand Name <span class="text-danger">*</span></label>
                        <input type="text" id="quick_brand_name" name="name" class="form-control rounded-3" placeholder="e.g. Apex Tech Industries" required>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-3 fw-bold">Save & Select Brand</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function filterSelectOptions(inputEl, selectId) {
    const filter = inputEl.value.toLowerCase().trim();
    const select = document.getElementById(selectId);
    if (!select) return;
    const options = select.options;

    for (let i = 0; i < options.length; i++) {
        if (i === 0) continue;
        const text = options[i].text.toLowerCase();
        if (text.includes(filter)) {
            options[i].style.display = '';
        } else {
            options[i].style.display = 'none';
        }
    }
}

async function submitQuickCategory(e) {
    e.preventDefault();
    const name = document.getElementById('quick_cat_name').value;
    const code = document.getElementById('quick_cat_code').value;
    const token = document.querySelector('input[name="_token"]').value;

    try {
        const response = await fetch("{{ route('categories.store') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ name: name, code: code, status: 'active' })
        });
        const result = await response.json();
        if (result.success && result.data) {
            const select = document.getElementById('category_id_select');
            const opt = document.createElement('option');
            opt.value = result.data.id;
            opt.text = result.data.name;
            opt.selected = true;
            select.appendChild(opt);
            
            const modalEl = document.getElementById('quickAddCategoryModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
            document.getElementById('quickCategoryForm').reset();
        } else {
            alert('Failed to add category.');
        }
    } catch(err) {
        console.error(err);
        alert('Error adding category.');
    }
}

async function submitQuickBrand(e) {
    e.preventDefault();
    const name = document.getElementById('quick_brand_name').value;
    const token = document.querySelector('input[name="_token"]').value;

    try {
        const response = await fetch("{{ route('brands.store') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ name: name, status: 'active' })
        });
        const result = await response.json();
        if (result.success && result.data) {
            const select = document.getElementById('brand_id_select');
            const opt = document.createElement('option');
            opt.value = result.data.id;
            opt.text = result.data.name;
            opt.selected = true;
            select.appendChild(opt);

            const modalEl = document.getElementById('quickAddBrandModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
            document.getElementById('quickBrandForm').reset();
        } else {
            alert('Failed to add brand.');
        }
    } catch(err) {
        console.error(err);
        alert('Error adding brand.');
    }
}
</script>
@endsection
