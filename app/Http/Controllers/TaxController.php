<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Tax\StoreTaxRequest;
use App\Http\Requests\Tax\UpdateTaxRequest;
use App\Models\Tax;
use App\Services\Contracts\TaxServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TaxController extends Controller
{
    /**
     * TaxServiceInterface instance.
     *
     * @var TaxServiceInterface
     */
    protected TaxServiceInterface $taxService;

    /**
     * TaxController constructor.
     *
     * @param TaxServiceInterface $taxService
     */
    public function __construct(TaxServiceInterface $taxService)
    {
        $this->taxService = $taxService;
    }

    /**
     * Display tax rule listing.
     */
    public function index(): View
    {
        $taxes = $this->taxService->paginate(15);

        return view('taxes.index', compact('taxes'));
    }

    /**
     * Store new tax rule.
     */
    public function store(StoreTaxRequest $request): RedirectResponse
    {
        $this->taxService->create($request->validated());

        return back()->with('success', 'Tax rule created successfully.');
    }

    /**
     * Update tax rule.
     */
    public function update(UpdateTaxRequest $request, Tax $tax): RedirectResponse
    {
        $this->taxService->update($tax->id, $request->validated());

        return back()->with('success', 'Tax rule updated successfully.');
    }

    /**
     * Soft delete tax rule.
     */
    public function destroy(Tax $tax): RedirectResponse
    {
        $this->taxService->delete($tax->id);

        return back()->with('success', 'Tax rule removed successfully.');
    }
}
