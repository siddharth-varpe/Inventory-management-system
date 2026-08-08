@extends('layouts.app')

@section('title', 'Barcode & QR Code Center - StockManager ERP')

@section('header', 'Barcode & QR Code Center')
@section('subheader', 'Generate Code128 barcodes, vector QR codes, and print product label sheets in bulk.')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('stock.dashboard') }}" class="text-decoration-none">Manage Stock</a></li>
    <li class="breadcrumb-item active" aria-current="page">Barcode Center</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Left Column: Sidebar -->
    <div class="col-12 col-lg-3">
        <x-stock-sidebar />
    </div>

    <!-- Right Column: Barcode Center Area -->
    <div class="col-12 col-lg-9">
        <div class="row g-4 mb-4">
            <!-- Single Product Barcode Generator -->
            <div class="col-12 col-xl-6">
                <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body h-100">
                    <h5 class="fw-bold text-body mb-3">Barcode & QR Preview</h5>
                    
                    <form method="GET" action="{{ route('stock.barcodes.index') }}" class="mb-4">
                        <label class="form-label fw-semibold">Select Product Master</label>
                        <div class="d-flex gap-2">
                            <select name="product_id" class="form-select rounded-3" required>
                                <option value="">Choose product...</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}" {{ ($selectedProduct->id ?? '') == $p->id ? 'selected' : '' }}>
                                        {{ $p->name }} (SKU: {{ $p->sku }})
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-primary rounded-3 px-3">Generate</button>
                        </div>
                    </form>

                    @if($selectedProduct)
                        <div class="p-4 rounded-4 bg-white text-center border">
                            <div class="fw-bold text-dark fs-5 mb-1">{{ $selectedProduct->name }}</div>
                            <div class="text-muted small mb-3">SKU: {{ $selectedProduct->sku }} | Price: ₹{{ number_format((float)$selectedProduct->selling_price, 2) }}</div>
                            
                            <div class="mb-3">
                                {!! $barcodeHTML !!}
                            </div>
                            <div class="font-monospace fw-bold text-dark mb-4" style="letter-spacing: 2px;">{{ $selectedProduct->barcode ?: $selectedProduct->sku }}</div>

                            <div class="mb-3">
                                {!! $qrSVG !!}
                            </div>

                            <form action="{{ route('stock.barcodes.print') }}" method="POST" target="_blank" class="mt-3">
                                @csrf
                                <input type="hidden" name="single_product_id" value="{{ $selectedProduct->id }}">
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <input type="number" name="copy_count" class="form-control form-control-sm rounded-3 text-center" style="width: 80px;" value="12" min="1" max="100">
                                    <button type="submit" class="btn btn-outline-dark btn-sm rounded-3 fw-bold px-3">Print {{ $selectedProduct->sku }} Labels</button>
                                </div>
                            </form>
                        </div>
                    @else
                        <div class="text-center text-muted py-5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-qr-code text-muted mb-2" viewBox="0 0 16 16"><path d="M2 2h2v2H2z"/><path d="M6 0v6H0V0zM5 1H1v4h4zM4 3H2v2h2zM14 2h2v2h-2z"/><path d="M12 0v6h6V0zM17 1h-4v4h4zM16 3h-2v2h2zM2 14h2v2H2z"/><path d="M6 12v6H0v-6zM5 13H1v4h4zM4 15H2v2h2zM14 14h2v2h-2z"/><path d="M12 12v6h6v-6zM17 13h-4v4h4zM16 15h-2v2h2z"/></svg>
                            <p class="mb-0 small">Select a product from the dropdown above to render live Code128 barcode and QR vectors.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Bulk Label Sheet Printing Form -->
            <div class="col-12 col-xl-6">
                <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body h-100">
                    <h5 class="fw-bold text-body mb-3">Bulk Label Sheet Generator</h5>
                    <p class="text-muted small">Select multiple products and specify label copy counts to generate print-ready sticker sheets (24 labels per A4 sheet format).</p>
                    
                    <form action="{{ route('stock.barcodes.print') }}" method="POST" target="_blank">
                        @csrf
                        <div class="table-responsive mb-3" style="max-height: 280px;">
                            <table class="table table-sm table-hover align-middle">
                                <thead>
                                    <tr class="text-muted small">
                                        <th style="width: 30px;">#</th>
                                        <th>Product</th>
                                        <th style="width: 90px;">Copies</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($products->take(10) as $idx => $p)
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="product_ids[]" value="{{ $p->id }}" class="form-check-input" id="chk{{ $p->id }}" {{ $idx < 3 ? 'checked' : '' }}>
                                        </td>
                                        <td>
                                            <label for="chk{{ $p->id }}" class="fw-semibold text-body small d-block mb-0">{{ $p->name }}</label>
                                            <code class="small text-muted">{{ $p->sku }}</code>
                                        </td>
                                        <td>
                                            <input type="number" name="quantities[]" class="form-control form-control-sm rounded-3 text-center" value="4" min="1" max="50">
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <button type="submit" class="btn btn-success rounded-3 w-100 py-2 fw-bold d-flex align-items-center justify-content-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer-fill" viewBox="0 0 16 16"><path d="M5 1a2 2 0 0 0-2 2v1h10V3a2 2 0 0 0-2-2zm6 8H5a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1"/><path d="M0 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1v-2a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2H2a2 2 0 0 1-2-2zm2.5 1a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1"/></svg>
                            <span>Generate Bulk Sticker Sheet</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
