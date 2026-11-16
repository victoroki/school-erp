<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Services\PortalScopeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * PHASE 4 — the per-child portal read.
 *
 * The authenticated parent's Child screen (and, for a Student persona, their
 * own view) is fed by ONE ownership-checked endpoint instead of trusting the
 * device's student_id: `visibleStudentIds()` membership decides, exactly like
 * the Phase 3 fee endpoints. Teachers/Admins pass by their existing scope so
 * staff debugging stays possible, but Parent A asking for Parent B's child
 * gets 403 — mobile navigation is never the boundary (STEP 3).
 */
class MobileChildController extends Controller
{
    public function __construct(private readonly PortalScopeService $scope)
    {
    }

    /**
     * GET /api/mobile/child/{studentId}
     *
     * Overview rollup (today, attendance term summary, fees, homework, exams)
     * for one visible student.
     */
    public function show(Request $request, $studentId): JsonResponse
    {
        $studentId = (int) $studentId;
        if (! in_array($studentId, $this->scope->visibleStudentIds($request->user()), true)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return response()->json($this->scope->childOverview($studentId));
    }

    /**
     * GET /api/mobile/child/{studentId}/attendance?limit=90
     *
     * Read-only attendance history for a visible student — date, status and
     * the staff remark only. No register controls, no staff-internal fields
     * (marked_by is deliberately absent), no fabricated rows: a date with no
     * record simply is not in the list.
     */
    public function attendance(Request $request, $studentId): JsonResponse
    {
        $studentId = (int) $studentId;
        if (! in_array($studentId, $this->scope->visibleStudentIds($request->user()), true)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $limit = min(365, max(1, (int) $request->query('limit', 90)));

        $records = \App\Models\StudentAttendance::where('student_id', $studentId)
            ->orderByDesc('date')
            ->limit($limit)
            ->get(['date', 'status', 'remarks']);

        return response()->json([
            'student_id' => $studentId,
            'records' => $records->map(fn ($r) => [
                'date' => $r->date->toDateString(),
                'status' => $r->status,
                'remark' => $r->remarks,
            ])->all(),
        ]);
    }
}
