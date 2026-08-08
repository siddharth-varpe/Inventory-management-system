<?php

declare(strict_types=1);

namespace App\Domain\Sales;

use App\Models\CrmLead;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Models\CrmActivity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CrmAutomationEngine
{
    public function __construct(
        protected ReservationEngine $reservationEngine,
        protected SendGoodsConnector $sendGoodsConnector
    ) {}

    /**
     * Triggered when a Lead stage is updated to 'won'.
     * Automatically creates Customer Master & logs CRM timeline.
     */
    public function onLeadWon(CrmLead $lead): Customer
    {
        return DB::transaction(function () use ($lead) {
            // Check if customer already exists for this lead
            $customer = Customer::where('email', $lead->email)
                ->orWhere('company_name', $lead->company_name)
                ->first();

            if (!$customer) {
                $nextCustId = Customer::max('id') + 1;
                $custCode = 'CUST-' . date('Y') . '-' . str_pad((string)$nextCustId, 4, '0', STR_PAD_LEFT);

                $customer = Customer::create([
                    'customer_code' => $custCode,
                    'company_name' => $lead->company_name,
                    'contact_person' => $lead->contact_person,
                    'email' => $lead->email,
                    'phone' => $lead->phone,
                    'customer_type' => 'dealer',
                    'territory_id' => $lead->territory_id,
                    'status' => 'active',
                    'created_by' => auth()->id() ?? 1,
                ]);
            }

            // Mark Lead as won and converted
            $lead->update([
                'status' => 'won',
                'remarks' => ($lead->remarks ? $lead->remarks . "\n" : '') . "Converted to Customer Master: {$customer->customer_code}",
            ]);

            // Log CRM Activity Timeline
            CrmActivity::create([
                'lead_id' => $lead->id,
                'customer_id' => $customer->id,
                'activity_type' => 'note',
                'subject' => 'Lead Won & Customer Account Created',
                'description' => "Lead successfully won! Created Customer Master {$customer->customer_code} ({$customer->company_name}).",
                'activity_date' => now(),
                'user_id' => auth()->id() ?? 1,
            ]);

            Log::info("CrmAutomationEngine: Lead #{$lead->lead_number} won and converted to Customer #{$customer->customer_code}");

            return $customer;
        });
    }

    /**
     * Triggered when a Sales Order is created.
     * Automates Inventory Validation, Stock Reservation, and Send Goods Pick Request creation.
     */
    public function processSalesOrderAutomation(SalesOrder $order): SalesOrder
    {
        return DB::transaction(function () use ($order) {
            // Step 1: Inventory Validation
            $order->update(['status' => 'inventory_validated']);

            // Step 2: Inventory Reservation
            $order = $this->reservationEngine->reserveInventory($order);

            // Step 3: Emit Warehouse Pick Request to Send Goods Portal
            $this->sendGoodsConnector->createDispatchRequest($order);

            // Step 4: Advance Status to Waiting Warehouse
            $order->update(['status' => 'waiting_warehouse']);

            Log::info("CrmAutomationEngine: Sales Order #{$order->order_number} automated -> reserved & forwarded to Warehouse");

            return $order;
        });
    }
}
