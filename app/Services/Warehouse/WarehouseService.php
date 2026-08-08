<?php

declare(strict_types=1);

namespace App\Services\Warehouse;

use App\Models\Warehouse;
use App\Models\WarehouseAisle;
use App\Models\WarehouseBin;
use App\Models\WarehouseRack;
use App\Models\WarehouseZone;
use App\Repositories\Contracts\WarehouseRepositoryInterface;
use App\Services\BaseService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class WarehouseService extends BaseService
{
    public function __construct(WarehouseRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function getWarehouses(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        /** @var WarehouseRepositoryInterface $repo */
        $repo = $this->repository;
        return $repo->getWarehouses($filters, $perPage);
    }

    public function createWarehouse(array $data): Warehouse
    {
        $data['created_by'] = auth()->id();
        /** @var Warehouse $warehouse */
        $warehouse = $this->repository->create($data);

        // Auto-seed standard zones for new warehouse
        $defaultZones = [
            ['name' => 'Receiving Zone', 'code' => 'ZONE-RCV', 'type' => 'receiving'],
            ['name' => 'Main Storage', 'code' => 'ZONE-STR', 'type' => 'storage'],
            ['name' => 'Picking Station', 'code' => 'ZONE-PCK', 'type' => 'picking'],
            ['name' => 'Packing & Dispatch', 'code' => 'ZONE-DSP', 'type' => 'dispatch'],
            ['name' => 'Returns & Quarantine', 'code' => 'ZONE-RET', 'type' => 'returns'],
            ['name' => 'Damaged Goods Area', 'code' => 'ZONE-DMG', 'type' => 'damaged'],
        ];

        foreach ($defaultZones as $zoneData) {
            $zone = WarehouseZone::create(array_merge($zoneData, ['warehouse_id' => $warehouse->id]));
            
            if ($zoneData['type'] === 'storage') {
                $aisle = WarehouseAisle::create(['warehouse_zone_id' => $zone->id, 'name' => 'Aisle 01', 'code' => 'A01']);
                $rack = WarehouseRack::create(['warehouse_aisle_id' => $aisle->id, 'name' => 'Rack 01', 'code' => 'R01']);
                
                // Create sample 5-tier bin
                WarehouseBin::create([
                    'warehouse_rack_id' => $rack->id,
                    'shelf_number' => 'S01',
                    'bin_number' => 'B01',
                    'location_code' => "{$warehouse->code}-A01-R01-S01-B01",
                    'barcode' => 'LOC-' . strtoupper(Str::random(8)),
                ]);
            }
        }

        return $warehouse;
    }

    public function createBin(array $data): WarehouseBin
    {
        $rack = WarehouseRack::with('aisle.zone.warehouse')->findOrFail($data['warehouse_rack_id']);
        $whCode = $rack->aisle->zone->warehouse->code ?? 'WH01';
        $aisleCode = $rack->aisle->code ?? 'A01';
        $rackCode = $rack->code ?? 'R01';
        $shelf = $data['shelf_number'] ?? 'S01';
        $bin = $data['bin_number'] ?? 'B01';

        $locationCode = "{$whCode}-{$aisleCode}-{$rackCode}-{$shelf}-{$bin}";

        return WarehouseBin::create([
            'warehouse_rack_id' => $rack->id,
            'shelf_number' => $shelf,
            'bin_number' => $bin,
            'location_code' => $locationCode,
            'barcode' => 'LOC-' . strtoupper(Str::random(8)),
            'max_weight' => $data['max_weight'] ?? 100,
            'max_volume' => $data['max_volume'] ?? 50,
            'status' => 'active',
        ]);
    }
}
