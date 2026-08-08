<?php

declare(strict_types=1);

namespace App\Services\Timeline;

use Illuminate\Database\Eloquent\Model;
use App\Models\CrmLead;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\SalesOrder;

class UniversalTimelineService
{
    /**
     * Build unified, chronologically sorted workflow timeline events for ANY ERP document or entity model.
     */
    public function getTimelineEvents(Model $model): array
    {
        $events = [];

        // 1. Initial Creation Event
        $events[] = [
            'icon' => '✨',
            'status' => 'created',
            'title' => class_basename($model) . ' Initialized',
            'description' => 'Document record created in enterprise database system.',
            'user_name' => $model->createdBy->name ?? ($model->user->name ?? ($model->salesperson->name ?? 'System Admin')),
            'date' => $model->created_at ? $model->created_at->format('d M Y') : 'N/A',
            'time' => $model->created_at ? $model->created_at->format('h:i A') : '',
            'timestamp' => $model->created_at ? $model->created_at->timestamp : time(),
            'badge_color' => 'bg-primary',
            'event_type' => 'creation',
        ];

        // 2. Specific Entity Handlers
        if ($model instanceof CrmLead) {
            foreach ($model->activities as $act) {
                $events[] = [
                    'icon' => $this->getActivityIcon($act->activity_type),
                    'status' => $act->activity_type,
                    'title' => ucfirst($act->activity_type) . ': ' . $act->subject,
                    'description' => $act->description ?? 'No details provided.',
                    'user_name' => $act->user->name ?? 'Sales Exec',
                    'date' => $act->activity_date->format('d M Y'),
                    'time' => $act->activity_date->format('h:i A'),
                    'timestamp' => $act->activity_date->timestamp,
                    'badge_color' => 'bg-info',
                    'event_type' => 'activity',
                ];
            }

            foreach ($model->meetings as $mtg) {
                $events[] = [
                    'icon' => '🤝',
                    'status' => 'meeting',
                    'title' => 'Meeting: ' . $mtg->title,
                    'description' => 'Location: ' . ($mtg->location ?? 'Online') . ' | Agenda: ' . ($mtg->agenda ?? 'Commercial Discussion'),
                    'user_name' => $mtg->createdBy->name ?? 'Sales Exec',
                    'date' => $mtg->meeting_date->format('d M Y'),
                    'time' => $mtg->meeting_date->format('h:i A'),
                    'timestamp' => $mtg->meeting_date->timestamp,
                    'badge_color' => 'bg-warning text-dark',
                    'event_type' => 'meeting',
                ];
            }

            foreach ($model->followups as $flw) {
                $events[] = [
                    'icon' => '⏰',
                    'status' => $flw->status,
                    'title' => 'Follow-up Scheduled: ' . $flw->title,
                    'description' => 'Status: ' . strtoupper($flw->status) . ' | Assigned to: ' . ($flw->assignedUser->name ?? 'Team Member'),
                    'user_name' => $flw->assignedUser->name ?? 'System',
                    'date' => $flw->due_date->format('d M Y'),
                    'time' => $flw->due_date->format('h:i A'),
                    'timestamp' => $flw->due_date->timestamp,
                    'badge_color' => $flw->status === 'completed' ? 'bg-success' : 'bg-secondary',
                    'event_type' => 'followup',
                ];
            }
        } elseif ($model instanceof Quotation) {
            if ($model->approved_at) {
                $events[] = [
                    'icon' => '✅',
                    'status' => 'approved',
                    'title' => 'Quotation Approved',
                    'description' => 'Commercial Proposal approved by Manager.',
                    'user_name' => $model->approvedBy->name ?? 'Manager',
                    'date' => $model->approved_at->format('d M Y'),
                    'time' => $model->approved_at->format('h:i A'),
                    'timestamp' => $model->approved_at->timestamp,
                    'badge_color' => 'bg-success',
                    'event_type' => 'approval',
                ];
            }
        } elseif ($model instanceof SalesOrder) {
            if ($model->approved_at) {
                $events[] = [
                    'icon' => '✅',
                    'status' => 'approved',
                    'title' => 'Sales Order Approved',
                    'description' => 'Sales Order approved.',
                    'user_name' => $model->approvedBy->name ?? 'Manager',
                    'date' => $model->approved_at->format('d M Y'),
                    'time' => $model->approved_at->format('h:i A'),
                    'timestamp' => $model->approved_at->timestamp,
                    'badge_color' => 'bg-success',
                    'event_type' => 'approval',
                ];
            }
            if ($model->reserved_at) {
                $events[] = [
                    'icon' => '📦',
                    'status' => 'reserved',
                    'title' => 'Inventory Stock Reserved',
                    'description' => 'Centralized Inventory Reservation allocated stock in SSOT Product Master.',
                    'user_name' => 'Reservation Engine',
                    'date' => $model->reserved_at->format('d M Y'),
                    'time' => $model->reserved_at->format('h:i A'),
                    'timestamp' => $model->reserved_at->timestamp,
                    'badge_color' => 'bg-info',
                    'event_type' => 'reservation',
                ];
            }
        }

        // Sort chronologically descending (latest first)
        usort($events, function ($a, $b) {
            return $b['timestamp'] <=> $a['timestamp'];
        });

        return $events;
    }

    private function getActivityIcon(string $type): string
    {
        return match ($type) {
            'call' => '📞',
            'email' => '✉️',
            'whatsapp' => '💬',
            'meeting' => '🤝',
            'site_visit' => '🏢',
            'demo' => '🖥️',
            default => '📝',
        };
    }
}
