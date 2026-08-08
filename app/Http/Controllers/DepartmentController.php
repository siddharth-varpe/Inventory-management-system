<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Department\StoreDepartmentRequest;
use App\Http\Requests\Department\UpdateDepartmentRequest;
use App\Models\Department;
use App\Services\Contracts\DepartmentServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    /**
     * DepartmentService instance.
     *
     * @var DepartmentServiceInterface
     */
    protected DepartmentServiceInterface $departmentService;

    /**
     * DepartmentController constructor.
     *
     * @param DepartmentServiceInterface $departmentService
     */
    public function __construct(DepartmentServiceInterface $departmentService)
    {
        $this->departmentService = $departmentService;
    }

    /**
     * Display department listing.
     */
    public function index(): View
    {
        $departments = $this->departmentService->paginate(15);

        return view('departments.index', compact('departments'));
    }

    /**
     * Store new department.
     */
    public function store(StoreDepartmentRequest $request): RedirectResponse
    {
        $this->departmentService->create($request->validated());

        return back()->with('success', 'Department created successfully.');
    }

    /**
     * Update department.
     */
    public function update(UpdateDepartmentRequest $request, Department $department): RedirectResponse
    {
        $this->departmentService->update($department->id, $request->validated());

        return back()->with('success', 'Department updated successfully.');
    }

    /**
     * Soft delete department.
     */
    public function destroy(Department $department): RedirectResponse
    {
        $this->departmentService->delete($department->id);

        return back()->with('success', 'Department removed successfully.');
    }
}
