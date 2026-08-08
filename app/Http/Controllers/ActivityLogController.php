<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    /**
     * Display activity log timeline.
     */
    public function index(Request $request): View
    {
        $query = ActivityLog::with('user')->orderByDesc('id');

        if ($request->filled('event')) {
            $query->where('event', $request->input('event'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('module', 'like', "%{$search}%");
            });
        }

        $activityLogs = $query->paginate(20)->withQueryString();

        return view('activity-logs.index', compact('activityLogs'));
    }
}
