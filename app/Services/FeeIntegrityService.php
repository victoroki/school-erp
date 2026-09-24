<?php

namespace App\Services;

use App\Models\LedgerEntry;
use App\Models\Refund;
use App\Support\Money;
use Illuminate\Support\Facades\DB;

/**
 * Conditions in the fee data that need a human decision.
 *
 * THIS SERVICE NEVER WRITES. It exists because some financial states cannot be
 * corrected by code: a refund that was paid out for more than the student ever
 * paid, a posted ledger movement whose direction the accounting model no longer
 * agrees with. Re-pointing a posted movement, or deciding that an overpayment
 * was really a grant, is an accounting decision — so the application reports the
 * position and leaves the history alone.
 *
 * Findings are facts about money, not warnings about code. Each one names the
 * records involved and states the arithmetic plainly, so whoever reviews it can
 * act without re-deriving anything.
 */
class FeeIntegrityService
{
    /**
     * Everything currently needing review, most serious first.
     *
     * @return array<int, array{
     *     code: string,
     *     severity: string,
     *     title: string,
     *     summary: string,
     *     detail: array<int, string>,
     *     records: array<string, array<int, int>>
     * }>
     */
    public function findings(): array
    {
        $findings = array_filter([
            $this->overRefundedStudents(),
            $this->refundsPostedInTheOldDirection(),
        ]);

        return array_values($findings);
    }

    public function hasFindings(): bool
    {
        return $this->findings() !== [];
    }

    /**
     * Students whose completed refunds are larger than the money the school ever
     * received from them.
     *
     * Under the rule now enforced at the request and payout stages this cannot
     * happen: a refund is capped at valid payments less completed refunds. Rows
     * already in the database predate that rule, so they are reported rather than
     * rewritten — and they DO count in the student's balance, because the money
     * really did leave the school.
     */
    private function overRefundedStudents(): ?array
    {
        $balances = app(FeeBalanceService::class);

        $rows = Refund::query()
            ->where('status', 'completed')
            ->groupBy('student_id')
            ->selectRaw('student_id, SUM(amount) as refunded_total')
            ->get();

        $affected = [];

        foreach ($rows as $row) {
            $summary = $balances->summaryForStudent((int) $row->student_id);
            $over = round((float) $row->refunded_total - $summary['payments'], 2);

            if ($over <= 0) {
                continue;
            }

            $student = DB::table('students')->where('student_id', $row->student_id)->first();

            $refunds = Refund::where('student_id', $row->student_id)
                ->where('status', 'completed')
                ->orderBy('id')
                ->get(['id', 'amount', 'completed_at', 'ledger_entry_id', 'bank_account_id', 'refund_method']);

            $affected[] = [
                'student_id' => (int) $row->student_id,
                'student' => trim(($student->first_name ?? 'Unknown') . ' ' . ($student->last_name ?? '')),
                'admission_no' => $student->admission_no ?? '',
                'payments' => round($summary['payments'], 2),
                'refunded' => round((float) $row->refunded_total, 2),
                'over' => $over,
                'effective_paid' => round($summary['paid'], 2),
                'balance' => round($summary['balance'], 2),
                'refunds' => $refunds,
            ];
        }

        if ($affected === []) {
            return null;
        }

        usort($affected, fn ($a, $b) => $b['over'] <=> $a['over']);

        $total = round(array_sum(array_column($affected, 'over')), 2);

        // One entry per affected student, so the report is readable line by line.
        $detail = [];

        foreach ($affected as $row) {
            $detail[] = $row['student'] . ' (' . $row['admission_no'] . '): paid '
                . Money::format($row['payments']) . ', refunded ' . Money::format($row['refunded'])
                . ' — over-refunded by ' . Money::format($row['over'])
                . '. Under the refund rule now enforced this student\'s effective paid would be '
                . Money::format($row['payments']) . ' and their balance '
                . Money::format((float) $row['balance'] - (float) $row['over'])
                . '; the recorded position is effective paid ' . Money::format($row['effective_paid'])
                . ' and balance ' . Money::format($row['balance'])
                . '. Reconcile by recording a written-off grant, or by treating the payout as an '
                . 'expense outside student fees — then, and only then, adjust the ledger.';
        }

        return [
            'code' => 'refund_exceeds_payments',
            'severity' => 'danger',
            'title' => 'Refunds paid out for more than the student ever paid',
            'summary' => count($affected) . ' student(s) affected, ' . Money::format($total)
                . ' in refunds with no payments behind them. Legacy data — nothing has been changed.',
            'detail' => $detail,
            'records' => [
                'student_ids' => array_column($affected, 'student_id'),
                'refund_ids' => $affected === [] ? [] : Refund::whereIn('student_id', array_column($affected, 'student_id'))
                    ->where('status', 'completed')
                    ->pluck('id')
                    ->all(),
                'ledger_entry_ids' => $affected === [] ? [] : Refund::whereIn('student_id', array_column($affected, 'student_id'))
                    ->where('status', 'completed')
                    ->whereNotNull('ledger_entry_id')
                    ->pluck('ledger_entry_id')
                    ->all(),
            ],
            'students' => $affected,
        ];
    }

    /**
     * Refund ledger entries posted as a CREDIT.
     *
     * The ledger used to record a refund as a credit, which says the refund
     * reduced what the student owed. A cash refund does the opposite: the money
     * left the school, so the student owes that amount again and the entry should
     * be a debit. Entries written before that change are detected, not rewritten.
     *
     * The student's balance is unaffected either way — it comes from the balance
     * service, not from the ledger — but the statement's itemisation of these
     * rows reads backwards until someone decides how to correct them.
     */
    private function refundsPostedInTheOldDirection(): ?array
    {
        $entries = LedgerEntry::where('entry_type', 'refund')
            ->where('credit', '>', 0)
            ->orderBy('id')
            ->get(['id', 'student_id', 'credit', 'entry_date', 'description']);

        if ($entries->isEmpty()) {
            return null;
        }

        $detail = [];

        foreach ($entries as $entry) {
            $student = DB::table('students')->where('student_id', $entry->student_id)->first();

            $detail[] = 'Ledger entry #' . $entry->id . ' (' . ($entry->entry_date?->format('d M Y') ?? 'no date') . ') '
                . 'credits ' . Money::format($entry->credit) . ' for '
                . trim(($student->first_name ?? 'Unknown') . ' ' . ($student->last_name ?? ''))
                . '. A refund should be a debit. Decide whether to post a correcting contra entry; '
                . 'the balance already reflects the refund correctly.';
        }

        return [
            'code' => 'refund_ledger_direction',
            'severity' => 'warning',
            'title' => 'Refund ledger entries posted in the old direction',
            'summary' => $entries->count() . ' entry(s) credit the student for a refund, which reduces what they owe '
                . 'instead of increasing it. Legacy data — nothing has been changed.',
            'detail' => $detail,
            'records' => [
                'ledger_entry_ids' => $entries->pluck('id')->all(),
                'student_ids' => $entries->pluck('student_id')->unique()->values()->all(),
            ],
        ];
    }
}
