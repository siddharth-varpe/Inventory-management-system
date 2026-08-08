<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\Contracts\CategoryServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * CategoryServiceInterface instance.
     *
     * @var CategoryServiceInterface
     */
    protected CategoryServiceInterface $categoryService;

    /**
     * CategoryController constructor.
     *
     * @param CategoryServiceInterface $categoryService
     */
    public function __construct(CategoryServiceInterface $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * Display category listing.
     */
    public function index(Request $request): View
    {
        $categories = $this->categoryService->paginate(15);
        $allCategories = $this->categoryService->getAll();

        return view('categories.index', compact('categories', 'allCategories'));
    }

    /**
     * Display category tree view.
     */
    public function tree(): View
    {
        $tree = $this->categoryService->getCategoryTree();

        return view('categories.tree', compact('tree'));
    }

    /**
     * Store new category.
     */
    public function store(StoreCategoryRequest $request)
    {
        $category = $this->categoryService->create($request->validated());

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Category created successfully.',
                'data' => $category,
            ]);
        }

        return back()->with('success', 'Product category created successfully.');
    }

    /**
     * Update category.
     */
    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->categoryService->update($category->id, $request->validated());

        return back()->with('success', 'Product category updated successfully.');
    }

    /**
     * Delete category (with sub-category check).
     */
    public function destroy(Category $category): RedirectResponse
    {
        $this->categoryService->safeDelete($category->id);

        return back()->with('success', 'Product category removed successfully.');
    }

    /**
     * Restore soft deleted category.
     */
    public function restore(int $id): RedirectResponse
    {
        $this->categoryService->restoreCategory($id);

        return back()->with('success', 'Product category restored successfully.');
    }
}
