<?php

declare(strict_types=1);

namespace App\Services\Product;

use App\Models\ActivityLog;
use App\Models\AuditLog;
use App\Models\Inventory;
use App\Models\PickingItem;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockReceipt;
use App\Models\StorageRequest;
use App\Models\WarehouseException;
use App\Models\WarehouseTransfer;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\BaseService;
use App\Services\Contracts\ProductServiceInterface;
use App\Services\File\FileManagerService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProductService extends BaseService implements ProductServiceInterface
{
    /**
     * FileManagerService instance.
     *
     * @var FileManagerService
     */
    protected FileManagerService $fileManager;

    /**
     * ProductService constructor.
     *
     * @param ProductRepositoryInterface $repository
     * @param FileManagerService $fileManager
     */
    public function __construct(ProductRepositoryInterface $repository, FileManagerService $fileManager)
    {
        parent::__construct($repository);
        $this->repository = $repository;
        $this->fileManager = $fileManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getCatalog(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        /** @var ProductRepositoryInterface $repo */
        $repo = $this->repository;
        return $repo->getCatalog($filters, $perPage);
    }

    /**
     * {@inheritdoc}
     */
    public function createProduct(array $data, ?UploadedFile $image = null, ?array $documents = null): Product
    {
        foreach (['category_id', 'brand_id', 'unit_id', 'tax_id'] as $fk) {
            if (array_key_exists($fk, $data)) {
                $val = $data[$fk];
                if ($val === null || $val === '' || $val === '0' || $val === 0 || $val === 'null' || $val === 'undefined') {
                    $data[$fk] = null;
                } else {
                    $data[$fk] = (int) $val;
                }
            }
        }

        if (empty($data['code'])) {
            $data['code'] = 'PRD-' . strtoupper(Str::random(6));
        }

        if (empty($data['sku'])) {
            $data['sku'] = 'SKU-' . strtoupper(Str::random(8));
        }

        if (empty($data['barcode'])) {
            $data['barcode'] = '890' . rand(100000000, 999999999);
        }

        if ($image) {
            $this->fileManager->validateFile($image, ['jpg', 'png', 'webp', 'jpeg'], 2048);
            $data['image'] = $this->fileManager->uploadFile($image, 'products');
        }

        if (!empty($documents) && is_array($documents)) {
            $docsArr = [];
            foreach ($documents as $doc) {
                if ($doc instanceof UploadedFile) {
                    $path = $this->fileManager->uploadFile($doc, 'products/documents');
                    $docsArr[] = [
                        'name' => $doc->getClientOriginalName(),
                        'path' => $path,
                        'size' => $doc->getSize(),
                    ];
                }
            }
            $data['documents'] = $docsArr;
        }

        $data['created_by'] = auth()->id() ?? 1;

        /** @var Product */
        return DB::transaction(function () use ($data) {
            $product = $this->repository->create($data);

            // Synchronize attribute values if passed
            if (!empty($data['attribute_values']) && is_array($data['attribute_values'])) {
                foreach ($data['attribute_values'] as $attrId => $val) {
                    if (!empty($val)) {
                        $product->attributeValues()->create([
                            'product_attribute_id' => $attrId,
                            'attribute_value' => is_array($val) ? json_encode($val) : (string) $val,
                        ]);
                    }
                }
            }

            Log::info("ProductService: Registered product #{$product->id} - SKU: {$product->sku}");

            return $product;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function updateProduct(int|string $id, array $data, ?UploadedFile $image = null, ?array $documents = null): bool
    {
        foreach (['category_id', 'brand_id', 'unit_id', 'tax_id'] as $fk) {
            if (array_key_exists($fk, $data)) {
                $val = $data[$fk];
                if ($val === null || $val === '' || $val === '0' || $val === 0 || $val === 'null' || $val === 'undefined') {
                    $data[$fk] = null;
                } else {
                    $data[$fk] = (int) $val;
                }
            }
        }

        /** @var Product $product */
        $product = $this->getById($id);

        if ($image) {
            $this->fileManager->validateFile($image, ['jpg', 'png', 'webp', 'jpeg'], 2048);
            $data['image'] = $this->fileManager->replaceFile($image, $product->image, 'products');
        }

        if (!empty($documents) && is_array($documents)) {
            $docsArr = $product->documents ?? [];
            foreach ($documents as $doc) {
                if ($doc instanceof UploadedFile) {
                    $path = $this->fileManager->uploadFile($doc, 'products/documents');
                    $docsArr[] = [
                        'name' => $doc->getClientOriginalName(),
                        'path' => $path,
                        'size' => $doc->getSize(),
                    ];
                }
            }
            $data['documents'] = $docsArr;
        }

        $data['updated_by'] = auth()->id() ?? 1;

        return DB::transaction(function () use ($id, $product, $data) {
            $updated = $this->repository->update($id, $data);

            if (isset($data['attribute_values']) && is_array($data['attribute_values'])) {
                $product->attributeValues()->delete();
                foreach ($data['attribute_values'] as $attrId => $val) {
                    if (!empty($val)) {
                        $product->attributeValues()->create([
                            'product_attribute_id' => $attrId,
                            'attribute_value' => is_array($val) ? json_encode($val) : (string) $val,
                        ]);
                    }
                }
            }

            return $updated;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function duplicateProduct(int|string $id): Product
    {
        /** @var ProductRepositoryInterface $repo */
        $repo = $this->repository;
        return $repo->duplicate($id);
    }

    /**
     * {@inheritdoc}
     */
    public function archiveProduct(int|string $id): bool
    {
        /** @var ProductRepositoryInterface $repo */
        $repo = $this->repository;
        return $repo->archive($id);
    }

    /**
     * Check if product has any historical business transactions or audit records.
     */
    public function hasTransactionalHistory(Product $product): bool
    {
        if (StockReceipt::where('product_id', $product->id)->exists()) return true;
        if (Inventory::where('product_id', $product->id)->exists()) return true;
        if (StorageRequest::where('product_id', $product->id)->exists()) return true;
        if (PickingItem::where('product_id', $product->id)->exists()) return true;
        if (WarehouseTransfer::where('product_id', $product->id)->exists()) return true;
        if (StockAdjustment::where('product_id', $product->id)->exists()) return true;
        if (WarehouseException::where('product_id', $product->id)->exists()) return true;
        if (AuditLog::where('table_name', 'products')->where('record_id', $product->id)->exists()) return true;
        if (ActivityLog::where('description', 'like', "%Product #{$product->id}%")->orWhere('description', 'like', "%{$product->sku}%")->exists()) return true;

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(int|string $id): bool
    {
        /** @var Product $product */
        $product = $this->getById($id);

        if ($this->hasTransactionalHistory($product)) {
            throw new \RuntimeException("This product has transactional history and cannot be deleted. Archive or discontinue the product instead.");
        }

        return parent::delete($id);
    }
    public function restoreProduct(int|string $id): bool
    {
        /** @var ProductRepositoryInterface $repo */
        $repo = $this->repository;
        return $repo->restore($id);
    }

    /**
     * {@inheritdoc}
     */
    public function receiveStock(array $data): bool
    {
        return DB::transaction(function () use ($data) {
            /** @var Product $product */
            $product = Product::lockForUpdate()->findOrFail($data['product_id']);

            $qty = (int) $data['quantity'];
            $unitCost = (float) $data['unit_cost'];
            $totalCost = $qty * $unitCost;

            // 1. Create Stock Receipt Record
            $receipt = StockReceipt::create([
                'reference_no' => $data['reference_no'] ?? ('REC-' . strtoupper(Str::random(8))),
                'supplier_name' => $data['supplier_name'] ?? ($product->primary_supplier ?? 'General Supplier'),
                'product_id' => $product->id,
                'type' => 'receive',
                'quantity' => $qty,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'batch_number' => $data['batch_number'] ?? null,
                'mfg_date' => $data['mfg_date'] ?? null,
                'expiry_date' => $data['expiry_date'] ?? null,
                'storage_condition' => $data['storage_condition'] ?? 'Ambient Room Temperature',
                'qc_status' => $data['qc_status'] ?? 'Pending Inspection',
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id() ?? 1,
            ]);

            // 2. Create Inventory Lot
            $inventory = Inventory::create([
                'product_id' => $product->id,
                'batch_number' => $data['batch_number'] ?? ('LOT-' . strtoupper(Str::random(6))),
                'lot_number' => 'LOT-' . rand(1000, 9999),
                'quantity' => $qty,
                'unit_cost' => $unitCost,
                'selling_price' => $data['selling_price'] ?? $product->selling_price,
                'mfg_date' => $data['mfg_date'] ?? null,
                'expiry_date' => $data['expiry_date'] ?? null,
                'storage_condition' => $data['storage_condition'] ?? 'Ambient Room Temperature',
                'status' => in_array($data['qc_status'] ?? '', ['Rejected', 'Quarantined']) ? 'quarantined' : 'active',
            ]);

            // 3. Recalculate Weighted Average Cost & Increase Physical Stock
            $currentStock = (int) $product->physical_stock;
            $currentCost = (float) $product->cost_price;
            $newStock = $currentStock + $qty;

            $newAvgCost = $newStock > 0 
                ? (($currentStock * $currentCost) + $totalCost) / $newStock 
                : $unitCost;

            $product->update([
                'physical_stock' => $newStock,
                'available_stock' => max(0, $newStock - $product->reserved_stock),
                'cost_price' => $newAvgCost,
                'selling_price' => $data['selling_price'] ?? $product->selling_price,
            ]);

            // 4. Dispatch through Enterprise Integration & ERP Core Event Bus
            event(new \App\Events\Integration\InventoryReceived(
                productId: $product->id,
                quantity: $qty,
                stockReceiptId: $receipt->id,
                batchNumber: $data['batch_number'] ?? null,
                supplierName: $data['supplier_name'] ?? null,
                priority: 'normal',
                userId: auth()->id() ?? 1
            ));

            app(\App\Domain\Contracts\EventBusInterface::class)->dispatch(
                (new \App\Domain\Events\GoodsReceiptCompleted(
                    productId: $product->id,
                    quantity: $qty,
                    unitCost: $unitCost,
                    batchNumber: $data['batch_number'] ?? null,
                    storageCondition: $data['storage_condition'] ?? 'Ambient Room Temperature',
                    qcStatus: $data['qc_status'] ?? 'Pending Inspection',
                    referenceNo: $receipt->reference_no,
                    userId: auth()->id() ?? 1
                ))->eventData
            );

            return true;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function adjustStock(array $data): bool
    {
        return DB::transaction(function () use ($data) {
            /** @var Product $product */
            $product = $this->getById($data['product_id']);

            $qty = (int) $data['quantity']; // positive for addition, negative for deduction
            $unitCost = (float) $product->cost_price;
            $totalAmount = abs($qty) * $unitCost;

            // Approval Rule threshold check (Default ₹50,000)
            $approvalThreshold = 50000;
            $status = ($totalAmount > $approvalThreshold) ? 'pending' : 'approved';

            StockAdjustment::create([
                'reference_no' => $data['reference_no'] ?? ('ADJ-' . strtoupper(Str::random(8))),
                'product_id' => $product->id,
                'type' => $data['type'],
                'quantity' => $qty,
                'unit_cost' => $unitCost,
                'total_amount' => $totalAmount,
                'reason' => $data['reason'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => $status,
                'created_by' => auth()->id() ?? 1,
                'approved_by' => ($status === 'approved') ? (auth()->id() ?? 1) : null,
            ]);

            if ($status === 'approved') {
                $newStock = max(0, $product->physical_stock + $qty);
                $product->update([
                    'physical_stock' => $newStock,
                    'available_stock' => max(0, $newStock - $product->reserved_stock),
                ]);
            }

            return true;
        });
    }
}
