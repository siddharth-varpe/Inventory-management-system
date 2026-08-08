<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Unit\StoreUnitRequest;
use App\Http\Requests\Unit\UpdateUnitRequest;
use App\Models\Unit;
use App\Services\Contracts\UnitServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UnitController extends Controller
{
    /**
     * UnitServiceInterface instance.
     *
     * @var UnitServiceInterface
     */
    protected UnitServiceInterface $unitService;

    /**
     * UnitController constructor.
     *
     * @param UnitServiceInterface $unitService
     */
    public function __construct(UnitServiceInterface $unitService)
    {
        $this->unitService = $unitService;
    }

    /**
     * Display unit listing.
     */
    public function index(): View
    {
        $units = $this->unitService->paginate(15);

        return view('units.index', compact('units'));
    }

    /**
     * Store new unit.
     */
    public function store(StoreUnitRequest $request): RedirectResponse
    {
        $this->unitService->create($request->validated());

        return back()->with('success', 'Unit of measurement created successfully.');
    }

    /**
     * Update unit.
     */
    public function update(UpdateUnitRequest $request, Unit $unit): RedirectResponse
    {
        $this->unitService->update($unit->id, $request->validated());

        return back()->with('success', 'Unit of measurement updated successfully.');
    }

    /**
     * Soft delete unit.
     */
    public function destroy(Unit $unit): RedirectResponse
    {
        $this->unitService->delete($unit->id);

        return back()->with('success', 'Unit of measurement removed successfully.');
    }
}
