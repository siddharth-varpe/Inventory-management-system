@props(['activeTab' => ''])

@php
    $navItems = [
        [
            'id' => 'dashboard',
            'route' => 'procurement.dashboard',
            'label' => 'Procurement Desk',
            'icon' => '<path d="M3 3h8v8H3V3zm0 10h8v8H3v-8zm10-10h8v8h-8V3zm0 10h8v8h-8v-8z"/>',
            'badge' => null,
        ],
        [
            'id' => 'suppliers',
            'route' => 'procurement.suppliers.index',
            'label' => 'Supplier Management',
            'icon' => '<path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>',
            'badge' => \App\Models\Supplier::where('status', 'active')->count(),
        ],
        [
            'id' => 'requisitions',
            'route' => 'procurement.requisitions.index',
            'label' => 'Purchase Requisitions',
            'icon' => '<path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-2 10h-4v4h-2v-4H7v-2h4V7h2v4h4v2z"/>',
            'badge' => \App\Models\PurchaseRequisition::where('status', 'pending_approval')->count(),
        ],
        [
            'id' => 'purchase-orders',
            'route' => 'procurement.purchase-orders.index',
            'label' => 'Purchase Orders',
            'icon' => '<path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>',
            'badge' => \App\Models\PurchaseOrder::whereIn('status', ['submitted', 'approved'])->count(),
        ],
        [
            'id' => 'grn',
            'route' => 'procurement.grn.index',
            'label' => 'Goods Receipt Notes',
            'icon' => '<path d="M20 8h-3V4H7v4H4c-1.1 0-2 .9-2 2v11h20V10c0-1.1-.9-2-2-2zM9 6h6v2H9V6zm11 13H4v-7h16v7zm-5-5H9v2h6v-2z"/>',
            'badge' => null,
        ],
        [
            'id' => 'vendor-performance',
            'route' => 'procurement.vendor-performance.index',
            'label' => 'Vendor Performance',
            'icon' => '<path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>',
            'badge' => null,
        ],
        [
            'id' => 'reports',
            'route' => 'procurement.reports.index',
            'label' => 'Operational Reports',
            'icon' => '<path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/>',
            'badge' => null,
        ],
    ];
@endphp

<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <!-- Header Badge -->
    <div class="p-3 bg-gradient-primary text-white d-flex align-items-center justify-content-between" style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);">
        <div class="d-flex align-items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-2 10h-4v4h-2v-4H7v-2h4V7h2v4h4v2z"/></svg>
            <div>
                <h6 class="fw-bold mb-0 text-white">Order Supplies</h6>
                <span class="small opacity-75" style="font-size: 0.75rem;">Procurement System</span>
            </div>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-light rounded-pill px-2 py-0 fw-semibold shadow-sm text-decoration-none" style="font-size: 0.75rem;">
            &larr; Dashboard
        </a>
    </div>

    <!-- Navigation List -->
    <div class="list-group list-group-flush p-2">
        @foreach($navItems as $item)
            @php
                $isCurrent = request()->routeIs($item['route']);
            @endphp
            <a href="{{ route($item['route']) }}" 
               class="list-group-item list-group-item-action rounded-3 border-0 d-flex align-items-center justify-content-between py-2 px-3 mb-1 {{ $isCurrent ? 'bg-primary text-white fw-bold active' : 'text-body' }}">
                <div class="d-flex align-items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 24 24" class="{{ $isCurrent ? 'text-white' : 'text-primary' }}">
                        {!! $item['icon'] !!}
                    </svg>
                    <span style="font-size: 0.875rem;">{{ $item['label'] }}</span>
                </div>
                @if($item['badge'] !== null && $item['badge'] > 0)
                    <span class="badge {{ $isCurrent ? 'bg-white text-primary' : 'bg-primary-subtle text-primary' }} rounded-pill px-2">
                        {{ $item['badge'] }}
                    </span>
                @endif
            </a>
        @endforeach
    </div>
</div>
