<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Brand\StoreBrandRequest;
use App\Http\Requests\Brand\UpdateBrandRequest;
use App\Models\Brand;
use App\Services\Contracts\BrandServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BrandController extends Controller
{
    /**
     * BrandServiceInterface instance.
     *
     * @var BrandServiceInterface
     */
    protected BrandServiceInterface $brandService;

    /**
     * BrandController constructor.
     *
     * @param BrandServiceInterface $brandService
     */
    public function __construct(BrandServiceInterface $brandService)
    {
        $this->brandService = $brandService;
    }

    /**
     * Display brand listing.
     */
    public function index(): View
    {
        $brands = $this->brandService->paginate(15);

        return view('brands.index', compact('brands'));
    }

    /**
     * Store new brand.
     */
    public function store(StoreBrandRequest $request)
    {
        $brand = $this->brandService->createBrand(
            $request->validated(),
            $request->file('logo')
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Brand created successfully.',
                'data' => $brand,
            ]);
        }

        return back()->with('success', 'Brand created successfully.');
    }

    /**
     * Update existing brand.
     */
    public function update(UpdateBrandRequest $request, Brand $brand): RedirectResponse
    {
        $this->brandService->updateBrand(
            $brand->id,
            $request->validated(),
            $request->file('logo')
        );

        return back()->with('success', 'Brand updated successfully.');
    }

    /**
     * Soft delete brand.
     */
    public function destroy(Brand $brand): RedirectResponse
    {
        $this->brandService->delete($brand->id);

        return back()->with('success', 'Brand removed successfully.');
    }

    /**
     * Restore soft deleted brand.
     */
    public function restore(int $id): RedirectResponse
    {
        $this->brandService->restoreBrand($id);

        return back()->with('success', 'Brand restored successfully.');
    }
}
