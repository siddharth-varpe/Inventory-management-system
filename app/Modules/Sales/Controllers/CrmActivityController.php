<?php

declare(strict_types=1);

namespace App\Modules\Sales\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CrmActivity;
use App\Models\CrmFollowup;
use App\Models\CrmMeeting;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class CrmActivityController extends Controller
{
    public function storeActivity(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'lead_id' => 'nullable|exists:crm_leads,id',
            'customer_id' => 'nullable|exists:customers,id',
            'activity_type' => 'required|string',
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'activity_date' => 'required|date',
        ]);

        $validated['user_id'] = auth()->id() ?? 1;

        CrmActivity::create($validated);

        return redirect()->back()->with('success', 'CRM activity logged successfully.');
    }

    public function storeFollowup(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'lead_id' => 'nullable|exists:crm_leads,id',
            'customer_id' => 'nullable|exists:customers,id',
            'title' => 'required|string|max:255',
            'due_date' => 'required|date',
            'priority' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $validated['assigned_user_id'] = auth()->id() ?? 1;
        $validated['status'] = 'pending';

        CrmFollowup::create($validated);

        return redirect()->back()->with('success', 'Follow-up scheduled successfully.');
    }

    public function storeMeeting(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'lead_id' => 'nullable|exists:crm_leads,id',
            'customer_id' => 'nullable|exists:customers,id',
            'title' => 'required|string|max:255',
            'meeting_date' => 'required|date',
            'location' => 'nullable|string',
            'meeting_type' => 'required|string',
            'agenda' => 'nullable|string',
        ]);

        $validated['created_by'] = auth()->id() ?? 1;

        CrmMeeting::create($validated);

        return redirect()->back()->with('success', 'Meeting scheduled successfully.');
    }
}
