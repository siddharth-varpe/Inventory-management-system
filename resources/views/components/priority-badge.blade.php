@props(['priority' => 'medium'])

@php
    $p = strtolower($priority);
    $class = match($p) {
        'urgent' => 'bg-danger text-white',
        'high' => 'bg-danger-subtle text-danger',
        'medium' => 'bg-warning-subtle text-warning-emphasis',
        'low' => 'bg-info-subtle text-info',
        default => 'bg-secondary-subtle text-secondary',
    };
@endphp

<span class="badge {{ $class }} rounded-3 text-uppercase" style="font-size: 0.7rem; font-weight: 700;">
    {{ $priority }}
</span>
