<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\FeePayment;
use App\Models\Student;
use App\Models\StudentFeeAssignment;
use App\Models\StudentParentRelationship;
use App\Services\FinanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MobileFeeController extends Controller
{
    /**
     * GET /api/mobile/fees/summary
     *
     * Returns fee summaries (assigned / paid / balance / status) for the
     * students visible to the current user.
     */
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load('roles');

        $studentIds = $this->visibleStudentIds($user);

        $assignments = StudentFeeAssignment::whereIn('student_id', $studentIds)
            ->where('status', 'active')
            ->with('student')
            ->get();

        // Group by student.
        $grouped = $assignments->groupBy('student_id')->map(function ($items, $studentId) {
            $student = $items->first()->student;
            $totalAssigned = (float) $items->sum('final_amount');
            $totalPaid     = (float) $items->sum('paid_amount');
            $balance       = $totalAssigned - $totalPaid;

            $status = 'Unpaid';
            if ($balance <= 0) {
                $status = 'Paid';
            } elseif ($totalPaid > 0) {
                $status = 'Partial';
            }

            return [
                'student_id'    => (int) $studentId,
                'student_name'  => trim(($student?->first_name ?? '') . ' ' . ($student?->last_name ?? '')),
                'total_assigned' => $totalAssigned,
                'total_paid'    => $totalPaid,
                'balance'       => $balance,
                'status'        => $status,
            ];
        });

        return response()->json($grouped->values());
    }

    /**
     * GET /api/mobile/fees/student/{student_id}
     *
     * Returns a detailed breakdown of fee assignments for a specific student.
     */
    public function detailed(Request $request, $studentId): JsonResponse
    {
        $user = $request->user();
        if (!in_array((int)$studentId, $this->visibleStudentIds($user))) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $assignments = StudentFeeAssignment::where('student_id', $studentId)
            ->with('feeStructure')
            ->get()
            ->map(fn($a) => [
                // StudentFeeAssignment's primary key is `id` (the model has no
                // custom $primaryKey; the old student_fee_assignment_id
                // accessor always returned null and broke this endpoint).
                'assignment_id' => (int) $a->id,
                'fee_name'      => $a->feeStructure->name ?? 'Unknown Fee',
                'amount'        => (float) $a->final_amount,
                'paid'          => (float) $a->paid_amount,
                'balance'       => round((float) $a->final_amount - (float) $a->paid_amount, 2),
                'status'        => $a->status,
                'payment_status' => $a->payment_status,
                'term'          => $a->term,
            ]);

        return response()->json([
            'student_id' => (int)$studentId,
            'breakdown'  => $assignments,
        ]);
    }

    /**
     * GET /api/mobile/fees/student/{student_id}/history
     *
     * Returns payment history for a specific student.
     */
    public function paymentHistory(Request $request, $studentId): JsonResponse
    {
        $user = $request->user();
        if (!in_array((int)$studentId, $this->visibleStudentIds($user))) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        // The relation on FeePayment is studentFeeAssignment (there is no
        // `assignment` relation — the old whereHas('assignment') threw and
        // made this endpoint dead).
        $payments = FeePayment::whereHas('studentFeeAssignment', function ($q) use ($studentId) {
            $q->where('student_id', $studentId);
        })
        ->with('studentFeeAssignment.feeStructure')
        ->orderBy('payment_date', 'desc')
        ->orderBy('payment_id', 'desc')
        ->get()
        ->map(fn($p) => [
            'payment_id' => (int) $p->payment_id,
            'amount'     => (float) $p->amount,
            'date'       => $p->payment_date->toDateString(),
            'method'     => $p->payment_method,
            'reference'  => $p->transaction_id,
            'receipt_no' => $p->receipt_number,
            'fee_name'   => $p->studentFeeAssignment?->feeStructure?->name ?? 'Unknown',
        ]);

        return response()->json([
            'student_id' => (int)$studentId,
            'payments'   => $payments,
        ]);
    }

    /**
     * GET /api/mobile/fees/students?search=...
     *
     * Fee-workflow student search for the collection flow. Returns ONLY the
     * fields the fee workflow needs (identity + fee position) — deliberately
     * not the full student record (contact details, guardian info, etc.).
     * Scoped by the same visibility rules as the rest of this controller, and
     * requires fees.view (Accountant has it; Teacher/Parent/Student do not).
     */
    public function studentSearch(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user->hasPermission('fees.view')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $request->validate([
            'search' => 'nullable|string|max:100',
        ]);

        $studentIds = $this->visibleStudentIds($user);
        if (empty($studentIds)) {
            return response()->json(['students' => []]);
        }

        $query = Student::query()
            ->where('status', 'active')
            ->whereIn('student_id', $studentIds);

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('admission_no', 'like', "%{$search}%");
            });
        }

        // Fee position per student, in ONE aggregate query (no N+1):
        // expected vs paid over active assignments.
        $feeRows = StudentFeeAssignment::whereIn('student_id', $studentIds)
            ->where('status', 'active')
            ->selectRaw('student_id, SUM(final_amount) as expected, SUM(COALESCE(paid_amount,0)) as paid')
            ->groupBy('student_id')
            ->get()
            ->keyBy('student_id');

        $students = $query
            ->orderBy('admission_no')
            ->limit(50)
            ->get(['student_id', 'admission_no', 'first_name', 'middle_name', 'last_name'])
            ->map(function ($s) use ($feeRows) {
                $fee = $feeRows->get($s->student_id);
                $expected = (float) ($fee->expected ?? 0);
                $paid     = (float) ($fee->paid ?? 0);
                $balance  = round($expected - $paid, 2);

                return [
                    'student_id'    => (int) $s->student_id,
                    'admission_no'  => $s->admission_no,
                    'name'          => preg_replace('/\s+/', ' ', trim($s->first_name . ' ' . ($s->middle_name ?? '') . ' ' . $s->last_name)),
                    'total_assigned' => $expected,
                    'total_paid'    => $paid,
                    'balance'       => max(0, $balance),
                    'fee_status'    => $expected <= 0 ? 'none' : ($balance <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid')),
                ];
            });

        return response()->json(['students' => $students->values()]);
    }

    /**
     * POST /api/mobile/fees/collect
     *
     * Record a fee payment. Body:
     *  { student_id, amount, method, reference?, client_uuid, paid_at? }
     *
     * PHASE 3 invariants (money correctness is the whole point):
     *  - IDEMPOTENCY (server is the final authority): `client_uuid` is a
     *    client-generated UUID stored in fee_payments.client_reference (UNIQUE).
     *    A repeat submit — e.g. a retry after a lost response — replays the
     *    ORIGINAL payment (same receipt, `duplicate: true`) instead of
     *    creating a second one. Concurrent double-submits are caught by the
     *    unique index itself.
     *  - `paid_at` preserves the collected-on DATE the accountant intended
     *    (offline payments sync later but belong to their original day).
     *    payment_date is a DATE column — day-level is the ERP's payment model.
     *    Future dates are rejected; the default is the server's today.
     *  - `method` must be one of the enum values the web UI offers
     *    (cash|check|card|bank_transfer|online — "Online / M-Pesa" is `online`
     *    with the manual reference in `reference`). Anything else is a 422;
     *    MySQL silently coerces invalid enum values to '' and we've seen that
     *    corrupt data before.
     *  - The write goes through FinanceService::recordTotalPayment — the SAME
     *    transactional path the web uses (allocations via LedgerService,
     *    ledger credit entries, RCP- receipt). Overpayment behaves exactly as
     *    the web: allocation caps at outstanding balances; the client is
     *    blocked from submitting more than the total balance to stay
     *    consistent with the web form.
     *  - Authorization: fees.collect permission, plus the student must be in
     *    the caller's visible scope (Teacher/Parent/Student → empty/forbidden).
     */
    public function collect(Request $request, FinanceService $finance): JsonResponse
    {
        $user = $request->user();
        if (! $user->hasPermission('fees.collect')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $data = $request->validate([
            'student_id'  => 'required|integer',
            'amount'      => 'required|numeric|min:0.01',
            'method'      => ['required', 'string', Rule::in(FeePayment::PAYMENT_METHODS)],
            'reference'   => 'nullable|string|max:100',
            // Accepts any stable client-generated identifier (8–64 chars of
            // [A-Za-z0-9-]): real UUIDs from current app builds, and the
            // legacy `pay-<ts>-<id>` ids queued on devices by Phase 2 builds
            // so already-queued payments can still sync instead of being
            // rejected. Format is irrelevant — uniqueness is what dedupes.
            'client_uuid' => 'required|string|min:8|max:64|regex:/^[A-Za-z0-9\-]+$/',
            'paid_at'     => 'nullable|date|before_or_equal:today',
        ]);

        $clientUuid = trim((string) $data['client_uuid']);
        $studentId  = (int) $data['student_id'];

        if (! in_array($studentId, $this->visibleStudentIds($user))) {
            return response()->json(['message' => 'Student not found in your scope.'], 404);
        }

        // Replay check BEFORE doing any work: same logical payment was already
        // accepted (lost-response retry, outbox re-flush, double sync).
        if ($existing = FeePayment::where('client_reference', $clientUuid)->first()) {
            return $this->replayResponse($existing, true);
        }

        $paymentDate = isset($data['paid_at'])
            ? \Illuminate\Support\Carbon::parse($data['paid_at'])->toDateString()
            : now()->toDateString();

        $amount = round((float) $data['amount'], 2);

        try {
            $payment = DB::transaction(function () use ($finance, $studentId, $amount, $data, $clientUuid, $paymentDate) {
                // Lock the outstanding assignments so a concurrent duplicate
                // (two in-flight retries) cannot both see room to allocate.
                $assignments = StudentFeeAssignment::where('student_id', $studentId)
                    ->where('status', 'active')
                    ->whereRaw('COALESCE(paid_amount, 0) < final_amount')
                    ->lockForUpdate()
                    ->get();

                $totalBalance = (float) $assignments->sum(fn ($a) => (float) $a->final_amount - (float) $a->paid_amount);
                if ($assignments->isEmpty() || $totalBalance <= 0) {
                    throw new \App\Exceptions\MobileFeeException('No outstanding fees for this student.', 422);
                }
                if ($amount > round($totalBalance, 2) + 0.009) {
                    // Mirrors the web form, which blocks amounts above balance.
                    throw new \App\Exceptions\MobileFeeException(
                        'Payment exceeds the outstanding balance of ' . number_format($totalBalance, 2) . '.',
                        422,
                        ['balance' => round($totalBalance, 2)]
                    );
                }

                return $finance->recordTotalPayment([
                    'amount'          => $amount,
                    'payment_date'    => $paymentDate,
                    'payment_method'  => $data['method'],
                    'transaction_id'  => $data['reference'] ?? null,
                    'client_reference' => $clientUuid,
                    'remarks'         => 'Mobile collection',
                    'allocation_strategy' => 'oldest_first',
                ], $assignments);
            });
        } catch (\App\Exceptions\MobileFeeException $e) {
            return response()->json(
                array_merge(['message' => $e->getMessage()], $e->context),
                $e->status
            );
        } catch (\Illuminate\Database\QueryException $e) {
            // Lost the unique-index race against a concurrent duplicate:
            // the other insert won, so replay its row instead of failing.
            if ($this->isDuplicateKey($e) && $existing = FeePayment::where('client_reference', $clientUuid)->first()) {
                return $this->replayResponse($existing, true);
            }
            report($e);

            return response()->json(['message' => 'Payment could not be recorded. Try again.'], 500);
        } catch (\Exception $e) {
            report($e);

            return response()->json(['message' => 'Payment could not be recorded: ' . $e->getMessage()], 500);
        }

        return $this->replayResponse($payment, false);
    }

    /**
     * Canonical success shape — identical whether this is the original
     * accept or an idempotent replay, so the app can never tell a retry from
     * a fresh record and always has the server-issued receipt.
     */
    private function replayResponse(FeePayment $payment, bool $duplicate): JsonResponse
    {
        $assignment = $payment->studentFeeAssignment;

        return response()->json([
            'ok'          => true,
            'duplicate'   => $duplicate,
            'payment_id'  => (int) $payment->payment_id,
            'receipt_no'  => $payment->receipt_number,
            'amount'      => (float) $payment->amount,
            'method'      => $payment->payment_method,
            'paid_at'     => $payment->payment_date->toDateString(),
            'student_id'  => $assignment ? (int) $assignment->student_id : null,
            'server_received_at' => now()->toIso8601String(),
        ]);
    }

    private function isDuplicateKey(\Illuminate\Database\QueryException $e): bool
    {
        return str_contains($e->getMessage(), 'Duplicate entry')
            || str_contains($e->getMessage(), 'UNIQUE constraint');
    }

    /**
     * Resolve the student IDs visible to the given user.
     */
    private function visibleStudentIds($user): array
    {
        if ($user->hasAnyRole(['Owner', 'Super Admin', 'Admin', 'Accountant'])) {
            return Student::where('status', 'active')->pluck('student_id')->toArray();
        }

        if ($user->hasRole('Parent')) {
            $parentRecord = \App\Models\Parents::where('user_id', $user->id)->first();
            if ($parentRecord) {
                return StudentParentRelationship::where('parent_id', $parentRecord->parent_id)
                    ->pluck('student_id')
                    ->toArray();
            }
            return [];
        }

        if ($user->hasRole('Student')) {
            $student = Student::where('user_id', $user->id)->first();
            return $student ? [$student->student_id] : [];
        }

        return [];
    }
}
