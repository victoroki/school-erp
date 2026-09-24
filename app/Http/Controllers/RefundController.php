<?php

namespace App\Http\Controllers;

use App\Models\Refund;
use App\Models\Student;
use App\Models\StudentFeeAssignment;
use App\Models\FeePayment;
use App\Models\BankAccount;
use App\Models\AuditTrail;
use App\Services\FeeBalanceService;
use App\Services\LedgerService;
use App\Services\BankLedger;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Flash;
use Barryvdh\DomPDF\Facade\Pdf;

class RefundController extends Controller
{
    /**
     * The single wording used wherever the refund cap refuses a request, so a
     * user reads the same sentence whether the backend rejected the request, or
     * blocked the payout later.
     */
    public const EXCEEDS_REFUNDABLE = "Refund amount exceeds the student's available refundable balance.";

    protected $ledgerService;

    protected $balances;

    public function __construct(LedgerService $ledgerService, FeeBalanceService $balances)
    {
        $this->ledgerService = $ledgerService;
        $this->balances = $balances;
        // studentPayments is the AJAX source list for the refund form and
        // returns a student's payment history — it was unguarded.
        $this->middleware('can:fees.view')->only(['index', 'show', 'exportCsv', 'exportPdf', 'studentPayments']);
        $this->middleware('can:fees.collect')->only(['create', 'store']);
        $this->middleware('can:fees.approve')->only(['approve', 'reject']);
        $this->middleware('can:fees.manage')->only(['complete']);
    }

    public function index(Request $request)
    {
        $query = Refund::with(['student', 'requestedBy', 'reviewedBy', 'completedBy'])
            ->latest();

        if ($request->filled('status') && $request->status != '') {
            $query->where('status', $request->status);
        }
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        $refunds = $query->paginate(20)->withQueryString();

        $metrics = [
            'requested' => Refund::where('status', 'requested')->sum('amount'),
            'approved' => Refund::where('status', 'approved')->sum('amount'),
            'completed' => Refund::where('status', 'completed')->sum('amount'),
            'rejected' => Refund::where('status', 'rejected')->sum('amount'),
        ];

        return view('fee_management.refunds.index', compact('refunds', 'metrics'));
    }

    public function create()
    {
        $students = Student::orderBy('first_name')->get();
        return view('fee_management.refunds.create', compact('students'));
    }

    /**
     * AJAX: what the refund form needs for the chosen student — the payments
     * that can be refunded, and how much of the student's money is still
     * refundable.
     *
     * Reversed payments are excluded: they are not money the school holds, so
     * they cannot be refunded. The figures come from FeeBalanceService, the same
     * source the server-side validation uses, so the number shown on the form is
     * the number the backend enforces.
     */
    public function studentPayments($studentId)
    {
        $payments = FeePayment::with(['studentFeeAssignment.feeStructure.category'])
            ->whereHas('studentFeeAssignment', fn($q) => $q->where('student_id', $studentId))
            ->whereHas('studentFeeAssignment', fn($q) => $q->where('status', 'active'))
            ->whereNull('reversed_at')
            ->orderByDesc('payment_date')
            ->get();

        return response()->json([
            'summary' => $this->balances->refundSummaryForStudent((int) $studentId),
            'payments' => $payments->map(function ($p) {
                return [
                    'payment_id' => $p->payment_id,
                    'student_fee_assignment_id' => $p->student_fee_assignment_id,
                    'label' => ($p->receipt_number ?? 'RCP-'. $p->payment_id) . ' — ' . $p->payment_date->format('d M Y') . ' — ' . Money::format($p->amount) . ' (' . $p->payment_method . ')',
                    'amount' => (float) $p->amount,
                ];
            })->values(),
        ]);
    }

    /**
     * Resolve which charge a refund should be taken off, or fail.
     *
     * Prefers the charge chosen on the form, then the charge behind the payment
     * being refunded. A refund that resolves to neither is refused: it could not
     * be taken off any fee, so it would leave the student's balance disagreeing
     * with every per-charge figure in the module.
     *
     * @param  array{student_id: int|string, payment_id?: int|string|null, student_fee_assignment_id?: int|string|null}  $data
     */
    private function resolveRefundAssignment(array $data): StudentFeeAssignment
    {
        $payment = null;

        if (! empty($data['payment_id'])) {
            $payment = FeePayment::with('studentFeeAssignment')->find($data['payment_id']);

            if (! $payment) {
                throw ValidationException::withMessages(['payment_id' => 'That payment no longer exists.']);
            }

            if ((int) $payment->studentFeeAssignment?->student_id !== (int) $data['student_id']) {
                throw ValidationException::withMessages(['payment_id' => 'That payment belongs to a different student.']);
            }
        }

        $assignmentId = ($data['student_fee_assignment_id'] ?? null) ?: $payment?->student_fee_assignment_id;

        if (! $assignmentId) {
            throw ValidationException::withMessages([
                'student_fee_assignment_id' => 'Select the payment or the fee this refund relates to, so it can be taken off that charge.',
            ]);
        }

        $assignment = StudentFeeAssignment::find($assignmentId);

        if (! $assignment) {
            throw ValidationException::withMessages(['student_fee_assignment_id' => 'That charge no longer exists.']);
        }

        if ((int) $assignment->student_id !== (int) $data['student_id']) {
            throw ValidationException::withMessages(['student_fee_assignment_id' => 'That charge belongs to a different student.']);
        }

        return $assignment;
    }

    /**
     * Refuse a refund larger than the student's money still available to refund.
     *
     * Enforced on the server at the two points that matter — when the request is
     * made, and again when the money is actually paid out — so the rule does not
     * depend on the form, or on the student's position not having moved since the
     * request was raised.
     */
    private function guardRefundableAmount(float $amount, int $studentId, ?int $excludeRefundId = null): void
    {
        $refundable = $this->balances->refundSummaryForStudent($studentId, $excludeRefundId);

        if (round($amount, 2) <= $refundable['maxRefundable']) {
            return;
        }

        throw ValidationException::withMessages([
            'amount' => self::EXCEEDS_REFUNDABLE
                . ' Valid payments: ' . Money::format($refundable['payments'])
                . ', already refunded: ' . Money::format($refundable['refunded'])
                . ($refundable['pending'] > 0 ? ', requested but not yet paid: ' . Money::format($refundable['pending']) : '')
                . ', available: ' . Money::format($refundable['maxRefundable']) . '.',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validate($request, \App\Models\Refund::$rules + [
            'payment_id' => 'nullable|exists:fee_payments,payment_id',
            'student_fee_assignment_id' => 'nullable|exists:student_fee_assignments,id',
        ]);

        $data['payment_id'] = $request->payment_id ?: null;

        // A refund must land on a specific charge, and must not exceed the money
        // the school is actually holding for this student. Both checks are
        // server-side: the form is a convenience, not the control.
        $assignment = $this->resolveRefundAssignment($data);
        $this->guardRefundableAmount((float) $data['amount'], (int) $data['student_id']);

        $data['student_fee_assignment_id'] = $assignment->id;
        $data['requested_by'] = auth()->id();
        $data['requested_at'] = now();
        $data['supporting_info'] = $request->supporting_info;

        $refund = Refund::create($data);

        AuditTrail::log('Fees', 'REFUND REQUEST', $refund->id, null, [
            'student_id' => $refund->student_id,
            'amount' => $refund->amount,
            'student_fee_assignment_id' => $refund->student_fee_assignment_id,
            'payment_id' => $refund->payment_id,
            'reason' => $refund->reason,
        ]);

        Flash::success('Refund request submitted for approval.');
        return redirect()->route('fees.refunds.show', $refund->id);
    }

    public function show($id)
    {
        $refund = Refund::with(['student', 'requestedBy', 'reviewedBy', 'completedBy', 'payment', 'studentFeeAssignment', 'bankAccount'])->findOrFail($id);

        // Active accounts (banks + cash office) for the payout selector.
        $bankAccounts = BankAccount::where('status', 'active')->orderBy('account_name')->get();

        return view('fee_management.refunds.show', compact('refund', 'bankAccounts'));
    }

    public function approve(Request $request, $id)
    {
        $refund = Refund::findOrFail($id);

        if (!in_array($refund->status, ['requested'])) {
            Flash::error('Only requested refunds can be approved.');
            return redirect()->route('fees.refunds.show', $refund->id);
        }

        $refund->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'approval_notes' => $request->approval_notes,
        ]);

        AuditTrail::log('Fees', 'REFUND APPROVED', $refund->id, null, [
            'amount' => $refund->amount,
            'notes' => $request->approval_notes,
        ]);

        Flash::success('Refund approved. It can now be completed.');
        return redirect()->route('fees.refunds.show', $refund->id);
    }

    public function reject(Request $request, $id)
    {
        $refund = Refund::findOrFail($id);

        if (!in_array($refund->status, ['requested'])) {
            Flash::error('Only requested refunds can be rejected.');
            return redirect()->route('fees.refunds.show', $refund->id);
        }

        $request->validate(['rejection_reason' => 'required|string']);

        $refund->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        AuditTrail::log('Fees', 'REFUND REJECTED', $refund->id, null, [
            'reason' => $request->rejection_reason,
        ]);

        Flash::success('Refund rejected.');
        return redirect()->route('fees.refunds.show', $refund->id);
    }

    public function complete(Request $request, $id)
    {
        $refund = Refund::findOrFail($id);

        if ($refund->status !== 'approved') {
            Flash::error('Only approved refunds can be completed.');
            return redirect()->route('fees.refunds.show', $refund->id);
        }

        $request->validate([
            'refund_method' => 'required|string',
            'refund_reference' => 'nullable|string',
            // The payout must be traceable to the account it left.
            'bank_account_id' => 'required|exists:bank_accounts,account_id',
        ]);

        $account = BankAccount::findOrFail($request->bank_account_id);

        // The position can have moved since the request was approved: another
        // refund may have completed, or a payment been reversed. Money is about
        // to leave the school, so the cap is re-checked here — this is the point
        // where it is actually enforced.
        try {
            $this->guardRefundableAmount((float) $refund->amount, (int) $refund->student_id, (int) $refund->id);
        } catch (ValidationException $e) {
            Flash::error(collect($e->errors())->flatten()->first());
            return redirect()->route('fees.refunds.show', $refund->id);
        }

        if ((float) $account->current_balance < (float) $refund->amount) {
            Flash::error(
                'Insufficient funds in ' . $account->account_name .
                ' (balance ' . Money::format($account->current_balance) . ').'
            );
            return redirect()->route('fees.refunds.show', $refund->id);
        }

        DB::beginTransaction();
        try {
            $refund->update([
                'status' => 'completed',
                'completed_by' => auth()->id(),
                'completed_at' => now(),
                'refund_method' => $request->refund_method,
                'refund_reference' => $request->refund_reference,
                'bank_account_id' => $account->account_id,
            ]);

            // Post the refund to the student's ledger as a debit: money has left
            // the school, so the student owes that amount again. See
            // LedgerService::postRefund() for why the direction matters.
            $entry = $this->ledgerService->postRefund($refund);
            $refund->update(['ledger_entry_id' => $entry->id]);

            // Recompute the student's running balances for good measure.
            $this->ledgerService->recomputeStudentBalance($refund->student_id);

            // `paid_amount` is the figure every screen reads for "settled", and
            // it feeds the profile, the arrears report, the dashboard, the portal
            // and the reminder job. It has to be rebuilt from the source of truth
            // now that this refund counts against the student's charges.
            $this->balances->recomputeForStudent((int) $refund->student_id);

            // Money actually leaves the school here: decrement the account
            // balance and write the matching withdrawal row in the same
            // commit, so the bank statement can never drift from the refund
            // register. The canonical description lets BankLedger::findFor()
            // locate the row for automatic reversal if the refund is undone.
            BankLedger::recordWithdrawal(
                $account,
                (float) $refund->amount,
                now()->toDateString(),
                BankLedger::describe('Refund', (int) $refund->id, $refund->student->full_name),
                $request->refund_reference,
                auth()->id()
            );

            AuditTrail::log('Fees', 'REFUND COMPLETED', $refund->id, null, [
                'amount' => $refund->amount,
                'method' => $refund->refund_method,
                'reference' => $refund->refund_reference,
                'paid_from_account_id' => $account->account_id,
            ]);

            DB::commit();

            Flash::success('Refund completed — posted to the student ledger and drawn from ' . $account->account_name . '.');
            return redirect()->route('fees.refunds.show', $refund->id);
        } catch (\Exception $e) {
            DB::rollBack();
            Flash::error('Error completing refund: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function exportPdf(Request $request)
    {
        $refunds = Refund::with(['student', 'requestedBy', 'reviewedBy'])
            ->latest()
            ->when($request->filled('status') && $request->status != '', fn($q) => $q->where('status', $request->status))
            ->get();

        $pdf = Pdf::loadView('fee_management.refunds.exports.pdf', compact('refunds'));
        $pdf->setPaper('A4', 'landscape');
        return $pdf->download('refund-register-' . date('Y-m-d') . '.pdf');
    }

    public function exportCsv(Request $request)
    {
        $refunds = Refund::with(['student'])
            ->latest()
            ->when($request->filled('status') && $request->status != '', fn($q) => $q->where('status', $request->status))
            ->get();

        $filename = 'refund-register-' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($refunds) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Student', 'Admission No', 'Amount', 'Status', 'Reason', 'Requested By', 'Requested At', 'Completed At']);
            foreach ($refunds as $r) {
                fputcsv($file, [
                    $r->id,
                    $r->student->full_name ?? '',
                    $r->student->admission_no ?? '',
                    Money::number($r->amount),
                    $r->status,
                    $r->reason,
                    $r->requestedBy->name ?? '',
                    $r->requested_at ? $r->requested_at->format('Y-m-d H:i') : '',
                    $r->completed_at ? $r->completed_at->format('Y-m-d H:i') : '',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
