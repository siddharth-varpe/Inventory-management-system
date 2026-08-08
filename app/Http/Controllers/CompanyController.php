<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Company\UpdateCompanyRequest;
use App\Services\Contracts\CompanyServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CompanyController extends Controller
{
    /**
     * CompanyService instance.
     *
     * @var CompanyServiceInterface
     */
    protected CompanyServiceInterface $companyService;

    /**
     * CompanyController constructor.
     *
     * @param CompanyServiceInterface $companyService
     */
    public function __construct(CompanyServiceInterface $companyService)
    {
        $this->companyService = $companyService;
    }

    /**
     * Display company profile screen.
     */
    public function index(): View
    {
        $company = $this->companyService->getPrimaryProfile();

        return view('company.index', compact('company'));
    }

    /**
     * Update primary company profile.
     */
    public function update(UpdateCompanyRequest $request): RedirectResponse
    {
        $this->companyService->updateProfile(
            $request->validated(),
            $request->file('logo')
        );

        return back()->with('success', 'Company profile updated successfully.');
    }
}
