@props([
    'status' => 'normal',
])

@php
    $badgeClasses = match($status) {
        'out_of_stock' => 'bg-danger-subtle text-danger border-danger-subtle',
        'critical' => 'bg-danger-subtle text-danger border-danger-subtle',
        'low' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
        default => 'bg-success-subtle text-success border-success-subtle',
    };

    $label = match($status) {
        'out_of_stock' => 'OUT OF STOCK',
        'critical' => 'CRITICAL',
        'low' => 'LOW STOCK',
        default => 'NORMAL',
    };
@endphp

<span class="badge border rounded-pill px-2 py-1 fw-semibold {{ $badgeClasses }}" style="font-size: 0.7rem;">
    {{ $label }}
</span>
