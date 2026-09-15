<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Parents;
use App\Models\StudentParentRelationship;
use App\Models\StudentFeeAssignment;
use App\Models\Notification;
use App\Models\AcademicEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MobileParentDashboardController extends Controller
{
    /**
     * GET /api/mobile/parent/dashboard
     *
     * Aggregated high-value data for the parent's home screen.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $parent = Parents::where('user_id', $user->id)->first();

        if (!$parent) {
            return response()->json(['error' => 'Parent record not found.'], 404);
        }

        // 1. Children Summary (Sense-making: "Who are my kids in this system?")
        $children = StudentParentRelationship::where('parent_id', $parent->parent_id)
            ->with('student.studentClassEnrollments.classSection.class')
            ->get()
            ->map(fn($rel) => [
                'student_id' => $rel->student_id,
                'name'       => trim(($rel->student?->first_name ?? '') . ' ' . ($rel->student?->last_name ?? '')),
                'class'      => $rel->student?->studentClassEnrollments->first()?->classSection?->class?->name ?? 'N/A',
            ]);

        // 2. Total Family Fee Balance (Sense-making: "How much do I owe in total?")
        $childIds = $children->pluck('student_id')->toArray();
        $totalBalance = StudentFeeAssignment::whereIn('student_id', $childIds)
            ->where('status', 'active')
            ->sum(DB::raw('final_amount - paid_amount'));

        // 3. Urgent Alerts (Sense-making: "Did something happen to my child today?")
        $alerts = Notification::whereHas('recipients', function ($q) use ($user) {
            $q->where('recipient_id', $user->id);
        })
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        // 4. Upcoming School Events
        $events = AcademicEvent::orderBy('start_date', 'asc')
            ->where('start_date', '>=', now()->toDateString())
            ->limit(3)
            ->get();

        return response()->json([
            'parent_name' => $user->name,
            'children'    => $children,
            'family_stats' => [
                'total_balance_due' => (float) $totalBalance,
                'balance_status'     => $totalBalance <= 0 ? 'Clear' : 'Payment Pending',
            ],
            'urgent_alerts' => $alerts->map(fn($n) => [
                'title' => $n->title,
                'body'  => $n->message,
                'date'  => $n->created_at->diffForHumans(),
            ]),
            'upcoming_events' => $events->map(fn($e) => [
                'event' => $e->title,
                'date'  => $e->start_date->toDateString(),
            ]),
        ]);
    }
}