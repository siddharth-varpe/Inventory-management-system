@props([
    'queueTitle' => 'Task Queue',
    'queueSubtitle' => 'Pending tasks',
])

<div class="row g-4">
    <!-- Left Master Panel: Task Queue (35% on Desktop) -->
    <div class="col-12 col-lg-4 col-xl-4">
        <div class="card p-3 rounded-4 shadow-sm border-translucent bg-body h-100">
            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                <div>
                    <h6 class="fw-bold text-body mb-0">{{ $queueTitle }}</h6>
                    <span class="text-muted small">{{ $queueSubtitle }}</span>
                </div>
                @if(isset($queueActions))
                    <div>{{ $queueActions }}</div>
                @endif
            </div>

            <!-- Live Task Queue Search Bar -->
            <div class="mb-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-body-tertiary border-end-0 rounded-start-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-search text-muted" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/></svg>
                    </span>
                    <input type="text" class="form-control form-control-sm border-start-0 rounded-end-3" id="queueSearchInput" placeholder="Search queue by SKU, Ref, Name..." onkeyup="filterQueueCards(this.value)">
                </div>
            </div>

            <div class="overflow-auto pe-1" style="max-height: 680px;">
                {{ $queueContent ?? $slot }}
            </div>
        </div>
    </div>

    <!-- Right Detail Panel: Execution Canvas (65% on Desktop) -->
    <div class="col-12 col-lg-8 col-xl-8">
        <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body h-100">
            {{ $canvasContent }}
        </div>
    </div>
</div>

<script>
function filterQueueCards(query) {
    const term = query.toLowerCase().trim();
    const cards = document.querySelectorAll('.task-card-item');
    cards.forEach(card => {
        const text = card.textContent.toLowerCase();
        if (text.includes(term)) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>
