<?php

declare(strict_types=1);

namespace App\Modules\Procurement\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequisition;
use App\Models\Supplier;
use Illuminate\View\View;

class ProcurementReportController extends Controller
{
    public function index(): View
    {
        $metrics = [
            'total_suppliers' => Supplier::count(),
            'total_po_value' => PurchaseOrder::sum('total_amount'),
            'total_requisitions' => PurchaseRequisition::count(),
        ];

        return view('procurement.reports', compact('metrics'));
    }
}
