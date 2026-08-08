@props([
    'items' => [],
    'taskId' => null,
])

<div class="table-responsive mb-3">
    <table class="table table-hover align-middle">
        <thead>
            <tr class="text-muted small">
                <th>Verification</th>
                <th>Product Item</th>
                <th>Source Coordinate</th>
                <th class="text-center">Qty</th>
                <th class="text-end">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr class="{{ $item->is_verified ? 'table-success-subtle' : '' }}">
                <td style="width: 40px;">
                    @if($item->is_verified)
                        <span class="badge bg-success p-1 rounded-circle">✓</span>
                    @else
                        <span class="badge bg-secondary-subtle text-muted p-1 rounded-circle">○</span>
                    @endif
                </td>
                <td>
                    <div class="fw-bold text-body">{{ $item->product->name ?? 'N/A' }}</div>
                    <code class="small text-muted">SKU: {{ $item->product->sku ?? 'N/A' }}</code>
                </td>
                <td>
                    <x-location-card :location="$item->location_coordinate ?: 'Main Storage'" />
                </td>
                <td class="fw-bold text-center fs-6">{{ $item->requested_quantity }}</td>
                <td class="text-end">
                    @if($item->is_verified)
                        <span class="badge bg-success-subtle text-success">Verified</span>
                    @else
                        <form action="{{ route('organize.pickpack.verify-item', $item->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-primary rounded-3 px-3 fw-semibold">Verify Item</button>
                        </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
