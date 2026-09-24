<?php

namespace App\Services;

use App\Models\FeePayment;
use App\Models\Refund;
use App\Models\StudentFeeAssignment;
use Illuminate\Support\Facades\DB;

/**
 * The single source of truth for every fee figure in the application.
 *
 * Business rule:
 *     outstanding = fees assigned - effective paid
 *     effective paid = valid payments - completed refunds
 *
 * where "valid" means a payment that has not been reversed. Reversed payments
 * stay in the table for audit but are excluded everywhere.
 *
 * Refunds reduce effective paid, never increase it. A refund is cash leaving the
 * school to return money the student had paid, so the student owes that amount
 * again afterwards — a completed refund must never make a student look like they
 * owe less. Before this rule the balance ignored refunds entirely while the
 * ledger recorded one as a credit, so the two disagreed and the statement's
 * derived opening line silently absorbed the difference.
 *
 * Attribution rule (one payment, one assignment):
 *   - When a payment has allocation rows, the allocations are the breakdown and
 *     they are authoritative for that assignment.
 *   - Otherwise the payment's direct `student_fee_assignment_id` is used.
 *
 * Refund attribution follows the same shape:
 *   - `refunds.student_fee_assignment_id` when set, else
 *   - the assignment of the payment the refund references (`refunds.payment_id`).
 * A completed refund with neither cannot be placed against a charge; it is
 * applied at student level instead, so a student's own balance is never wrong,
 * and `refundsThatCannotBeAttributed()` reports it for administrative review.
 *
 * This makes both the current data set (direct FK only, no allocations yet) and
 * the allocation-based payment flow produce identical figures.
 *
 * Views, controllers and reports must call this service rather than writing
 * their own SUM(...) against fee_payments - that is what allowed the same
 * student's balance to disagree between screens.
 */
class FeeBalanceService
{
    /**
     * Paid amount per assignment, resolved in two queries regardless of size.
     *
     * @param  array<int, int>  $assignmentIds
     * @return array<int, float>  keyed by assignment id
     */
    public function paidForAssignments(array $assignmentIds): array
    {
        $assignmentIds = array_values(array_unique(array_map('intval', $assignmentIds)));

        if ($assignmentIds === []) {
            return [];
        }

        $fromAllocations = DB::table('payment_allocations as pa')
            ->join('fee_payments as p', 'p.payment_id', '=', 'pa.payment_id')
            ->whereIn('pa.student_fee_assignment_id', $assignmentIds)
            ->whereNull('p.reversed_at')
            ->groupBy('pa.student_fee_assignment_id')
            ->selectRaw('pa.student_fee_assignment_id as aid, COALESCE(SUM(pa.amount), 0) as total')
            ->pluck('total', 'aid');

        $directOnly = DB::table('fee_payments')
            ->whereIn('student_fee_assignment_id', $assignmentIds)
            ->whereNull('reversed_at')
            ->groupBy('student_fee_assignment_id')
            ->selectRaw('student_fee_assignment_id as aid, COALESCE(SUM(amount), 0) as total')
            ->pluck('total', 'aid');

        $result = [];

        foreach ($assignmentIds as $id) {
            $allocated = (float) ($fromAllocations[$id] ?? 0);
            $direct = (float) ($directOnly[$id] ?? 0);

            $result[$id] = round($allocated > 0 ? $allocated : $direct, 2);
        }

        return $result;
    }

    /**
     * Paid amount attributed to a single assignment.
     */
    public function paidForAssignment(int $assignmentId): float
    {
        return $this->paidForAssignments([$assignmentId])[$assignmentId] ?? 0.0;
    }

    /**
     * Completed refunds per assignment, in SQL — the single place the refund
     * attribution rule is written. paidTotalsSubquery() and refundsForAssignments()
     * both read this, so the SQL path used by reports and the PHP path used by
     * screens cannot drift.
     *
     * Attribution: the refund's own assignment when set, otherwise the assignment
     * of the payment the refund references. A refund resolving to neither groups
     * under NULL and therefore attaches to no assignment — see
     * unattributedRefundsForStudents(), which keeps it in the student's balance.
     */
    public function attributedRefundsSubquery(): string
    {
        return $this->attributedRefundsBody() . ' AS attributed_refunds';
    }

    /**
     * The same derived table, unaliased, for callers that join it themselves and
     * need to name it. Kept next to the aliased form so the two cannot diverge.
     */
    private function attributedRefundsBody(): string
    {
        return '(
            SELECT COALESCE(r.student_fee_assignment_id, p.student_fee_assignment_id) AS student_fee_assignment_id,
                   SUM(r.amount) AS refund_total
            FROM refunds r
            LEFT JOIN fee_payments p ON p.payment_id = r.payment_id
            WHERE r.status = \'completed\'
            GROUP BY COALESCE(r.student_fee_assignment_id, p.student_fee_assignment_id)
        )';
    }

    /**
     * Completed refunds attributed per assignment.
     *
     * @param  array<int, int>  $assignmentIds
     * @return array<int, float>  keyed by assignment id
     */
    public function refundsForAssignments(array $assignmentIds): array
    {
        $assignmentIds = array_values(array_unique(array_map('intval', $assignmentIds)));

        if ($assignmentIds === []) {
            return [];
        }

        $rows = DB::table(DB::raw($this->attributedRefundsSubquery()))
            ->whereIn('student_fee_assignment_id', $assignmentIds)
            ->pluck('refund_total', 'student_fee_assignment_id');

        $result = [];

        foreach ($rows as $assignmentId => $total) {
            $result[(int) $assignmentId] = round((float) $total, 2);
        }

        return $result;
    }

    /**
     * What each assignment has actually been settled by, once refunds are taken
     * into account: paid minus refunded.
     *
     * Deliberately NOT clamped at zero. Refunding more out of an assignment than
     * was ever paid into it is a real state in the live data (the legacy KES
     * 200,000 refund), and a clamp would hide it while making the per-assignment
     * figures stop adding up to the student's balance.
     *
     * @param  array<int, int>  $assignmentIds
     * @return array<int, float>  keyed by assignment id
     */
    public function effectivePaidForAssignments(array $assignmentIds): array
    {
        $assignmentIds = array_values(array_unique(array_map('intval', $assignmentIds)));

        if ($assignmentIds === []) {
            return [];
        }

        $paid = $this->paidForAssignments($assignmentIds);
        $refunded = $this->refundsForAssignments($assignmentIds);

        $result = [];

        foreach ($assignmentIds as $id) {
            $result[$id] = round(($paid[$id] ?? 0.0) - ($refunded[$id] ?? 0.0), 2);
        }

        return $result;
    }

    /**
     * Effective paid for one assignment.
     */
    public function effectivePaidForAssignment(int $assignmentId): float
    {
        return $this->effectivePaidForAssignments([$assignmentId])[$assignmentId] ?? 0.0;
    }

    /**
     * Completed refunds that cannot be placed against any charge, per student.
     *
     * These still reduce the student's effective paid — the money genuinely left
     * the school — but they have no assignment to sit against, so they cannot
     * appear in a per-charge figure. Surfacing them here (and in the fee
     * integrity report) keeps that limitation visible instead of silent.
     *
     * @param  array<int, int>  $studentIds
     * @return array<int, float>  keyed by student id
     */
    public function unattributedRefundsForStudents(array $studentIds): array
    {
        $studentIds = array_values(array_unique(array_map('intval', $studentIds)));

        if ($studentIds === []) {
            return [];
        }

        $rows = DB::table('refunds as r')
            ->leftJoin('fee_payments as p', 'p.payment_id', '=', 'r.payment_id')
            ->where('r.status', 'completed')
            ->whereNull('r.student_fee_assignment_id')
            ->where(function ($q) {
                $q->whereNull('r.payment_id')->orWhereNull('p.student_fee_assignment_id');
            })
            ->whereIn('r.student_id', $studentIds)
            ->groupBy('r.student_id')
            ->selectRaw('r.student_id as sid, SUM(r.amount) as total')
            ->pluck('total', 'sid');

        $result = [];

        foreach ($rows as $studentId => $total) {
            $result[(int) $studentId] = round((float) $total, 2);
        }

        return $result;
    }

    /**
     * The refund figures a refund form or a refund validation needs.
     *
     *     maximum refundable = valid payments - completed refunds - in-flight requests
     *
     * "In flight" means requested or approved. Reservations are held because
     * without them a queue of requests could each pass validation and the last
     * one to be completed would overdraw the student; rejecting a request
     * releases its reservation automatically. A refund being validated is
     * excluded from its own calculation via $excludeRefundId.
     *
     * The base is gross valid payments, not effective paid: effective paid has
     * already had the refunds taken out, so subtracting them again would
     * double-count.
     *
     * @return array{payments: float, refunded: float, pending: float, maxRefundable: float}
     */
    public function refundSummaryForStudent(int $studentId, ?int $excludeRefundId = null): array
    {
        $payments = round($this->summaryForStudent($studentId)['payments'], 2);

        $refundQuery = fn () => Refund::where('student_id', $studentId)
            ->when($excludeRefundId, fn ($q) => $q->where('id', '!=', $excludeRefundId));

        $completed = round((float) $refundQuery()->where('status', 'completed')->sum('amount'), 2);
        $pending = round((float) $refundQuery()->whereIn('status', ['requested', 'approved'])->sum('amount'), 2);

        return [
            'payments' => $payments,
            'refunded' => $completed,
            'pending' => $pending,
            'maxRefundable' => round(max(0, $payments - $completed - $pending), 2),
        ];
    }

    /**
     * The SAME paid-per-assignment definition as paidForAssignments(), as a raw
     * derived table, for reports that must aggregate in SQL (arrears paginates
     * and sorts on the computed balance, so hydrating every assignment first is
     * not an option).
     *
     * Produces one row per assignment:
     *
     *     student_fee_assignment_id | paid_total
     *
     * IMPORTANT: paid_total is EFFECTIVE paid — payments minus attributed
     * refunds — not money received. It is the figure that goes with
     * `final_amount` to produce a balance, so an assignment that was paid and
     * then refunded correctly shows less settled. Collections figures that mean
     * "money received" read fee_payments directly and are unaffected.
     *
     * Join it as:
     *
     *     ->leftJoin(DB::raw($balances->paidTotalsSubquery()), 'paid_totals.student_fee_assignment_id', '=', 'sfa.id')
     *
     * and read `paid_totals.paid_total`. Every assignment produces a row, so the
     * join never yields NULL and SUM() needs no COALESCE.
     *
     * Why this exists: arrears, the mobile dashboard and the raw subqueries in
     * the reports each used to write their own `SUM(fee_payments.amount)` grouped
     * by the payment's direct assignment link. That variant (a) counted reversed
     * payments as money received, and (b) ignored payment_allocations entirely, so
     * a "pay total balance" payment — which allocates one payment across several
     * charges while its direct link points at only the first — was credited
     * wholly to one assignment. Both defects made arrears disagree with the
     * student profile for the same student.
     *
     * IMPORTANT: this must stay behaviourally identical to
     * effectivePaidForAssignments(). `FeeReconciliationTest` asserts the two
     * agree for a fixture that mixes direct payments, reversed payments, split
     * allocations and a completed refund, so a change to one without the other
     * fails the suite.
     */
    public function paidTotalsSubquery(): string
    {
        return '(
            SELECT sfa_paid.id AS student_fee_assignment_id,
                   CASE
                      WHEN COALESCE(alloc.total, 0) > 0 THEN COALESCE(alloc.total, 0)
                      ELSE COALESCE(direct.total, 0)
                   END - COALESCE(ref.refund_total, 0) AS paid_total
            FROM student_fee_assignments sfa_paid
            LEFT JOIN ' . $this->attributedRefundsBody() . ' AS ref ON ref.student_fee_assignment_id = sfa_paid.id
            LEFT JOIN (
                SELECT pa.student_fee_assignment_id AS aid, SUM(pa.amount) AS total
                FROM payment_allocations pa
                JOIN fee_payments p ON p.payment_id = pa.payment_id
                WHERE p.reversed_at IS NULL
                GROUP BY pa.student_fee_assignment_id
            ) alloc ON alloc.aid = sfa_paid.id
            LEFT JOIN (
                SELECT p.student_fee_assignment_id AS aid, SUM(p.amount) AS total
                FROM fee_payments p
                WHERE p.reversed_at IS NULL AND p.student_fee_assignment_id IS NOT NULL
                GROUP BY p.student_fee_assignment_id
            ) direct ON direct.aid = sfa_paid.id
        ) AS paid_totals';
    }

    /**
     * Fee summary for one student.
     *
     * @return array{assigned: float, payments: float, refunded: float, paid: float, balance: float}
     */
    public function summaryForStudent(int $studentId): array
    {
        return $this->summariesForStudents([$studentId])[$studentId]
            ?? ['assigned' => 0.0, 'payments' => 0.0, 'refunded' => 0.0, 'paid' => 0.0, 'balance' => 0.0];
    }

    /**
     * Fee summaries for many students, without N+1 queries.
     *
     * The four money keys are not interchangeable:
     *
     *   payments  valid payments received (reversals excluded) — gross
     *   refunded  completed refunds, whether or not they could be attributed
     *   paid      effective paid = payments − refunded. This is the figure that
     *             pairs with `assigned` to make the balance, so it is the one
     *             every screen means by "Paid"
     *   balance   assigned − paid. Negative means the student is in credit
     *
     * A completed refund therefore raises `balance`, which is the whole point:
     * money handed back to the student is money the school no longer holds
     * against their fees.
     *
     * @param  array<int, int>  $studentIds
     * @return array<int, array{assigned: float, payments: float, refunded: float, paid: float, balance: float}>
     */
    public function summariesForStudents(array $studentIds): array
    {
        $studentIds = array_values(array_unique(array_map('intval', $studentIds)));

        if ($studentIds === []) {
            return [];
        }

        $assignments = StudentFeeAssignment::whereIn('student_id', $studentIds)
            ->get(['id', 'student_id', 'final_amount', 'status']);

        $assignmentIds = $assignments->pluck('id')->all();
        $payments = $this->paidForAssignments($assignmentIds);
        $refunds = $this->refundsForAssignments($assignmentIds);
        $unattributed = $this->unattributedRefundsForStudents($studentIds);

        $summaries = [];

        foreach ($studentIds as $id) {
            $summaries[$id] = ['assigned' => 0.0, 'payments' => 0.0, 'refunded' => 0.0, 'paid' => 0.0, 'balance' => 0.0];
        }

        foreach ($assignments as $assignment) {
            $studentId = (int) $assignment->student_id;

            $summaries[$studentId]['payments'] = round(
                $summaries[$studentId]['payments'] + ($payments[$assignment->id] ?? 0.0),
                2
            );

            $summaries[$studentId]['refunded'] = round(
                $summaries[$studentId]['refunded'] + ($refunds[$assignment->id] ?? 0.0),
                2
            );

            // Only active assignments count towards what is owed.
            if ($assignment->status === 'active') {
                $summaries[$studentId]['assigned'] = round(
                    $summaries[$studentId]['assigned'] + (float) $assignment->final_amount,
                    2
                );
            }
        }

        foreach ($summaries as $id => $summary) {
            // A refund with no charge to sit against still reduced what the
            // school holds, so it lands here rather than being dropped.
            $summaries[$id]['refunded'] = round($summary['refunded'] + ($unattributed[$id] ?? 0.0), 2);
            $summaries[$id]['paid'] = round($summary['payments'] - $summaries[$id]['refunded'], 2);
            $summaries[$id]['balance'] = round($summary['assigned'] - $summaries[$id]['paid'], 2);
        }

        return $summaries;
    }

    /**
     * What the student has effectively paid: valid payments less completed
     * refunds. This is the "Paid" that pairs with assignedForStudent() to give
     * balanceForStudent(), so the three can never disagree.
     */
    public function paidForStudent(int $studentId): float
    {
        return $this->summaryForStudent($studentId)['paid'];
    }

    /**
     * What the student has been charged for active assignments.
     */
    public function assignedForStudent(int $studentId): float
    {
        return $this->summaryForStudent($studentId)['assigned'];
    }

    /**
     * What the student still owes. Negative means credit on the account.
     */
    public function balanceForStudent(int $studentId): float
    {
        return $this->summaryForStudent($studentId)['balance'];
    }

    /**
     * Total value of non-reversed payments in the period.
     */
    public function totalCollected(?int $academicYearId = null, ?string $from = null, ?string $to = null): float
    {
        $query = FeePayment::query()->whereNull('reversed_at');

        if ($from) {
            $query->where('payment_date', '>=', $from);
        }

        if ($to) {
            $query->where('payment_date', '<=', $to);
        }

        if ($academicYearId) {
            $query->whereHas('studentFeeAssignment', function ($q) use ($academicYearId) {
                $q->where('academic_year_id', $academicYearId);
            });
        }

        return round((float) $query->sum('amount'), 2);
    }

    /**
     * Outstanding value across a set of assignments: charges − effective paid.
     *
     * The per-assignment total is deliberately NOT clamped at zero. Clamping hid
     * an overpayment, so a student in credit was reported as owing nothing and
     * the dashboard's Outstanding card disagreed with that student's own page —
     * the exact cross-screen drift FeeReconciliationTest exists to catch. The
     * unclamped form is also the rule the rest of the module states: outstanding
     * = charges − effective paid. A negative result is a credit, which is real
     * money the school is holding for the student.
     *
     * Uses effective paid, so a refunded assignment correctly shows the refunded
     * money as outstanding again.
     *
     * @param  array<int, int>  $assignmentIds
     */
    public function outstandingForAssignments(array $assignmentIds): float
    {
        $assignments = StudentFeeAssignment::whereIn('id', $assignmentIds)
            ->get(['id', 'final_amount']);

        if ($assignments->isEmpty()) {
            return 0.0;
        }

        $paid = $this->effectivePaidForAssignments($assignments->pluck('id')->all());

        $outstanding = 0.0;

        foreach ($assignments as $assignment) {
            $outstanding += (float) $assignment->final_amount - ($paid[$assignment->id] ?? 0.0);
        }

        return round($outstanding, 2);
    }

    /**
     * Total value of active fee assignments.
     */
    public function totalAssigned(?int $academicYearId = null): float
    {
        $query = StudentFeeAssignment::where('status', 'active');

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        return round((float) $query->sum('final_amount'), 2);
    }

    /**
     * Collection rate as a percentage, clamped to 0-100.
     *
     * The previous implementation divided an all-time collected total by the
     * currently-active receivable, which could report more than 100%.
     */
    public function collectionRate(?int $academicYearId = null): float
    {
        $receivable = $this->totalAssigned($academicYearId);

        if ($receivable <= 0) {
            return 0.0;
        }

        $collected = $this->totalCollected($academicYearId);

        return round(min(100, max(0, ($collected / $receivable) * 100)), 2);
    }

    /**
     * Recompute and persist one assignment's denormalised paid_amount.
     *
     * The stored figure is EFFECTIVE paid (payments less attributed refunds).
     * Every screen that asks "how much of this charge is settled?" reads
     * `paid_amount`, and `final_amount - paid_amount` is the balance those
     * screens show, so the column has to mean effective paid or a refund would
     * silently fail to raise the balance on the dashboard, the portal, the
     * reminder job and the report tables.
     *
     * @return float the newly stored paid amount
     */
    public function recomputeAssignment(int|StudentFeeAssignment $assignment): float
    {
        $assignmentId = $assignment instanceof StudentFeeAssignment
            ? (int) $assignment->getKey()
            : (int) $assignment;

        $paid = $this->effectivePaidForAssignment($assignmentId);

        StudentFeeAssignment::whereKey($assignmentId)->update(['paid_amount' => $paid]);

        return $paid;
    }

    /**
     * Recompute every assignment of a student.
     */
    public function recomputeForStudent(int $studentId): void
    {
        foreach (StudentFeeAssignment::where('student_id', $studentId)->pluck('id') as $id) {
            $this->recomputeAssignment((int) $id);
        }
    }
}
