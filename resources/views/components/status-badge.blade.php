@props(['status' => 'pending'])

@php
    $s = strtolower(str_replace(' ', '_', $status));
    $class = match($s) {
        'completed', 'active', 'approved', 'delivered', 'resolved', 'packed', 'won' => 'bg-success-subtle text-success border border-success-subtle',
        'in_progress', 'picking', 'packing', 'in_transit', 'assigned', 'inventory_validated' => 'bg-primary-subtle text-primary border border-primary-subtle',
        'pending', 'queued', 'open', 'draft', 'pending_approval', 'waiting_warehouse' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
        'cancelled', 'rejected', 'damaged', 'blocked', 'closed', 'lost', 'expired' => 'bg-danger-subtle text-danger border border-danger-subtle',
        'converted', 'reserved' => 'bg-purple-subtle text-purple border border-purple-subtle',
        default => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
    };
@endphp

<span class="badge {{ $class }} rounded-3" style="font-size: 0.75rem; font-weight: 600;">
    {{ str_replace('_', ' ', ucfirst($status)) }}
</span>
