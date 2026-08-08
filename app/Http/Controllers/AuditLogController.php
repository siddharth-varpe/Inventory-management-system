<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    /**
     * Display audit log records.
     */
    public function index(Request $request): View
    {
        $query = AuditLog::with('user')->orderByDesc('id');

        if ($request->filled('module')) {
            $query->where('module', $request->input('module'));
        }

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('table_name', 'like', "%{$search}%");
        }

        $auditLogs = $query->paginate(20)->withQueryString();

        return view('audit-logs.index', compact('auditLogs'));
    }
}
