@props([
    'id' => 'deleteModal',
    'title' => 'Confirm Record Deletion',
    'action' => '#',
])

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-translucent shadow">
            <div class="modal-header border-bottom border-translucent bg-danger-subtle">
                <h5 class="modal-title fw-bold text-danger d-flex align-items-center gap-2" id="{{ $id }}Label">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-exclamation-triangle-fill" viewBox="0 0 16 16">
                        <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                    </svg>
                    <span>{{ $title }}</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ $action }}" method="POST" id="{{ $id }}Form">
                @csrf
                @method('DELETE')
                <div class="modal-body py-4 text-body">
                    Are you sure you want to delete this record? This action cannot be undone.
                </div>
                <div class="modal-footer border-top border-translucent">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm fw-semibold">Delete Record</button>
                </div>
            </form>
        </div>
    </div>
</div>
