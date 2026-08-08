<li class="list-group-item py-2 border-0">
    <div class="d-flex align-items-center gap-2 p-2 rounded-3 bg-body-tertiary border border-translucent">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-folder-fill text-primary" viewBox="0 0 16 16">
            <path d="M9.828 3h3.982a2 2 0 0 1 1.992 2.181l-.637 7A2 2 0 0 1 13.174 14H2.826a2 2 0 0 1-1.991-1.819l-.637-7a2 2 0 0 1 1.99-2.181h3.982a2 2 0 0 1 1.414.586l1.828 1.828A2 2 0 0 0 9.828 3m-2.95 1H2.5a1 1 0 0 0-.996 1.091l.637 7a1 1 0 0 0 .995.909h10.348a1 1 0 0 0 .995-.909l.637-7A1 1 0 0 0 13.5 4H9.828a1 1 0 0 1-.707-.293L7.293 1.88A1 1 0 0 0 6.586 1.5"/>
        </svg>
        <span class="fw-bold text-body">{{ $category->name }}</span>
        <code class="small text-muted">({{ $category->code }})</code>
        <x-status-badge :status="$category->status" />
    </div>

    @if($category->children->isNotEmpty())
        <ul class="list-group list-group-flush ms-4 mt-2 border-start border-2 ps-2 border-primary-subtle">
            @foreach($category->children as $child)
                @include('categories.partials.tree_node', ['category' => $child])
            @endforeach
        </ul>
    @endif
</li>
