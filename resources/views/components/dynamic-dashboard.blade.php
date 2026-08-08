@props(['portal' => 'stock'])

@php
    /** @var \App\Core\Workspace\Builder\WorkspaceBuilderService $workspaceBuilder */
    $workspaceBuilder = app(\App\Core\Workspace\Builder\WorkspaceBuilderService::class);
    $config = $workspaceBuilder->buildWorkspace(auth()->user(), $portal);
@endphp

<div class="dynamic-workspace-container">
    <!-- Dynamic Quick Actions Bar -->
    @if(!empty($config->quickActions))
    <div class="card p-3 rounded-4 shadow-sm border-translucent bg-body mb-4">
        <div class="d-flex align-items-center justify-content-between">
            <h6 class="fw-bold text-body mb-0">Role Quick Actions</h6>
            <div class="d-flex flex-wrap gap-2">
                @foreach($config->quickActions as $act)
                    <a href="{{ route($act['route']) }}" class="btn {{ $act['class'] }} rounded-3 px-3 fw-bold btn-sm">
                        {{ $act['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Dynamic Registered Widgets Grid -->
    <div class="row g-3">
        @foreach($config->dashboardWidgets as $widget)
            <div class="col-12 col-md-6">
                <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="fw-bold text-body mb-0">{{ $widget['title'] }}</h6>
                        <span class="badge bg-light text-muted border">{{ strtoupper($widget['type']) }}</span>
                    </div>
                    <p class="text-muted small mb-0">Composed dynamically for {{ implode(', ', $config->userContext['roles']) }}.</p>
                </div>
            </div>
        @endforeach
    </div>
</div>
