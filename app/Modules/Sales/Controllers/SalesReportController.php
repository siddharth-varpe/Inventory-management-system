<?php

declare(strict_types=1);

namespace App\Modules\Sales\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CrmLead;
use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Models\InventoryReservation;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesReportController extends Controller
{
    /**
     * Commercial Reports Engine Center
     */
    public function index(Request $request): View
    {
        $reportType = $request->input('report_type', 'orders');

        $metrics = [
            'total_sales' => SalesOrder::whereIn('status', ['reserved', 'approved', 'ready_for_dispatch', 'completed'])->sum('grand_total'),
            'total_orders' => SalesOrder::count(),
            'total_quotations' => Quotation::count(),
            'total_leads' => CrmLead::count(),
            'total_customers' => Customer::count(),
            'total_reservations' => InventoryReservation::where('status', 'active')->sum('reserved_qty'),
        ];

        $orders = SalesOrder::with(['customer', 'salesperson'])->latest()->paginate(15);
        $quotations = Quotation::with(['customer', 'salesperson'])->latest()->paginate(15);
        $leads = CrmLead::with(['salesperson'])->latest()->paginate(15);
        $customers = Customer::with(['territory'])->latest()->paginate(15);

        return view('sales.reports.index', compact('reportType', 'metrics', 'orders', 'quotations', 'leads', 'customers'));
    }

    /**
     * Export Commercial Report to CSV
     */
    public function export(Request $request): StreamedResponse
    {
        $reportType = $request->input('report_type', 'orders');

        $response = new StreamedResponse(function () use ($reportType) {
            $handle = fopen('php://output', 'w');

            if ($reportType === 'orders') {
                fputcsv($handle, ['Order Number', 'Customer', 'Order Date', 'Taxable Amount', 'CGST', 'SGST', 'IGST', 'Grand Total', 'Status']);
                SalesOrder::with('customer')->chunk(100, function ($orders) use ($handle) {
                    foreach ($orders as $o) {
                        fputcsv($handle, [
                            $o->order_number,
                            $o->customer->company_name ?? 'N/A',
                            $o->order_date ? $o->order_date->format('Y-m-d') : '',
                            $o->taxable_amount,
                            $o->cgst_amount,
                            $o->sgst_amount,
                            $o->igst_amount,
                            $o->grand_total,
                            $o->status,
                        ]);
                    }
                });
            } else {
                fputcsv($handle, ['Customer Code', 'Company Name', 'Contact Person', 'Type', 'Status']);
                Customer::chunk(100, function ($customers) use ($handle) {
                    foreach ($customers as $c) {
                        fputcsv($handle, [
                            $c->customer_code,
                            $c->company_name,
                            $c->contact_person,
                            $c->customer_type,
                            $c->status,
                        ]);
                    }
                });
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="sales_report_' . $reportType . '_' . date('Y-m-d') . '.csv"');

        return $response;
    }
}
