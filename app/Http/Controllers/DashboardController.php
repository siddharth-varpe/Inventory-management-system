<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Contracts\CompanyServiceInterface;
use App\Models\PickingTask;
use App\Models\TransportRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * CompanyServiceInterface instance.
     *
     * @var CompanyServiceInterface
     */
    protected CompanyServiceInterface $companyService;

    /**
     * DashboardController constructor.
     *
     * @param CompanyServiceInterface $companyService
     */
    public function __construct(CompanyServiceInterface $companyService)
    {
        $this->companyService = $companyService;
    }

    /**
     * Display the minimal enterprise dashboard launcher shell.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $company = $this->companyService->getPrimaryProfile();

        $actionBadges = [
            'manage_stock' => 0,
            'organize_stock' => PickingTask::whereIn('status', ['pending', 'assigned'])->count(),
            'order_supplies' => Schema::hasTable('purchase_requisitions') ? DB::table('purchase_requisitions')->where('status', 'pending')->count() : 0,
            'send_goods' => PickingTask::whereIn('status', ['packed', 'ready', 'seal_ready'])->count(),
            'transport' => TransportRequest::whereIn('status', ['requested', 'pending'])->count(),
            'driver_terminal' => TransportRequest::whereIn('status', ['assigned', 'dispatched'])->count(),
            'sales_crm' => DB::table('crm_leads')->whereIn('status', ['new', 'contacted', 'negotiating'])->count(),
            'bill_customers' => DB::table('sales_orders')->where('status', 'processing')->count(),
            'admin' => 0,
        ];

        return view('dashboard', compact('company', 'actionBadges'));
    }

    /**
     * Display the enterprise dashboard shell via single invoke.
     *
     * @param Request $request
     * @return View
     */
    public function __invoke(Request $request): View
    {
        return $this->index($request);
    }
}
