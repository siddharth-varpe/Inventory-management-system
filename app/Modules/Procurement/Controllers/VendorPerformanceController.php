<?php

declare(strict_types=1);

namespace App\Modules\Procurement\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\View\View;

class VendorPerformanceController extends Controller
{
    public function index(): View
    {
        $suppliers = Supplier::where('status', 'active')->get();
        return view('procurement.vendor-performance', compact('suppliers'));
    }
}
