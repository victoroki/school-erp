<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\AuditTrail;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\Staff;
use App\Models\StaffLeaveBalance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * PHASE 5 — staff leave self-service on the REAL leave model (STEP 17–18).
 *
 * The mobile HR screen previously "applied" with a fake success alert. These
 * endpoints reuse the same model, business validation and audit trail as the
 * web LeaveApplicationController — permissions, working-day calculation,
 * balance and advance-notice rules included — so there is exactly one set of
 * rules. Every route is SELF-SCOPED via Staff.user_id: an applicant only ever
 * sees and creates their own applications; approvals stay web-only (HR/HOD).
 */
class MobileLeaveController extends Controller
{
    /** GET /api/mobile/hr/leave — my applications, newest first. */
    public function index(Request $request): JsonResponse
    {
        $staff = $this->staffFor($request->user());
        if ($staff instanceof JsonResponse) {
            return $staff;
        }

        $applications = LeaveApplication::with('leaveType')
            ->where('staff_id', $staff->staff_id)
            ->latest()
            ->limit(50)
            ->get();

        return response()->json([
            'data' => $applications->map(fn($l) => [
                'id'           => $l->id,
                'leave_type'   => $l->leaveType?->name ?? 'Leave',
                'start_date'   => optional($l->start_date)->format('Y-m-d') ?? (string) $l->start_date,
                'end_date'     => optional($l->end_date)->format('Y-m-d') ?? (string) $l->end_date,
                'working_days' => $l->working_days,
                'reason'       => $l->reason,
                'status'       => $l->application_status,
                'applied_on'   => optional($l->submitted_date)->toIso8601String() ?? optional($l->created_at)->toIso8601String(),
            ]),
        ]);
    }

    /** GET /api/mobile/hr/leave/types — applyable types + my current balances. */
    public function types(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$this->canApply($user)) {
            return response()->json(['message' => 'You are not authorised to apply for leave.'], 403);
        }

        $staff = $this->staffFor($user);
        if ($staff instanceof JsonResponse) {
            return $staff;
        }

        $currentYear = AcademicYear::where('is_current', true)->first();

        $balances = StaffLeaveBalance::where('staff_id', $staff->staff_id)
            ->where('academic_year_id', $currentYear->academic_year_id ?? null)
            ->get()
            ->keyBy('leave_type_id');

        $types = LeaveType::where('status', 'active')->get()->map(function ($t) use ($balances) {
            $balance = $balances->get($t->leave_type_id);

            return [
                'id'                  => $t->leave_type_id,
                'name'                => $t->name,
                'days_allowed'        => $t->days_allowed ?? null,
                'is_paid'             => (bool) ($t->is_paid ?? false),
                'notice_days_required' => (int) ($t->notice_days_required ?? 0),
                'balance'             => $balance ? [
                    'total_available' => (int) $balance->total_available,
                    'used'            => (int) $balance->used,
                    'remaining'       => (int) $balance->remaining,
                ] : null,
            ];
        });

        return response()->json(['data' => $types]);
    }

    /** POST /api/mobile/hr/leave — apply for my own leave. */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        // Same boundary as web create/store: hr.manage (HR) or hr.leave.apply
        // (staff self-service). Students/Parents hold neither → 403.
        if (!$this->canApply($user)) {
            return response()->json(['message' => 'You are not authorised to apply for leave.'], 403);
        }

        $staff = $this->staffFor($user);
        if ($staff instanceof JsonResponse) {
            return $staff;
        }

        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,leave_type_id',
            'start_date'    => 'required|date|after_or_equal:today',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'reason'        => 'required|string|min:10',
        ]);

        $leaveType = LeaveType::find($request->leave_type_id);
        $currentYear = AcademicYear::where('is_current', true)->first();

        $workingDays = $this->calculateWorkingDays($request->start_date, $request->end_date);

        // Business rules identical to LeaveApplicationController::store(),
        // surfaced as 422 JSON instead of web flash messages.
        $balance = StaffLeaveBalance::where('staff_id', $staff->staff_id)
            ->where('leave_type_id', $request->leave_type_id)
            ->where('academic_year_id', $currentYear->academic_year_id ?? null)
            ->first();

        if ($balance && $balance->remaining < $workingDays) {
            return response()->json([
                'message' => "Insufficient leave balance. You have only {$balance->remaining} days remaining.",
            ], 422);
        }

        $daysUntilLeave = now()->diffInDays($request->start_date, false);
        if ($leaveType->notice_days_required && $daysUntilLeave < $leaveType->notice_days_required) {
            return response()->json([
                'message' => "This leave type requires {$leaveType->notice_days_required} days advance notice.",
            ], 422);
        }

        $leave = LeaveApplication::create([
            'staff_id'           => $staff->staff_id,
            'leave_type_id'      => $request->leave_type_id,
            'start_date'         => $request->start_date,
            'end_date'           => $request->end_date,
            'working_days'       => $workingDays,
            'reason'             => $request->reason,
            'application_status' => 'pending',
            'submitted_date'     => now(),
        ]);

        AuditTrail::log('Leave', 'CREATE', $leave->id, null, $leave->toArray());

        return response()->json([
            'message' => 'Leave application submitted successfully.',
            'data'    => [
                'id'           => $leave->id,
                'leave_type'   => $leaveType->name,
                'start_date'   => (string) $leave->start_date,
                'end_date'     => (string) $leave->end_date,
                'working_days' => $leave->working_days,
                'reason'       => $leave->reason,
                'status'       => $leave->application_status,
                'applied_on'   => optional($leave->submitted_date)->toIso8601String(),
            ],
        ], 201);
    }

    /** Applicants: HR managers (hr.manage) or scoped staff (hr.leave.apply). */
    private function canApply($user): bool
    {
        return $user->hasPermission('hr.manage') || $user->hasPermission('hr.leave.apply');
    }

    /** Own staff record, or a 404 response when the account has none. */
    private function staffFor($user): Staff|JsonResponse
    {
        $staff = Staff::where('user_id', $user->id)->first();
        if (!$staff) {
            return response()->json(['message' => 'Staff record not found for this account.'], 404);
        }

        return $staff;
    }

    /** Same weekday-only count as the web controller. */
    private function calculateWorkingDays($startDate, $endDate): int
    {
        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);
        $workingDays = 0;

        while ($start->lte($end)) {
            if ($start->isWeekday()) {
                $workingDays++;
            }
            $start->addDay();
        }

        return $workingDays;
    }
}
