<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Branch\StoreBranchRequest;
use App\Http\Requests\Branch\UpdateBranchRequest;
use App\Models\Branch;
use App\Services\Contracts\BranchServiceInterface;
use App\Services\Contracts\CompanyServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BranchController extends Controller
{
    /**
     * BranchService instance.
     *
     * @var BranchServiceInterface
     */
    protected BranchServiceInterface $branchService;

    /**
     * CompanyService instance.
     *
     * @var CompanyServiceInterface
     */
    protected CompanyServiceInterface $companyService;

    /**
     * BranchController constructor.
     *
     * @param BranchServiceInterface $branchService
     * @param CompanyServiceInterface $companyService
     */
    public function __construct(BranchServiceInterface $branchService, CompanyServiceInterface $companyService)
    {
        $this->branchService = $branchService;
        $this->companyService = $companyService;
    }

    /**
     * Display branch listing.
     */
    public function index(): View
    {
        $branches = $this->branchService->paginate(15);
        $company = $this->companyService->getPrimaryProfile();

        return view('branches.index', compact('branches', 'company'));
    }

    /**
     * Store new branch.
     */
    public function store(StoreBranchRequest $request): RedirectResponse
    {
        $company = $this->companyService->getPrimaryProfile();
        $data = array_merge($request->validated(), ['company_id' => $company->id]);

        $this->branchService->create($data);

        return back()->with('success', 'Branch created successfully.');
    }

    /**
     * Update existing branch.
     */
    public function update(UpdateBranchRequest $request, Branch $branch): RedirectResponse
    {
        $this->branchService->update($branch->id, $request->validated());

        return back()->with('success', 'Branch updated successfully.');
    }

    /**
     * Soft delete branch.
     */
    public function destroy(Branch $branch): RedirectResponse
    {
        $this->branchService->delete($branch->id);

        return back()->with('success', 'Branch removed successfully.');
    }
}
