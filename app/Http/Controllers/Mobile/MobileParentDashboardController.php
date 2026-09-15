<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\AcademicEvent;
use App\Models\Notification;
use App\Models\Parents;
use App\Models\StudentFeeAssignment;
use App\Services\PortalScopeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MobileParentDashboardController extends Controller
{
    /**
     * GET /api/mobile/parent/dashboard
     *
     * Aggregated high-value data for the parent's home screen. PHASE 4: each
     * child carries a server-computed overview (today's attendance, next
     * lesson, current-term fee position, homework due, upcoming exams) so the
     * app never re-derives school logic on-device. Ownership is resolved here
     * from the authenticated parent's StudentParentRelationship rows — the
     * device never chooses which children appear.
     */
    public function __invoke(Request $request, PortalScopeService $scope): JsonResponse
    {
        $user = $request->user();
        $parent = Parents::where('user_id', $user->id)->first();

        if (!$parent) {
            return response()->json(['error' => 'Parent record not found.'], 404);
        }

        // 1. Children (id/name/admission/class) — the switcher's source.
        $children = $scope->childrenFor($user);

        // 2. Per-child overview rollups (server-computed).
        $overviews = $children
            ->map(fn ($child) => $scope->childOverview((int) $child['student_id']))
            ->values();

        // 3. Total family fee balance (Sense-making: "How much do I owe in total?")
        $childIds = $children->pluck('student_id')->map(fn ($id) => (int) $id)->all();
        $totalBalance = $childIds
            ? round((float) StudentFeeAssignment::whereIn('student_id', $childIds)
                ->where('status', 'active')
                ->sum(DB::raw('final_amount - paid_amount')), 2)
            : 0.0;

        // 4. Urgent Alerts (Sense-making: "Did something happen to my child today?")
        $alerts = Notification::whereHas('recipients', function ($q) use ($user) {
            $q->where('recipient_id', $user->id);
        })
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        // 5. Upcoming School Events
        $events = AcademicEvent::orderBy('start_date', 'asc')
            ->where('start_date', '>=', now()->toDateString())
            ->limit(3)
            ->get();

        return response()->json([
            'parent_name' => $user->name,
            'children' => $children,
            'child_overviews' => $overviews,
            'family_stats' => [
                'total_balance_due' => $totalBalance,
                'balance_status' => $totalBalance <= 0 ? 'Clear' : 'Payment Pending',
            ],
            'urgent_alerts' => $alerts->map(fn ($n) => [
                'title' => $n->title,
                'body' => $n->message,
                'date' => $n->created_at->diffForHumans(),
            ]),
            'upcoming_events' => $events->map(fn ($e) => [
                'event' => $e->title,
                'date' => $e->start_date->toDateString(),
            ]),
        ]);
    }
}
