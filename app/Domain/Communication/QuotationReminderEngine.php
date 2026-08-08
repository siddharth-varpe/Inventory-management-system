<?php

declare(strict_types=1);

namespace App\Domain\Communication;

use App\Models\Quotation;
use App\Models\CommunicationRecord;
use Illuminate\Support\Carbon;

class QuotationReminderEngine
{
    /**
     * Get active follow-up reminders for Sales Dashboard & CRM.
     */
    public function getPendingReminders(): array
    {
        $reminders = [];

        // 1. Unviewed Quotations (Delivered > 2 days ago but not viewed)
        $unviewedCommRecords = CommunicationRecord::where('related_document_type', 'Quotation')
            ->whereIn('status', ['delivered', 'sent', 'prepared'])
            ->where('created_at', '<=', Carbon::now()->subDays(2))
            ->with(['customer'])
            ->get();

        foreach ($unviewedCommRecords as $cce) {
            $quotation = Quotation::find($cce->related_document_id);
            if ($quotation && $quotation->status !== 'converted') {
                $reminders[] = [
                    'type' => 'unviewed',
                    'severity' => 'warning',
                    'title' => "Proposal Unviewed",
                    'quotation_id' => $quotation->id,
                    'quotation_number' => $quotation->quotation_number,
                    'customer_name' => $cce->customer_name ?? $quotation->customer->company_name ?? 'Account',
                    'message' => "Quotation #{$quotation->quotation_number} for {$cce->customer_name} delivered on " . ($cce->last_delivered_at ? $cce->last_delivered_at->format('d M Y') : $cce->created_at->format('d M Y')) . " has not been viewed yet.",
                    'created_at' => $cce->created_at->format('d M Y, h:i A'),
                    'cce_number' => $cce->communication_number,
                ];
            }
        }

        // 2. Unaccepted Quotations (Viewed > 3 days ago, approved but not converted)
        $viewedCommRecords = CommunicationRecord::where('related_document_type', 'Quotation')
            ->whereIn('status', ['viewed', 'downloaded'])
            ->where('last_viewed_at', '<=', Carbon::now()->subDays(3))
            ->with(['customer'])
            ->get();

        foreach ($viewedCommRecords as $cce) {
            $quotation = Quotation::find($cce->related_document_id);
            if ($quotation && in_array($quotation->status, ['approved', 'customer_accepted'])) {
                $reminders[] = [
                    'type' => 'unaccepted',
                    'severity' => 'info',
                    'title' => "Awaiting Customer Decision",
                    'quotation_id' => $quotation->id,
                    'quotation_number' => $quotation->quotation_number,
                    'customer_name' => $cce->customer_name ?? $quotation->customer->company_name ?? 'Account',
                    'message' => "Quotation #{$quotation->quotation_number} viewed on " . ($cce->last_viewed_at ? $cce->last_viewed_at->format('d M Y') : 'recently') . " is awaiting customer acceptance / conversion.",
                    'created_at' => $cce->last_viewed_at ? $cce->last_viewed_at->format('d M Y, h:i A') : 'N/A',
                    'cce_number' => $cce->communication_number,
                ];
            }
        }

        // 3. Expired Quotations
        $expiredQuotations = Quotation::where('status', '!=', 'converted')
            ->whereNotNull('validity_date')
            ->where('validity_date', '<', Carbon::today())
            ->with(['customer'])
            ->take(10)
            ->get();

        foreach ($expiredQuotations as $q) {
            $reminders[] = [
                'type' => 'expired',
                'severity' => 'danger',
                'title' => "Quotation Expired",
                'quotation_id' => $q->id,
                'quotation_number' => $q->quotation_number,
                'customer_name' => $q->customer->company_name ?? 'Account',
                'message' => "Quotation #{$q->quotation_number} expired on " . $q->validity_date->format('d M Y') . " and requires salesperson renewal.",
                'created_at' => $q->validity_date->format('d M Y'),
                'cce_number' => null,
            ];
        }

        return $reminders;
    }
}
