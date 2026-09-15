<?php

namespace App\Services;

use App\Models\AcademicEvent;
use App\Models\AcademicYear;
use App\Models\ClassSection;
use App\Models\FeePayment;
use App\Models\LeaveApplication;
use App\Models\Staff;
use App\Models\StaffAttendance;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentClassEnrollment;
use App\Models\StudentFeeAssignment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * PHASE 3 — authoritative "school today" queries for the Admin mobile home.
 *
 * The whole point of this service is that the app never computes school-wide
 * statistics locally: every number below is a single server-side query over
 * the same tables the web dashboard uses.
 *
 * Attendance model (verified against schema): student_attendance is one
 * DAILY register row per (student, date) — NOT per lesson. "Pending
 * registers" therefore means: class sections (school-wide, not just one
 * teacher's) whose active-enrolled roster does not yet have an attendance
 * row for the date. This mirrors TeacherMobileHomeService::pendingRegisters
 * with the teacher scope removed.
 *
 * Money numbers deliberately use `payment_date` (the business date), not
 * created_at — matching FeeReportsController::collections().
 */
class AdminMobileHomeService
{
    /**
     * Student attendance rollup for the date.
     *
     * Roster = DISTINCT students with an active enrollment in a section of
     * the current academic year. Counted = roster students with ANY
     * attendance row for the date (status breakdown only among those).
     *
     * @return array{date:string, enrolled:int, marked:int, unmarked:int, present:int, absent:int, late:int, half_day:int, excused:int, attendance_rate:?float}
     */
    public function attendanceToday(string $date): array
    {
        $roster = $this->currentRosterStudentIds();
        $enrolled = $roster->count();

        $rows = StudentAttendance::where('date', $date)
            ->whereIn('student_id', $roster)
            ->select('student_id', 'status')
            ->get();

        $marked = $rows->pluck('student_id')->unique()->count();
        $byStatus = $rows->groupBy('status')->map->count();

        $present = ($byStatus['present'] ?? 0) + ($byStatus['late'] ?? 0) + ($byStatus['half_day'] ?? 0);

        return [
            'date'            => $date,
            'enrolled'        => $enrolled,
            'marked'          => $marked,
            'unmarked'        => max(0, $enrolled - $marked),
            'present'         => (int) $present,
            'absent'          => (int) ($byStatus['absent'] ?? 0),
            'late'            => (int) ($byStatus['late'] ?? 0),
            'half_day'        => (int) ($byStatus['half_day'] ?? 0),
            'excused'         => (int) ($byStatus['excused'] ?? 0),
            // rate of students marked, not of enrollment — "of those counted,
            // how many were in". Null until anyone is counted at all.
            'attendance_rate' => $marked > 0 ? round(($present / $marked) * 100, 1) : null,
        ];
    }

    /**
     * School-wide registers still outstanding for the date. Sections with an
     * empty roster are skipped — an empty register is not pending work.
     *
     * @return list<array{class_section_id:int,label:string,marked:int,enrolled:int,pending:int}>
     */
    public function pendingRegisters(string $date, int $limit = 50): array
    {
        $year = AcademicYear::where('is_current', true)->first();
        if (!$year) {
            return [];
        }

        $sections = ClassSection::with(['schoolClass', 'section'])
            ->where('academic_year_id', $year->academic_year_id)
            ->get();
        if ($sections->isEmpty()) {
            return [];
        }

        $sectionIds = $sections->pluck('class_section_id');

        $enrolledCounts = StudentClassEnrollment::whereIn('class_section_id', $sectionIds)
            ->where('status', 'active')
            ->selectRaw('class_section_id, COUNT(DISTINCT student_id) as c')
            ->groupBy('class_section_id')
            ->pluck('c', 'class_section_id');

        $markedIds = StudentAttendance::where('date', $date)
            ->pluck('student_id')
            ->unique()
            ->flip();

        $pending = [];
        foreach ($sections as $section) {
            $enrolled = (int) ($enrolledCounts[$section->class_section_id] ?? 0);
            if ($enrolled === 0) {
                continue;
            }

            $roster = StudentClassEnrollment::where('class_section_id', $section->class_section_id)
                ->where('status', 'active')
                ->pluck('student_id');
            $marked = $roster->intersect($markedIds->keys())->count();

            if ($marked >= $enrolled) {
                continue;
            }

            $pending[] = [
                'class_section_id' => (int) $section->class_section_id,
                'label'            => trim(($section->schoolClass->name ?? '') . ' ' . ($section->section->name ?? '')),
                'marked'           => $marked,
                'enrolled'         => $enrolled,
                'pending'          => $enrolled - $marked,
            ];
        }

        usort($pending, fn ($a, $b) => strcmp($a['label'], $b['label']));

        return array_slice($pending, 0, $limit);
    }

    /**
     * Staff attendance snapshot for the date, using the same source as the
     * web HR dashboard (StaffAttendance rows + approved leave spanning today).
     *
     * Only callers with hr.view should call this — the controller gates it
     * and the mobile screen shows "unavailable" when null.
     *
     * @return array{date:string, active_staff:int, present:int, late:int, absent:int, on_leave:int, not_recorded:int}
     */
    public function staffToday(string $date): array
    {
        $activeStaff = Staff::where('employment_status', 'active')->count();

        $rows = StaffAttendance::whereDate('date', $date)
            ->select('staff_id', 'status')
            ->get();
        $byStatus = $rows->groupBy('status')->map->count();

        // Canonical "on leave today" (HRDashboardController pattern):
        // approved leave whose span covers the date.
        $onLeaveToday = LeaveApplication::where('final_status', 'approved')
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->count();

        $recorded = $rows->pluck('staff_id')->unique()->count();

        return [
            'date'          => $date,
            'active_staff'  => (int) $activeStaff,
            'present'       => (int) (($byStatus['present'] ?? 0) + ($byStatus['late'] ?? 0) + ($byStatus['half_day'] ?? 0)),
            'late'          => (int) ($byStatus['late'] ?? 0),
            'absent'        => (int) ($byStatus['absent'] ?? 0),
            'on_leave'      => $onLeaveToday,
            'not_recorded'  => max(0, $activeStaff - $recorded),
        ];
    }

    /**
     * Money snapshot for the date. Uses payment_date (business date), the
     * same column FeeReportsController::collections() filters on.
     *
     * Outstanding total mirrors FinanceService::getMetrics()['total_pending']
     * (active assignments, final_amount − paid_amount where still owed).
     *
     * @return array{date:string, collected_today:float, payments_today:int, by_method:list<array{method:string,count:int,total:float}>, total_outstanding:float, total_expected:float, total_collected:float, students_in_arrears:int}
     */
    public function financeToday(string $date): array
    {
        $collectedToday = (float) FeePayment::whereDate('payment_date', $date)->sum('amount');
        $paymentsToday  = FeePayment::whereDate('payment_date', $date)->count();

        $byMethod = FeePayment::whereDate('payment_date', $date)
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => [
                'method' => $r->payment_method !== '' && $r->payment_method !== null ? $r->payment_method : 'unspecified',
                'count'  => (int) $r->count,
                'total'  => (float) $r->total,
            ])
            ->values()
            ->all();

        $totalExpected = (float) StudentFeeAssignment::where('status', 'active')->sum('final_amount');
        $totalCollected = (float) StudentFeeAssignment::where('status', 'active')->sum(DB::raw('COALESCE(paid_amount, 0)'));

        // Students in arrears — canonical FeeArrearsController query shape,
        // scoped to the whole school, counted (not paginated) for the badge.
        $paidTotalsSub = DB::raw('(SELECT fp.student_fee_assignment_id,
                COALESCE(SUM(fp.amount),0) AS paid_total
            FROM fee_payments fp
            GROUP BY fp.student_fee_assignment_id) AS paid_totals');

        $studentsInArrears = Student::query()
            ->join('student_fee_assignments as sfa', 'sfa.student_id', '=', 'students.student_id')
            ->leftJoin($paidTotalsSub, 'paid_totals.student_fee_assignment_id', '=', 'sfa.id')
            ->where('sfa.status', 'active')
            ->selectRaw('students.student_id,
                SUM(sfa.final_amount) as expected_total,
                COALESCE(SUM(paid_totals.paid_total),0) as paid_total')
            ->groupBy('students.student_id')
            ->havingRaw('expected_total > paid_total')
            ->get()
            ->count();

        return [
            'date'               => $date,
            'collected_today'    => round($collectedToday, 2),
            'payments_today'     => (int) $paymentsToday,
            'by_method'          => $byMethod,
            'total_outstanding'  => round(max(0, $totalExpected - $totalCollected), 2),
            'total_expected'     => round($totalExpected, 2),
            'total_collected'    => round($totalCollected, 2),
            'students_in_arrears' => $studentsInArrears,
        ];
    }

    /**
     * Real operational tasks only — every entry counts a row set that an
     * admin can actually act on. No invented approval queues.
     *
     * @return list<array{key:string,label:string,count:int}>
     */
    public function taskCounts(string $date, bool $canHr): array
    {
        $tasks = [];

        // Attendance registers still not completed school-wide.
        $tasks[] = [
            'key'   => 'pending_registers',
            'label' => 'Attendance registers pending today',
            'count' => count($this->pendingRegisters($date, 1000)),
        ];

        // Leave requests waiting for an approver (real approval queue that
        // exists in the web HR module) — only visible with hr.view.
        if ($canHr) {
            $tasks[] = [
                'key'   => 'pending_leave',
                'label' => 'Leave requests awaiting approval',
                'count' => LeaveApplication::where('application_status', 'pending')->count(),
            ];
        }

        // Students owing money — the accountant-facing queue admins can see.
        $arrears = $this->financeToday($date)['students_in_arrears'];
        $tasks[] = [
            'key'   => 'fee_arrears',
            'label' => 'Students with outstanding balances',
            'count' => $arrears,
        ];

        return $tasks;
    }

    /**
     * Upcoming calendar events (next `$days` days) for the briefing.
     *
     * @return list<array{event_id:int,title:string,date:string,type:string}>
     */
    public function upcomingEvents(int $days = 14): array
    {
        $year = AcademicYear::where('is_current', true)->first();
        if (!$year) {
            return [];
        }

        return AcademicEvent::where('academic_year_id', $year->academic_year_id)
            ->whereDate('start_date', '>=', now()->toDateString())
            ->whereDate('start_date', '<=', now()->addDays($days)->toDateString())
            ->orderBy('start_date')
            ->limit(5)
            ->get()
            ->map(fn ($e) => [
                'event_id' => (int) $e->id,
                'title'    => $e->title,
                'date'     => $e->start_date->toDateString(),
                'type'     => (string) ($e->event_type ?? 'event'),
            ])
            ->all();
    }

    /**
     * Most recent payments (any date) for the "money movement" feed.
     *
     * @return list<array{payment_id:int,amount:float,date:?string,method:string,receipt_number:?string,student_name:?string,admission_no:?string}>
     */
    public function recentPayments(int $limit = 8): array
    {
        return FeePayment::with(['studentFeeAssignment.student'])
            ->orderByDesc('payment_date')
            ->orderByDesc('payment_id')
            ->limit($limit)
            ->get()
            ->map(fn ($p) => [
                'payment_id'     => (int) $p->payment_id,
                'amount'         => (float) $p->amount,
                'date'           => $p->payment_date?->toDateString(),
                'method'         => $p->payment_method !== '' && $p->payment_method !== null ? $p->payment_method : 'unspecified',
                'receipt_number' => $p->receipt_number,
                'student_name'   => $p->studentFeeAssignment?->student
                    ? trim($p->studentFeeAssignment->student->first_name . ' ' . $p->studentFeeAssignment->student->last_name)
                    : null,
                'admission_no'   => $p->studentFeeAssignment?->student?->admission_no,
            ])
            ->all();
    }

    /**
     * DISTINCT student ids with a current active enrollment.
     */
    private function currentRosterStudentIds()
    {
        $year = AcademicYear::where('is_current', true)->first();
        if (!$year) {
            return collect();
        }

        return StudentClassEnrollment::whereHas('classSection', fn ($q) => $q->where('academic_year_id', $year->academic_year_id))
            ->where('status', 'active')
            ->pluck('student_id')
            ->unique();
    }
}
