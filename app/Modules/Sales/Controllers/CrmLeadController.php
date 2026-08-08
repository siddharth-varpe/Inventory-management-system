<?php

declare(strict_types=1);

namespace App\Modules\Sales\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CrmLead;
use App\Models\Territory;
use App\Models\User;
use App\Domain\Sales\CrmAutomationEngine;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class CrmLeadController extends Controller
{
    public function __construct(
        protected CrmAutomationEngine $automationEngine
    ) {}

    /**
     * Lead Directory List View
     */
    public function index(Request $request): View
    {
        $query = CrmLead::with(['salesperson', 'territory']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where('company_name', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('lead_number', 'like', "%{$search}%");
        }

        $leads = $query->latest()->paginate(15)->withQueryString();
        $salespeople = User::all();
        $territories = Territory::all();

        return view('sales.leads.index', compact('leads', 'salespeople', 'territories'));
    }

    /**
     * Visual 6-Stage Kanban Opportunity Pipeline
     */
    public function pipeline(): View
    {
        $stages = ['new', 'contacted', 'qualified', 'proposal', 'negotiation', 'won', 'lost'];
        $leadsByStage = [];

        foreach ($stages as $st) {
            $leadsByStage[$st] = CrmLead::with(['salesperson', 'territory'])->where('status', $st)->latest()->get();
        }

        $salespeople = User::all();
        $territories = Territory::all();

        return view('sales.leads.pipeline', compact('stages', 'leadsByStage', 'salespeople', 'territories'));
    }

    /**
     * Store new Lead with Transaction Safety, Duplicate Detection & Audit Trail
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'source' => 'nullable|string|max:100',
            'industry' => 'nullable|string|max:100',
            'expected_revenue' => 'nullable|numeric|min:0',
            'probability' => 'nullable|integer|between:0,100',
            'salesperson_id' => 'nullable|exists:users,id',
            'territory_id' => 'nullable|exists:territories,id',
            'priority' => 'nullable|string|max:50',
            'expected_closing_date' => 'nullable|date',
            'remarks' => 'nullable|string',
        ]);

        // Duplicate Lead Detection
        if (!empty($validated['email']) || !empty($validated['phone'])) {
            $existing = CrmLead::where('company_name', $validated['company_name'])
                ->where(function ($q) use ($validated) {
                    if (!empty($validated['email'])) {
                        $q->orWhere('email', $validated['email']);
                    }
                    if (!empty($validated['phone'])) {
                        $q->orWhere('phone', $validated['phone']);
                    }
                })
                ->first();

            if ($existing) {
                $errorMsg = "Duplicate lead detected! Lead {$existing->lead_number} already exists for company '{$existing->company_name}'.";
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $errorMsg], 422);
                }
                return redirect()->back()->withInput()->with('error', $errorMsg);
            }
        }

        try {
            $lead = DB::transaction(function () use ($validated, $request) {
                $nextId = (int)CrmLead::max('id') + 1;
                $leadNumber = 'LEAD-' . date('Y') . '-' . str_pad((string)$nextId, 6, '0', STR_PAD_LEFT);

                $data = array_merge($validated, [
                    'lead_number' => $leadNumber,
                    'status' => 'new',
                    'priority' => $validated['priority'] ?? 'medium',
                    'source' => $validated['source'] ?? 'website',
                    'expected_revenue' => (float)($validated['expected_revenue'] ?? 0.00),
                    'probability' => (int)($validated['probability'] ?? 50),
                    'created_by' => auth()->id() ?? 1,
                ]);

                $createdLead = CrmLead::create($data);

                // Write Audit Log
                $createdLead->activities()->create([
                    'activity_type' => 'creation',
                    'subject' => "Lead Created: {$createdLead->lead_number}",
                    'description' => "Lead {$createdLead->lead_number} created for company '{$createdLead->company_name}' under stage 'New Lead' by User #" . (auth()->id() ?? 1),
                    'activity_date' => now(),
                    'user_id' => auth()->id() ?? 1,
                ]);

                return $createdLead;
            });

            $lead->load(['salesperson', 'territory']);

            $successMsg = "✓ Lead Created Successfully! ID: {$lead->lead_number} | Company: {$lead->company_name} | Salesperson: " . ($lead->salesperson->name ?? 'Unassigned') . " | Stage: New Lead";

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMsg,
                    'lead' => [
                        'id' => $lead->id,
                        'lead_number' => $lead->lead_number,
                        'company_name' => $lead->company_name,
                        'contact_person' => $lead->contact_person,
                        'phone' => $lead->phone,
                        'email' => $lead->email,
                        'expected_revenue' => (float)$lead->expected_revenue,
                        'probability' => (int)$lead->probability,
                        'priority' => $lead->priority,
                        'status' => $lead->status,
                        'stage_label' => 'New Lead',
                        'salesperson_name' => $lead->salesperson->name ?? 'Unassigned',
                        'created_at' => $lead->created_at ? $lead->created_at->format('d M Y, h:i A') : 'Just now',
                    ]
                ]);
            }

            return redirect()->route('sales.leads.pipeline')->with('success', $successMsg);

        } catch (\Exception $e) {
            $errText = "Failed to create lead: " . $e->getMessage();
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $errText], 500);
            }
            return redirect()->back()->withInput()->with('error', $errText);
        }
    }

    /**
     * 360° Lead Profile View
     */
    public function show(CrmLead $lead): View
    {
        $lead->load(['salesperson', 'territory', 'activities.user', 'meetings.createdBy', 'followups.assignedUser']);

        return view('sales.leads.show', compact('lead'));
    }

    /**
     * Update Lead Status Stage or Convert to Customer via Automation Engine
     */
    public function updateStatus(Request $request, CrmLead $lead): RedirectResponse
    {
        $request->validate(['status' => 'required|string']);

        $newStatus = $request->input('status');

        if ($newStatus === 'won') {
            $customer = $this->automationEngine->onLeadWon($lead);
            return redirect()->back()->with('success', "Lead won! Automatically created Customer Master {$customer->customer_code} ({$customer->company_name}).");
        }

        $lead->update(['status' => $newStatus]);
        return redirect()->back()->with('success', "Lead status updated to " . strtoupper($newStatus));
    }
}
