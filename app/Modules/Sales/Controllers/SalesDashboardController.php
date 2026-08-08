<?php

declare(strict_types=1);

namespace App\Modules\Sales\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\Territory;
use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Models\CrmLead;
use Illuminate\View\View;

class SalesDashboardController extends Controller
{
    /**
     * Display the Executive Commercial Intelligence & Sales CRM Dashboard.
     */
    public function index(): View
    {
        $totalCustomers = Customer::count();
        $activeCustomers = Customer::where('status', 'active')->count();
        $blockedCustomers = Customer::where('status', 'blocked')->count();
        $totalTerritories = Territory::count();

        // Live Commercial KPIs
        $totalRevenue = SalesOrder::whereIn('status', ['reserved', 'approved', 'ready_for_dispatch', 'completed'])->sum('grand_total');
        $todaysRevenue = SalesOrder::whereIn('status', ['reserved', 'approved', 'ready_for_dispatch', 'completed'])->whereDate('created_at', date('Y-m-d'))->sum('grand_total');
        $todaysOrdersCount = SalesOrder::whereDate('created_at', date('Y-m-d'))->count();

        $pendingQuotationsCount = Quotation::where('status', 'pending_approval')->count();
        $pendingOrdersCount = SalesOrder::whereIn('status', ['draft', 'pending_approval'])->count();

        $totalLeads = CrmLead::count();
        $wonLeads = CrmLead::where('status', 'won')->count();
        $leadConversionRate = $totalLeads > 0 ? round(($wonLeads / $totalLeads) * 100, 1) : 0;

        $recentCustomers = Customer::with(['group', 'category', 'territory'])
            ->latest()
            ->take(5)
            ->get();

        $groups = CustomerGroup::withCount('customers')->get();
        $territories = Territory::withCount('customers')->get();

        return view('sales.dashboard', compact(
            'totalCustomers',
            'activeCustomers',
            'blockedCustomers',
            'totalTerritories',
            'totalRevenue',
            'todaysRevenue',
            'todaysOrdersCount',
            'pendingQuotationsCount',
            'pendingOrdersCount',
            'totalLeads',
            'wonLeads',
            'leadConversionRate',
            'recentCustomers',
            'groups',
            'territories'
        ));
    }
}
