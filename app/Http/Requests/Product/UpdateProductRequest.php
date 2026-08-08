<?php

declare(strict_types=1);

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if user is authorized.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare inputs for validation.
     */
    protected function prepareForValidation(): void
    {
        $sanitizeFk = function ($val) {
            if ($val === null || $val === '' || $val === '0' || $val === 0 || $val === 'null' || $val === 'undefined') {
                return null;
            }
            return (int) $val;
        };

        $this->merge([
            'category_id' => $sanitizeFk($this->input('category_id')),
            'brand_id' => $sanitizeFk($this->input('brand_id')),
            'unit_id' => $sanitizeFk($this->input('unit_id')),
            'tax_id' => $sanitizeFk($this->input('tax_id')),
            'track_inventory' => $this->boolean('track_inventory', true),
            'batch_tracking' => $this->boolean('batch_tracking', false),
            'expiry_tracking' => $this->boolean('expiry_tracking', false),
            'purchase_price' => $this->input('purchase_price') !== null ? (float)$this->input('purchase_price') : 0.00,
            'cost_price' => $this->input('cost_price') !== null ? (float)$this->input('cost_price') : 0.00,
            'selling_price' => $this->input('selling_price') !== null ? (float)$this->input('selling_price') : 0.00,
        ]);
    }

    /**
     * Get validation rules.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $product = $this->route('product');
        $productId = is_object($product) ? $product->id : $product;

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:products,code,'.$productId],
            'sku' => ['required', 'string', 'max:50', 'unique:products,sku,'.$productId],
            'barcode' => ['nullable', 'string', 'max:50', 'unique:products,barcode,'.$productId],
            'qr_code' => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'tax_id' => ['nullable', 'exists:taxes,id'],
            'product_type' => ['required', 'string', 'in:single,variant,batch,combo'],
            'status' => ['required', 'string', 'in:active,inactive,archived'],
            'description' => ['nullable', 'string'],
            'internal_notes' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'documents.*' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,png,jpg', 'max:5120'],

            // Supplier & MOQ
            'primary_supplier' => ['nullable', 'string', 'max:255'],
            'moq' => ['nullable', 'integer', 'min:1'],

            // Pricing
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'mrp' => ['nullable', 'numeric', 'min:0'],
            'dealer_price' => ['nullable', 'numeric', 'min:0'],
            'wholesale_price' => ['nullable', 'numeric', 'min:0'],
            'min_selling_price' => ['nullable', 'numeric', 'min:0'],

            // Inventory Tracking Flags & Thresholds
            'track_inventory' => ['nullable', 'boolean'],
            'batch_tracking' => ['nullable', 'boolean'],
            'serial_tracking' => ['nullable', 'boolean'],
            'expiry_tracking' => ['nullable', 'boolean'],
            'min_stock' => ['nullable', 'integer', 'min:0'],
            'max_stock' => ['nullable', 'integer', 'min:0'],
            'reorder_level' => ['nullable', 'integer', 'min:0'],
            'warehouse_location' => ['nullable', 'string', 'max:255'],
            'rack_location' => ['nullable', 'string', 'max:255'],
            'storage_condition' => ['nullable', 'string', 'max:255'],

            // Dynamic Attribute Values
            'attribute_values' => ['nullable', 'array'],
        ];
    }
}
