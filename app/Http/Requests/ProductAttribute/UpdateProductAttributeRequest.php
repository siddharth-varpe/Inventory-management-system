<?php

declare(strict_types=1);

namespace App\Http\Requests\ProductAttribute;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductAttributeRequest extends FormRequest
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
        $attrId = $this->route('attribute') ? $this->route('attribute')->id : null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:product_attributes,code,'.$attrId],
            'type' => ['required', 'string', 'in:text,number,decimal,date,boolean,dropdown,multi_select'],
            'options' => ['nullable', 'string'],
            'is_required' => ['nullable', 'boolean'],
            'is_searchable' => ['nullable', 'boolean'],
            'is_filterable' => ['nullable', 'boolean'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ];
    }
}
