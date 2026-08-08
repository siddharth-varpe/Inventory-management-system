@props([
    'location',
    'badgeClass' => 'bg-secondary-subtle text-body',
])

<div class="d-inline-flex align-items-center gap-2 p-2 px-3 rounded-3 border {{ $badgeClass }}">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-geo-alt-fill text-primary" viewBox="0 0 16 16"><path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10m0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6"/></svg>
    <code class="fw-bold fs-6">{{ $location ?? 'WH01-A01-R01-S01-B01' }}</code>
</div>
