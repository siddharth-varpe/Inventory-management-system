@props([
    'title',
    'subtitle' => null,
    'priority' => 'medium',
    'status' => 'pending',
    'active' => false,
    'url' => '#',
])

<a href="{{ $url }}" class="text-decoration-none task-card-item">
    <div class="card p-3 rounded-4 mb-2 shadow-xs border-translucent {{ $active ? 'border-primary border-2 bg-primary-subtle' : 'bg-body hover-shadow' }} transition-all">
        <div class="d-flex justify-content-between align-items-start mb-1">
            <h6 class="fw-bold text-body mb-0 text-truncate" style="max-width: 180px;">{{ $title }}</h6>
            <x-priority-badge :priority="$priority" />
        </div>
        
        @if($subtitle)
            <div class="text-muted small mb-2 text-truncate">{{ $subtitle }}</div>
        @endif

        <div class="d-flex justify-content-between align-items-center">
            <x-status-badge :status="$status" />
            <span class="text-primary small fw-semibold">View Task &rarr;</span>
        </div>
    </div>
</a>
