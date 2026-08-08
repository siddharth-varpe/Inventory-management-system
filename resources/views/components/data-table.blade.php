@props([
    'headers' => [],
])

<div class="table-responsive rounded-3 border border-translucent shadow-sm">
    <table {{ $attributes->merge(['class' => 'table table-hover align-middle mb-0']) }}>
        @if(!empty($headers))
        <thead class="table-light">
            <tr>
                @foreach($headers as $header)
                <th scope="col" class="fw-semibold text-uppercase small text-muted px-3 py-3" style="font-size: 0.75rem; letter-spacing: 0.05rem;">
                    {{ $header }}
                </th>
                @endforeach
            </tr>
        </thead>
        @endif
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>
