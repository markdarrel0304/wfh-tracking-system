<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->role === 'admin', 403);

        $filters = $request->validate([
            'event' => ['nullable', 'string', 'in:'.implode(',', array_keys(AuditLog::EVENT_LABELS))],
            'search' => ['nullable', 'string', 'max:100'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $logs = AuditLog::query()
            ->with('actor')
            ->when($filters['event'] ?? null, fn ($query, string $event) => $query->where('event', $event))
            ->when($filters['from'] ?? null, fn ($query, string $from) => $query->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($query, string $to) => $query->whereDate('created_at', '<=', $to))
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($searchQuery) use ($search): void {
                    $searchQuery->where('summary', 'like', "%{$search}%")
                        ->orWhereHas('actor', fn ($actorQuery) => $actorQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $weekStart = now()->startOfWeek();

        return view('audit-logs.index', [
            'logs' => $logs,
            'eventLabels' => AuditLog::EVENT_LABELS,
            'totalLogs' => AuditLog::count(),
            'weeklyLogs' => AuditLog::query()->where('created_at', '>=', $weekStart)->count(),
            'reviewLogs' => AuditLog::query()->whereIn('event', [
                'wfh_request.approved',
                'wfh_request.rejected',
                'attendance_correction.approved',
                'attendance_correction.rejected',
                'accomplishment_report.reviewed',
                'accomplishment_report.revision_requested',
            ])->count(),
        ]);
    }
}
