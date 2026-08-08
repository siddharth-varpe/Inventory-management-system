@props([
    'warehouses' => [],
])

<div class="row g-2">
    <div class="col-12 col-md-6">
        <label class="form-label small fw-semibold">Warehouse</label>
        <input type="text" name="warehouse_name" class="form-control form-control-sm rounded-3" value="Main Depot" required>
    </div>

    <div class="col-12 col-md-6">
        <label class="form-label small fw-semibold">Rack & Aisle</label>
        <input type="text" name="rack_name" class="form-control form-control-sm rounded-3" value="Rack A-01" required>
    </div>

    <div class="col-6">
        <label class="form-label small fw-semibold">Shelf</label>
        <select name="shelf" class="form-select form-select-sm rounded-3">
            <option value="Shelf 1">Shelf 1</option>
            <option value="Shelf 2">Shelf 2</option>
            <option value="Shelf 3">Shelf 3</option>
            <option value="Shelf 4">Shelf 4</option>
        </select>
    </div>

    <div class="col-6">
        <label class="form-label small fw-semibold">Bin</label>
        <select name="bin" class="form-select form-select-sm rounded-3">
            <option value="Bin 01">Bin 01</option>
            <option value="Bin 02">Bin 02</option>
            <option value="Bin 03">Bin 03</option>
            <option value="Bin 04">Bin 04</option>
        </select>
    </div>
</div>
