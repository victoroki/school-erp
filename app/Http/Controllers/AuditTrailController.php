<?php

namespace App\Http\Controllers;

use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class AuditTrailController extends AppBaseController
{
    public function __construct()
    {
        // The audit trail exposes every action taken in the system, so it is
        // reserved for the platform Owner by role rather than riding on a
        // module permission that could be renamed or revoked. School
        // administrators (Super Admin) must never see it.
        $this->middleware(function ($request, $next) {
            $user = auth()->user();

            if ($user && $user->isOwner()) {
                return $next($request);
            }

            abort(403);
        });
    }

    public function index(Request $request)
    {
        $query = AuditTrail::with('user');

        if ($module = $request->input('module')) {
            $query->where('module', $module);
        }

        if ($action = $request->input('action')) {
            $query->where('action', 'LIKE', "%{$action}%");
        }

        if ($user = $request->input('user')) {
            $query->whereHas('user', fn($q) => $q->where('name', 'LIKE', "%{$user}%"));
        }

        if ($recordId = $request->input('record_id')) {
            $query->where('record_id', $recordId);
        }

        if ($from = $request->input('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->input('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $logs = $query->latest()->paginate(50)->withQueryString();

        $modules = AuditTrail::query()->distinct()->orderBy('module')->pluck('module');
        $actions = AuditTrail::query()->distinct()->orderBy('action')->pluck('action');

        $filters = $request->only(['module', 'action', 'user', 'record_id', 'from', 'to']);

        // ── System-wide overview (ignores filters — these are summary metrics) ──
        $totalEvents     = AuditTrail::count();
        $eventsToday     = AuditTrail::whereDate('created_at', today())->count();
        $uniqueExecutors = AuditTrail::whereNotNull('user_id')->distinct()->count('user_id');

        // Most active modules (top 6) with counts for the progress bars.
        $moduleStats = AuditTrail::query()
            ->select('module', DB::raw('COUNT(*) as total'))
            ->groupBy('module')
            ->orderByDesc('total')
            ->orderBy('module')
            ->limit(6)
            ->get()
            ->map(fn($m) => ['module' => $m->module, 'total' => (int) $m->total])
            ->values();

        $maxModule = $moduleStats->max('total') ?: 1;
        $topModule = $moduleStats->first()['module'] ?? null;

        // Action mix (how events are distributed across action types).
        $actionStats = AuditTrail::query()
            ->select('action', DB::raw('COUNT(*) as total'))
            ->groupBy('action')
            ->orderByDesc('total')
            ->orderBy('action')
            ->get()
            ->map(fn($a) => ['action' => $a->action, 'total' => (int) $a->total])
            ->values();

        // Top executors (5 users with the most recorded actions).
        $topExecutors = AuditTrail::query()
            ->select('user_id', DB::raw('COUNT(*) as total'))
            ->whereNotNull('user_id')
            ->with('user:id,name')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn($e) => [
                'name'  => $e->user->name ?? 'System',
                'total' => (int) $e->total,
            ])
            ->values();

        return view('audit_trail.index', compact(
            'logs', 'modules', 'actions', 'filters',
            'totalEvents', 'eventsToday', 'uniqueExecutors',
            'moduleStats', 'maxModule', 'topModule',
            'actionStats', 'topExecutors'
        ));
    }

    public function export(Request $request)
    {
        $query = AuditTrail::with('user');

        if ($module = $request->input('module')) {
            $query->where('module', $module);
        }

        if ($action = $request->input('action')) {
            $query->where('action', 'LIKE', "%{$action}%");
        }

        if ($user = $request->input('user')) {
            $query->whereHas('user', fn($q) => $q->where('name', 'LIKE', "%{$user}%"));
        }

        if ($recordId = $request->input('record_id')) {
            $query->where('record_id', $recordId);
        }

        if ($from = $request->input('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->input('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $logs = $query->latest()->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="audit_trail_export.csv"',
        ];

        $callback = function () use ($logs) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'ID', 'Timestamp', 'User', 'Module', 'Action',
                'Record ID', 'IP Address', 'Old Values', 'New Values',
            ]);

            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->id,
                    $log->created_at->toIso8601String(),
                    $log->user->name ?? 'System',
                    $log->module,
                    $log->action,
                    $log->record_id,
                    $log->ip_address,
                    $log->old_values ? json_encode($log->old_values) : '',
                    $log->new_values ? json_encode($log->new_values) : '',
                ]);
            }

            fclose($handle);
        };

        return Response::stream($callback, 200, $headers);
    }
}
