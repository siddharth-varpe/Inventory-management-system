@props([
    'placeholder' => 'Search records...',
    'name' => 'search',
    'value' => '',
])

<form method="GET" class="d-flex align-items-center gap-2">
    <div class="input-group">
        <span class="input-group-text bg-body-tertiary border-end-0">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-search text-muted" viewBox="0 0 16 16">
                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
            </svg>
        </span>
        <input type="text" name="{{ $name }}" class="form-control form-control-sm border-start-0 shadow-none" placeholder="{{ $placeholder }}" value="{{ request($name, $value) }}">
    </div>
    <button type="submit" class="btn btn-sm btn-outline-primary">Search</button>
    @if(request()->filled($name))
        <a href="{{ url()->current() }}" class="btn btn-sm btn-outline-secondary">Clear</a>
    @endif
</form>
