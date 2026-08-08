<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Product Barcode Labels - StockManager ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: system-ui, -apple-system, sans-serif;
        }
        .label-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            padding: 20px;
            max-width: 1000px;
            margin: 0 auto;
        }
        .barcode-card {
            background: #ffffff;
            border: 1px dashed #ccc;
            border-radius: 8px;
            padding: 12px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            page-break-inside: avoid;
        }
        .product-title {
            font-weight: 700;
            font-size: 0.9rem;
            color: #111;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 2px;
        }
        .product-meta {
            font-size: 0.75rem;
            color: #555;
            margin-bottom: 6px;
        }
        .barcode-svg {
            max-width: 100%;
            height: auto;
        }
        .barcode-text {
            font-family: monospace;
            font-weight: 700;
            font-size: 0.8rem;
            letter-spacing: 1px;
            color: #000;
            margin-top: 2px;
        }
        @media print {
            body {
                background: none;
            }
            .no-print {
                display: none !important;
            }
            .label-grid {
                padding: 0;
                gap: 10px;
            }
            .barcode-card {
                border: 1px solid #ddd;
            }
        }
    </style>
</head>
<body>
    <div class="no-print p-3 bg-white border-bottom shadow-sm mb-3">
        <div class="container d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-0">Product Barcode Label Sheet</h5>
                <span class="text-muted small">Total Labels: {{ count($labels) }}</span>
            </div>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-primary fw-bold px-4">Print Labels Now</button>
                <button onclick="window.close()" class="btn btn-outline-secondary">Close Window</button>
            </div>
        </div>
    </div>

    <div class="label-grid">
        @foreach($labels as $item)
            <div class="barcode-card">
                <div class="product-title">{{ $item['product']->name }}</div>
                <div class="product-meta">SKU: {{ $item['product']->sku }} | Price: ₹{{ number_format((float)$item['product']->selling_price, 2) }}</div>
                <div>
                    {!! $item['barcode_svg'] !!}
                </div>
                <div class="barcode-text">{{ $item['product']->barcode ?: $item['product']->sku }}</div>
            </div>
        @endforeach
    </div>
</body>
</html>
