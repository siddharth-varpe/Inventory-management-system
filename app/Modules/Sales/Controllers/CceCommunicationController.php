<?php

declare(strict_types=1);

namespace App\Modules\Sales\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CommunicationRecord;
use App\Models\Quotation;
use App\Domain\Communication\CommunicationEngineService;
use App\Domain\Communication\CommunicationDeliveryEngine;
use App\Domain\Communication\QuotationReminderEngine;
use App\Domain\Communication\SimplifiedCommunicationLauncher;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CceCommunicationController extends Controller
{
    public function __construct(
        protected CommunicationEngineService $cceService,
        protected CommunicationDeliveryEngine $deliveryEngine,
        protected QuotationReminderEngine $reminderEngine,
        protected SimplifiedCommunicationLauncher $launcher
    ) {}

    /**
     * Launch Client-side Communication (mailto: or wa.me) with Secure Document Link & CCE Logging.
     */
    public function launch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'quotation_id' => 'required|exists:quotations,id',
            'channel' => 'required|string|in:email,whatsapp',
            'subject' => 'nullable|string|max:255',
            'message_preview' => 'nullable|string',
        ]);

        $quotation = Quotation::findOrFail($validated['quotation_id']);

        try {
            $payload = $this->launcher->launch(
                $quotation,
                $validated['channel'],
                [
                    'subject' => $validated['subject'] ?? null,
                    'message_preview' => $validated['message_preview'] ?? null,
                ],
                auth()->id() ?? 1
            );

            return response()->json([
                'success' => true,
                'message' => "✓ Opened " . strtoupper($validated['channel']) . " launcher for Quotation #{$quotation->quotation_number}!",
                'payload' => $payload,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Dispatch Commercial Communication via Channel (Email, WhatsApp).
     */
    public function dispatch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'communication_id' => 'required|exists:communication_records,id',
            'channel' => 'required|string|in:email,whatsapp',
            'subject' => 'nullable|string|max:255',
            'message_preview' => 'nullable|string',
            'recipient_email' => 'nullable|email|max:255',
            'recipient_mobile' => 'nullable|string|max:50',
        ]);

        $record = CommunicationRecord::findOrFail($validated['communication_id']);

        $options = [
            'subject' => $validated['subject'] ?? null,
            'message_preview' => $validated['message_preview'] ?? null,
            'recipient_email' => $validated['recipient_email'] ?? null,
            'recipient_mobile' => $validated['recipient_mobile'] ?? null,
        ];

        $updatedRecord = $this->deliveryEngine->dispatch($record, $validated['channel'], $options, auth()->id() ?? 1);

        $channelName = strtoupper($validated['channel']);
        $msg = $updatedRecord->status === 'delivered' 
            ? "✓ Commercial Quotation proposal dispatched successfully via {$channelName}!"
            : "⚠️ Communication dispatch failed: " . ($updatedRecord->failure_reason ?? 'Channel error');

        return response()->json([
            'success' => ($updatedRecord->status === 'delivered'),
            'message' => $msg,
            'record' => $updatedRecord->load(['customer', 'timelines', 'auditLogs']),
        ]);
    }

    /**
     * Retry Failed Communication.
     */
    public function retry(CommunicationRecord $record): JsonResponse
    {
        try {
            $updatedRecord = $this->deliveryEngine->retry($record, auth()->id() ?? 1);

            return response()->json([
                'success' => ($updatedRecord->status === 'delivered'),
                'message' => "✓ Retried communication #{$record->communication_number} dispatch via " . strtoupper($updatedRecord->preferred_channel) . "!",
                'record' => $updatedRecord->load(['customer', 'timelines', 'auditLogs']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Retry failed: " . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get Record Preview Data.
     */
    public function preview(CommunicationRecord $record): JsonResponse
    {
        $record->load(['customer', 'timelines', 'auditLogs']);

        return response()->json([
            'success' => true,
            'record' => $record,
            'pdf_url' => route('sales.quotations.pdf', $record->related_document_id),
        ]);
    }

    /**
     * Track Recipient Lifecycle Engagement Event.
     */
    public function track(Request $request, CommunicationRecord $record): JsonResponse
    {
        $request->validate(['event' => 'required|string|in:viewed,downloaded,completed']);

        $event = $request->input('event');
        $updatedRecord = $this->deliveryEngine->trackEvent($record, $event, auth()->id() ?? 1);

        return response()->json([
            'success' => true,
            'message' => "Event '{$event}' logged for Communication #{$record->communication_number}.",
            'record' => $updatedRecord->load(['timelines']),
        ]);
    }

    /**
     * Get Follow-up Reminders for Sales Dashboard.
     */
    public function reminders(): JsonResponse
    {
        $reminders = $this->reminderEngine->getPendingReminders();

        return response()->json([
            'success' => true,
            'reminders' => $reminders,
            'count' => count($reminders),
        ]);
    }
}
