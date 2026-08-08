<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quotation #{{ $quotation->quotation_number }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #333; line-height: 1.5; padding: 20px; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #e5e7eb; padding-bottom: 15px; margin-bottom: 20px; }
        .company-name { font-size: 22px; font-weight: bold; color: #1e293b; }
        .title { font-size: 20px; font-weight: bold; text-align: right; color: #d97706; }
        .grid { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th { background: #f8fafc; text-align: left; padding: 8px; border-bottom: 1px solid #cbd5e1; }
        .table td { padding: 8px; border-bottom: 1px solid #f1f5f9; }
        .text-right { text-align: right; }
        .total-box { margin-left: auto; width: 300px; border-top: 2px solid #e5e7eb; padding-top: 10px; }
        .total-row { display: flex; justify-content: space-between; padding: 4px 0; }
        .grand-total { font-size: 16px; font-weight: bold; color: #16a34a; }
        .footer { margin-top: 40px; border-top: 1px solid #e5e7eb; padding-top: 15px; font-size: 10px; color: #64748b; text-align: center; }
    </style>
</head>
<body>

<div class="header">
    <div>
        <div class="company-name">{{ $company->name ?? 'StockManager Enterprise ERP' }}</div>
        <div>{{ $company->address ?? 'Central Depot, MIDC Industrial Area, Mumbai' }}</div>
        <div>GSTIN: {{ $company->gst_number ?? '27AAACG1234F1Z5' }} | Email: info@stockmanager.com</div>
    </div>
    <div>
        <div class="title">COMMERCIAL QUOTATION</div>
        <div style="text-align: right;"><strong>#{{ $quotation->quotation_number }}</strong></div>
        <div style="text-align: right;">Date: {{ $quotation->created_at->format('d M Y') }}</div>
        <div style="text-align: right;">Valid Until: {{ $quotation->validity_date->format('d M Y') }}</div>
    </div>
</div>

<div class="grid">
    <div>
        <strong>CUSTOMER PROPOSAL TO:</strong><br>
        <strong>{{ $quotation->customer->company_name }}</strong><br>
        Code: {{ $quotation->customer->customer_code }}<br>
        GSTIN: {{ $quotation->customer->gst_number ?? 'N/A' }}<br>
        Email: {{ $quotation->customer->email ?? 'N/A' }} | Phone: {{ $quotation->customer->phone ?? 'N/A' }}
    </div>
    <div style="text-align: right;">
        <strong>PREPARED BY:</strong><br>
        {{ $quotation->salesperson->name ?? 'Sales Executive' }}<br>
        Status: <strong>{{ strtoupper($quotation->status) }}</strong>
    </div>
</div>

<table class="table">
    <thead>
        <tr>
            <th>#</th>
            <th>SKU</th>
            <th>Product Description</th>
            <th class="text-right">Qty</th>
            <th class="text-right">Unit Price</th>
            <th class="text-right">Taxable Value</th>
            <th class="text-right">GST %</th>
            <th class="text-right">Line Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($quotation->items as $idx => $item)
            <tr>
                <td>{{ $idx + 1 }}</td>
                <td><code>{{ $item->product->sku ?? 'N/A' }}</code></td>
                <td><strong>{{ $item->product->name }}</strong></td>
                <td class="text-right">{{ $item->quantity }}</td>
                <td class="text-right">₹{{ number_format((float)$item->unit_price, 2) }}</td>
                <td class="text-right">₹{{ number_format((float)$item->taxable_value, 2) }}</td>
                <td class="text-right">{{ number_format((float)$item->gst_rate, 1) }}%</td>
                <td class="text-right">₹{{ number_format((float)$item->line_total, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<div class="total-box">
    <div class="total-row"><span>Subtotal:</span><strong>₹{{ number_format((float)$quotation->subtotal, 2) }}</strong></div>
    <div class="total-row"><span>Order Discount:</span><strong>-₹{{ number_format((float)$quotation->order_discount_amount, 2) }}</strong></div>
    <div class="total-row"><span>Taxable Value:</span><strong>₹{{ number_format((float)$quotation->taxable_amount, 2) }}</strong></div>
    @if((float)$quotation->igst_amount > 0)
        <div class="total-row"><span>IGST:</span><strong>₹{{ number_format((float)$quotation->igst_amount, 2) }}</strong></div>
    @else
        <div class="total-row"><span>CGST (9%):</span><strong>₹{{ number_format((float)$quotation->cgst_amount, 2) }}</strong></div>
        <div class="total-row"><span>SGST (9%):</span><strong>₹{{ number_format((float)$quotation->sgst_amount, 2) }}</strong></div>
    @endif
    <div class="total-row grand-total"><span>Grand Total:</span><span>₹{{ number_format((float)$quotation->grand_total, 2) }}</span></div>
</div>

<div style="margin-top: 20px;">
    <strong>Terms & Conditions:</strong><br>
    • Payment Terms: {{ $quotation->payment_terms ?? 'Net 30' }}<br>
    • Delivery Terms: {{ $quotation->delivery_terms ?? 'Standard Dispatch' }}
</div>

<div class="footer">
    This is an enterprise system generated quotation. StockManager Enterprise ERP - Commercial Sales Engine.
</div>

<script>window.print();</script>
</body>
</html>
