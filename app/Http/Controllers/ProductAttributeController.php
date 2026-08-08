<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ProductAttribute\StoreProductAttributeRequest;
use App\Http\Requests\ProductAttribute\UpdateProductAttributeRequest;
use App\Models\ProductAttribute;
use App\Services\Contracts\ProductAttributeServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductAttributeController extends Controller
{
    /**
     * ProductAttributeServiceInterface instance.
     *
     * @var ProductAttributeServiceInterface
     */
    protected ProductAttributeServiceInterface $attributeService;

    /**
     * ProductAttributeController constructor.
     *
     * @param ProductAttributeServiceInterface $attributeService
     */
    public function __construct(ProductAttributeServiceInterface $attributeService)
    {
        $this->attributeService = $attributeService;
    }

    /**
     * Display attributes listing.
     */
    public function index(): View
    {
        $attributes = $this->attributeService->paginate(15);

        return view('attributes.index', compact('attributes'));
    }

    /**
     * Store new attribute.
     */
    public function store(StoreProductAttributeRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if (!empty($data['options'])) {
            $data['options'] = array_map('trim', explode(',', $data['options']));
        }

        $this->attributeService->create($data);

        return back()->with('success', 'Product attribute created successfully.');
    }

    /**
     * Update attribute.
     */
    public function update(UpdateProductAttributeRequest $request, ProductAttribute $attribute): RedirectResponse
    {
        $data = $request->validated();
        if (!empty($data['options'])) {
            $data['options'] = array_map('trim', explode(',', $data['options']));
        }

        $this->attributeService->update($attribute->id, $data);

        return back()->with('success', 'Product attribute updated successfully.');
    }

    /**
     * Soft delete attribute.
     */
    public function destroy(ProductAttribute $attribute): RedirectResponse
    {
        $this->attributeService->delete($attribute->id);

        return back()->with('success', 'Product attribute removed successfully.');
    }
}
