@props([
    'name',
    'id' => null,
    'options' => [],
    'selected' => null,
    'placeholder' => 'Select option...',
    'required' => false,
    'class' => '',
])

@php
    $selectId = $id ?: $name;
    $selectedLabel = $placeholder;
    foreach($options as $opt) {
        if ($selected == $opt['value']) {
            $selectedLabel = $opt['label'];
            break;
        }
    }
@endphp

<div class="searchable-select-container position-relative {{ $class }}" id="container-{{ $selectId }}">
    <!-- Hidden Native Select Tag for Form Submission & JS Event Compatibility -->
    <select name="{{ $name }}" id="{{ $selectId }}" class="d-none" {{ $required ? 'required' : '' }}>
        <option value="">{{ $placeholder }}</option>
        @foreach($options as $opt)
            <option value="{{ $opt['value'] }}" 
                @if(isset($opt['cost'])) data-cost="{{ $opt['cost'] }}" @endif
                @if(isset($opt['stock'])) data-stock="{{ $opt['stock'] }}" @endif
                @if(isset($opt['price'])) data-price="{{ $opt['price'] }}" @endif
                {{ $selected == $opt['value'] ? 'selected' : '' }}>
                {{ $opt['label'] }}
            </option>
        @endforeach
    </select>

    <!-- Unified Searchable Dropdown Button Trigger -->
    <button type="button" 
            class="form-select rounded-3 text-start d-flex align-items-center justify-content-between bg-body shadow-xs" 
            id="btn-{{ $selectId }}" 
            data-bs-toggle="dropdown" 
            data-bs-auto-close="outside" 
            aria-expanded="false">
        <span class="text-truncate text-body selected-text-label">{{ $selectedLabel }}</span>
    </button>

    <!-- Dropdown Menu Panel with Embedded Search Bar at Top -->
    <div class="dropdown-menu p-2 rounded-3 shadow-lg border-translucent w-100" id="menu-{{ $selectId }}" style="max-height: 320px; overflow-y: auto; z-index: 1050;">
        <!-- Search bar inside the dropdown header -->
        <div class="p-1 mb-2 sticky-top bg-body border-bottom pb-2">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-body-tertiary border-end-0 rounded-start-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" class="bi bi-search text-muted" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/></svg>
                </span>
                <input type="text" 
                       class="form-control form-control-sm border-start-0 rounded-end-3 search-input" 
                       placeholder="Search option..." 
                       onkeyup="filterSearchableSelectOptions(this, '{{ $selectId }}')">
            </div>
        </div>

        <!-- Options Container -->
        <div class="options-container" id="options-{{ $selectId }}">
            <div class="dropdown-item rounded-2 cursor-pointer py-1.5 px-2 option-node text-muted small" 
                 data-value="" 
                 onclick="selectSearchableOption('{{ $selectId }}', '', '{{ addslashes($placeholder) }}')">
                {{ $placeholder }}
            </div>
            @foreach($options as $opt)
                <div class="dropdown-item rounded-2 cursor-pointer py-1.5 px-2 option-node small {{ $selected == $opt['value'] ? 'active bg-primary text-white' : '' }}" 
                     data-value="{{ $opt['value'] }}"
                     data-search-text="{{ strtolower($opt['label']) }}"
                     onclick="selectSearchableOption('{{ $selectId }}', '{{ $opt['value'] }}', '{{ addslashes($opt['label']) }}')">
                    {{ $opt['label'] }}
                </div>
            @endforeach
        </div>
    </div>
</div>

@once
<script>
function filterSearchableSelectOptions(inputEl, selectId) {
    const filter = inputEl.value.toLowerCase().trim();
    const container = document.getElementById('options-' + selectId);
    if (!container) return;

    const items = container.querySelectorAll('.option-node');
    items.forEach(item => {
        const val = item.getAttribute('data-value');
        if (val === '') return; // keep placeholder option
        const text = (item.getAttribute('data-search-text') || item.textContent).toLowerCase();
        if (text.includes(filter)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

function selectSearchableOption(selectId, value, label) {
    const select = document.getElementById(selectId);
    const btn = document.getElementById('btn-' + selectId);
    const menu = document.getElementById('menu-' + selectId);
    const container = document.getElementById('options-' + selectId);

    if (select) {
        select.value = value;
        select.dispatchEvent(new Event('change', { bubbles: true }));
    }

    if (btn) {
        const labelSpan = btn.querySelector('.selected-text-label');
        if (labelSpan) {
            labelSpan.textContent = label;
        }
    }

    if (container) {
        container.querySelectorAll('.option-node').forEach(item => {
            if (item.getAttribute('data-value') === value) {
                item.classList.add('active', 'bg-primary', 'text-white');
            } else {
                item.classList.remove('active', 'bg-primary', 'text-white');
            }
        });
    }

    if (btn) {
        const bsDropdown = bootstrap.Dropdown.getInstance(btn);
        if (bsDropdown) {
            bsDropdown.hide();
        }
    }
}

document.addEventListener('show.bs.dropdown', function (e) {
    const btn = e.target;
    if (btn && btn.id && btn.id.startsWith('btn-')) {
        const selectId = btn.id.replace('btn-', '');
        setTimeout(() => {
            const menu = document.getElementById('menu-' + selectId);
            if (menu) {
                const searchInput = menu.querySelector('.search-input');
                if (searchInput) {
                    searchInput.focus();
                }
            }
        }, 100);
    }
});
</script>
@endonce
