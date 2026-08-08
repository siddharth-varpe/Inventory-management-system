@props([
    'title' => 'An Error Occurred',
    'message' => 'Unable to process enterprise request. Please try again later.',
])

<div class="alert alert-danger d-flex align-items-start gap-3 p-3 rounded-3 mb-4" role="alert">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-exclamation-octagon-fill flex-shrink-0 mt-1" viewBox="0 0 16 16">
        <path d="M11.46 1e-3h-6.92a.5.5 0 0 0-.353.146L.146 4.184A.5.5 0 0 0 0 4.538v6.924a.5.5 0 0 0 .146.353l4.038 4.038a.5.5 0 0 0 .353.146h6.924a.5.5 0 0 0 .353-.146l4.038-4.038a.5.5 0 0 0 .146-.353V4.538a.5.5 0 0 0-.146-.353L11.813.146A.5.5 0 0 0 11.46 0zM8 4c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995A.905.905 0 0 1 8 4zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
    </svg>
    <div>
        <h6 class="fw-bold mb-1">{{ $title }}</h6>
        <p class="mb-0 small opacity-90">{{ $message }}</p>
    </div>
</div>
