<?php

declare(strict_types=1);

namespace App\Http\Requests\ProductAttribute;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductAttributeRequest extends FormRequest
{
    /**
     * Determine if user is authorized.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get validation rules.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:product_attributes,code'],
            'type' => ['required', 'string', 'in:text,number,decimal,date,boolean,dropdown,multi_select'],
            'options' => ['nullable', 'string'], // CSV or lines to be converted to array
            'is_required' => ['nullable', 'boolean'],
            'is_searchable' => ['nullable', 'boolean'],
            'is_filterable' => ['nullable', 'boolean'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ];
    }
}
