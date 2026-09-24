<?php

namespace App\Http\Controllers;

use Flash;
use App\Models\Student;
use App\Models\StudentFeeAssignment;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class FeeManagementController extends Controller
{
    protected $financeService;

    public function __construct(\App\Services\FinanceService $financeService)
    {
        $this->financeService = $financeService;
        // Read-only screens, plus the whole-school ledger exports (they expose
        // exactly what index already exposes, so they need the same gate).
        $this->middleware('can:fees.view')->only(['index', 'show', 'print', 'exportPdf', 'exportExcel']);
        $this->middleware('can:fees.print')->only(['printReceipt']);
        $this->middleware('can:fees.collect')->only(['collect', 'collectPayment', 'storePayment']);
        // Reversal voids money that was already receipted, so it must not be
        // reachable by whoever merely collects payments. It was previously in
        // NO middleware list at all: any authenticated user — Teacher, Parent,
        // Student — could POST to the reverse URL and void a real payment.
        $this->middleware('can:fees.manage')->only(['reverseForm', 'reversePayment']);
    }

    public function index(Request $request)
    {
        $query = Student::query()
            ->with(['feeAssignments', 'payments', 'studentClassEnrollments.classSection.schoolClass'])
            ->whereHas('feeAssignments');

        if ($request->has('status') && $request->status != '') {
            $status = $request->status;
            if ($status == 'unpaid') {
                $query->whereHas('feeAssignments', function ($q) {
                    $q->where('status', 'active')
                      ->where(function ($subQ) {
                          $subQ->whereNull('paid_amount')
                               ->orWhere('paid_amount', 0);
                      });
                });
            } elseif ($status == 'paid') {
                $query->whereHas('feeAssignments', function ($q) {
                    $q->where('status', 'active')
                      ->whereColumn('paid_amount', '>=', 'final_amount');
                });
            } elseif ($status == 'partial') {
                $query->whereHas('feeAssignments', function ($q) {
                    $q->where('status', 'active')
                      ->whereColumn('paid_amount', '>', 0)
                      ->whereColumn('paid_amount', '<', 'final_amount');
                });
            }
        }

        if ($request->has('class_id') && $request->class_id != '') {
            $query->whereHas('studentClassEnrollments.classSection.schoolClass', function($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('admission_no', 'like', "%{$search}%");
            });
        }

        $students = $query->paginate(10)->withQueryString();
        $metrics = $this->financeService->getMetrics();
        $classes = \App\Models\SchoolClass::pluck('name', 'class_id');

        return view('fee_management.index', compact('students', 'metrics', 'classes'));
    }

    public function collect(Request $request)
    {
        $query = Student::query()
            ->with(['feeAssignments', 'studentClassEnrollments.classSection.schoolClass'])
            ->where('is_active', true);

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('admission_no', 'like', "%{$q}%")
                    ->orWhere('first_name', 'like', "%{$q}%")
                    ->orWhere('middle_name', 'like', "%{$q}%")
                    ->orWhere('last_name', 'like', "%{$q}%");
            });
        }

        if ($request->filled('class_id')) {
            $query->whereHas('studentClassEnrollments.classSection.schoolClass', function ($sub) use ($request) {
                $sub->where('class_id', $request->class_id);
            });
        }

        $students = $query->orderBy('admission_no')->paginate(25)->withQueryString();
        $classes = \App\Models\SchoolClass::orderBy('name')->pluck('name', 'class_id');

        return view('fee_management.collect', compact('students', 'classes'));
    }

    public function show($id)
    {
        $student = Student::with([
            'feeAssignments.feeStructure.category',
            'feeAssignments.payments.collectedBy',
            'studentClassEnrollments.classSection.schoolClass'
        ])->findOrFail($id);

        // Load the chronological statement with running balance.
        //
        // Opening balances are DERIVED inside getStudentStatement() rather than
        // seeded into the table on first view — so opening a student's page no
        // longer writes financial rows as a side effect of a GET, students who
        // are never opened are still correct, and the statement's closing figure
        // cannot disagree with the balance shown everywhere else.
        $statement = app(\App\Services\LedgerService::class)->getStudentStatement($id);

        return view('fee_management.show', compact('student', 'statement'));
    }

    public function collectPayment($id)
    {
        $student = Student::with(['feeAssignments' => function($q) {
            $q->where('status', 'active')
              ->whereRaw('COALESCE(paid_amount, 0) < final_amount');
        }, 'feeAssignments.feeStructure.category'])->findOrFail($id);

        // Calculate total balance across all fee assignments
        $totalBalance = $student->feeAssignments->sum('balance');

        // One token per rendered form. A double-click resubmits the same token,
        // which the payment service recognises as the same payment; a genuine
        // second payment comes from a fresh page load with a new token.
        $submissionToken = (string) \Illuminate\Support\Str::uuid();

        return view('fee_management.collect_payment', compact('student', 'totalBalance', 'submissionToken'));
    }

    public function storePayment(Request $request, $id)
    {
        // This endpoint previously accepted $request->all() with no validation
        // at all, so an amount of 0, a negative amount, an arbitrary string, or
        // a fee assignment belonging to a different student would all be
        // accepted and written to the ledger.
        $validated = $request->validate([
            'student_fee_assignment_id' => 'required',
            'amount' => 'required|numeric|min:0.01|max:100000000',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:' . implode(',', \App\Models\FeePayment::PAYMENT_METHODS),
            'transaction_id' => 'nullable|string|max:100',
            'client_reference' => 'nullable|string|max:64',
            'remarks' => 'nullable|string|max:2000',
            'allocation_strategy' => 'nullable|in:oldest_first,manual',
        ]);

        $isTotalPayment = (string) $validated['student_fee_assignment_id'] === 'total';

        // After a successful payment the bursar is taken straight to the
        // printable receipt, which auto-triggers the browser print dialog.
        // "skip_print" (from the checkbox on the collect form) keeps the old
        // behaviour of landing on the student's fee detail page.
        $redirectToReceipt = ! $request->boolean('skip_print');

        // The URL carries the student; the posted assignment must belong to that
        // same student, otherwise a crafted POST could pay against another
        // student's fees while the redirect claims student X.
        if (! $isTotalPayment) {
            $ownsAssignment = \App\Models\StudentFeeAssignment::where('id', $validated['student_fee_assignment_id'])
                ->where('student_id', $id)
                ->exists();

            if (! $ownsAssignment) {
                Flash::error('That fee assignment does not belong to this student.');

                return redirect()->route('fee-management.show', $id);
            }
        }

        try {
            if ($isTotalPayment) {
                $student = Student::with(['feeAssignments' => function($q) {
                    $q->where('status', 'active')
                      ->whereRaw('COALESCE(paid_amount, 0) < final_amount');
                }])->findOrFail($id);

                $payment = $this->financeService->recordTotalPayment([
                    'amount' => $validated['amount'],
                    'payment_date' => $validated['payment_date'],
                    'payment_method' => $validated['payment_method'],
                    'transaction_id' => $validated['transaction_id'] ?? null,
                    'client_reference' => $validated['client_reference'] ?? null,
                    'remarks' => $validated['remarks'] ?? null,
                    'allocation_strategy' => $validated['allocation_strategy'] ?? 'oldest_first',
                ], $student->feeAssignments);

                AuditTrail::log('Fees', 'RECORD PAYMENT', $id, null, [
                    'student_id' => $id,
                    'amount' => $validated['amount'],
                    'payment_method' => $validated['payment_method'],
                    'type' => 'total',
                ]);

                Flash::success('Total payment recorded successfully across all fees. Your receipt is ready to print.');
            } else {
                $payment = $this->financeService->recordPayment($validated);

                AuditTrail::log('Fees', 'RECORD PAYMENT', $id, null, [
                    'student_id' => $id,
                    'student_fee_assignment_id' => $validated['student_fee_assignment_id'],
                    'amount' => $validated['amount'],
                    'payment_method' => $validated['payment_method'],
                ]);

                Flash::success('Payment recorded successfully.');
            }
            // A duplicate submission (idempotency token) returns the original
            // payment, so re-printing its receipt is safe and shows the bursar
            // the real receipt that was already issued.
            if ($redirectToReceipt && isset($payment) && $payment instanceof \App\Models\FeePayment) {
                return redirect()->route('fee-management.receipt', $payment->payment_id);
            }
        } catch (\Exception $e) {
            // Log the detail, show the user something safe.
            \Log::error('Payment recording failed for student ' . $id . ': ' . $e->getMessage());

            Flash::error('The payment could not be recorded. No money has been moved. Please try again or contact the administrator.');
        }

        return redirect()->route('fee-management.show', $id);
    }

    public function reverseForm($payment)
    {
        $payment = \App\Models\FeePayment::with(['studentFeeAssignment.student', 'studentFeeAssignment.feeStructure.category'])->findOrFail($payment);
        return view('fee_management.reverse_payment', compact('payment'));
    }

    public function reversePayment(Request $request, $payment)
    {
        $payment = \App\Models\FeePayment::find($payment);

        if (!$payment) {
            Flash::error('Payment not found.');
            return redirect()->route('fee-management.index');
        }

        try {
            $request->validate(['reason' => 'required|string|max:2000']);

            app(\App\Services\LedgerService::class)->reversePayment($payment, $request->reason, auth()->id());

            AuditTrail::log('Fees', 'REVERSE PAYMENT', $payment->payment_id, null, [
                'student_id' => $payment->studentFeeAssignment?->student_id,
                'amount' => $payment->amount,
                'receipt_number' => $payment->receipt_number,
                'reason' => $request->reason,
            ]);

            Flash::success('Payment reversed. The original record and reason are preserved for audit.');
        } catch (\Exception $e) {
            \Log::error('Payment reversal failed: ' . $e->getMessage());
            Flash::error('Error reversing payment: ' . $e->getMessage());
        }

        $studentId = $payment->studentFeeAssignment?->student_id ?? $request->student_id;
        return $studentId
            ? redirect()->route('fee-management.show', $studentId)
            : redirect()->route('fee-management.index');
    }

    public function print($id)
    {
        $student = Student::with([
            'feeAssignments.feeStructure.category',
            'feeAssignments.payments',
            'studentClassEnrollments.classSection.schoolClass'
        ])->findOrFail($id);

        return view('fee_management.print', compact('student'));
    }

    /**
     * Printable receipt for a single fee payment.
     *
     * Shown automatically after a payment is recorded (storePayment redirects
     * here) and reachable again from the student's payment history for
     * reprints. A reversed payment still prints — for audit — but with a
     * VOID watermark, matching how the receipt register and the student fees
     * tab treat reversed receipts.
     */
    public function printReceipt($payment)
    {
        $payment = \App\Models\FeePayment::with([
            'studentFeeAssignment.student.studentClassEnrollments.classSection.schoolClass',
            'studentFeeAssignment.feeStructure.category',
            'studentFeeAssignment.feeStructure.academicYear',
            'allocations.studentFeeAssignment.feeStructure.category',
            'collectedBy',
        ])->findOrFail($payment);

        $student = $payment->studentFeeAssignment->student;

        return view('fee_management.receipt', [
            'payment' => $payment,
            'student' => $student,
            'amountInWords' => $this->amountInWords((float) $payment->amount),
        ]);
    }

    /**
     * Spell an amount in words (Kenyan Shillings) for the receipt's legal line.
     */
    private function amountInWords(float $amount): string
    {
        $shillings = (int) floor($amount);
        $cents = (int) round(($amount - $shillings) * 100);

        if ($cents === 100) {
            $shillings++;
            $cents = 0;
        }

        $words = $this->numberToWords($shillings) . ' shilling' . ($shillings === 1 ? '' : 's');

        if ($cents > 0) {
            $words .= ' and ' . $this->numberToWords($cents) . ' cent' . ($cents === 1 ? '' : 's');
        }

        return ucfirst($words . ' only');
    }

    private function numberToWords(int $number): string
    {
        if ($number === 0) {
            return 'zero';
        }

        $ones = [
            '', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine',
            'ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen',
            'seventeen', 'eighteen', 'nineteen',
        ];
        $tens = ['', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];

        $chunks = [
            ['value' => 1000000000, 'label' => 'billion'],
            ['value' => 1000000, 'label' => 'million'],
            ['value' => 1000, 'label' => 'thousand'],
            ['value' => 100, 'label' => 'hundred'],
        ];

        $parts = [];

        foreach ($chunks as $chunk) {
            if ($number >= $chunk['value']) {
                $count = intdiv($number, $chunk['value']);
                $parts[] = trim($this->numberToWords($count) . ' ' . $chunk['label']);
                $number %= $chunk['value'];
            }
        }

        if ($number >= 20) {
            $part = $tens[intdiv($number, 10)];
            if ($number % 10 > 0) {
                $part .= '-' . $ones[$number % 10];
            }
            $parts[] = $part;
            $number = 0;
        }

        if ($number > 0) {
            $parts[] = $ones[$number];
        }

        return implode(' ', array_filter($parts));
    }

    public function exportPdf(Request $request)
    {
        $query = Student::query()
            ->with(['feeAssignments', 'payments', 'studentClassEnrollments.classSection.schoolClass'])
            ->whereHas('feeAssignments');

        if ($request->has('status') && $request->status != '') {
            $status = $request->status;
            if ($status == 'unpaid') {
                $query->whereHas('feeAssignments', function ($q) {
                    $q->where('status', 'active')
                      ->where(function ($subQ) {
                          $subQ->whereNull('paid_amount')
                               ->orWhere('paid_amount', 0);
                      });
                });
            } elseif ($status == 'paid') {
                $query->whereHas('feeAssignments', function ($q) {
                    $q->where('status', 'active')
                      ->whereColumn('paid_amount', '>=', 'final_amount');
                });
            } elseif ($status == 'partial') {
                $query->whereHas('feeAssignments', function ($q) {
                    $q->where('status', 'active')
                      ->whereColumn('paid_amount', '>', 0)
                      ->whereColumn('paid_amount', '<', 'final_amount');
                });
            }
        }

        if ($request->has('class_id') && $request->class_id != '') {
            $query->whereHas('studentClassEnrollments.classSection.schoolClass', function($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('admission_no', 'like', "%{$search}%");
            });
        }

        $students = $query->get();
        $metrics = $this->financeService->getMetrics();

        $pdf = Pdf::loadView('fee_management.export_pdf', compact('students', 'metrics'));
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('fee-report-' . date('Y-m-d') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $query = Student::query()
            ->with(['feeAssignments', 'payments', 'studentClassEnrollments.classSection.schoolClass'])
            ->whereHas('feeAssignments');

        if ($request->has('status') && $request->status != '') {
            $status = $request->status;
            if ($status == 'unpaid') {
                $query->whereHas('feeAssignments', function ($q) {
                    $q->where('status', 'active')
                      ->where(function ($subQ) {
                          $subQ->whereNull('paid_amount')
                               ->orWhere('paid_amount', 0);
                      });
                });
            } elseif ($status == 'paid') {
                $query->whereHas('feeAssignments', function ($q) {
                    $q->where('status', 'active')
                      ->whereColumn('paid_amount', '>=', 'final_amount');
                });
            } elseif ($status == 'partial') {
                $query->whereHas('feeAssignments', function ($q) {
                    $q->where('status', 'active')
                      ->whereColumn('paid_amount', '>', 0)
                      ->whereColumn('paid_amount', '<', 'final_amount');
                });
            }
        }

        if ($request->has('class_id') && $request->class_id != '') {
            $query->whereHas('studentClassEnrollments.classSection.schoolClass', function($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('admission_no', 'like', "%{$search}%");
            });
        }

        $students = $query->get();

        $filename = 'fee-report-' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($students) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Admission No', 'Student Name', 'Class', 'Total Fee', 'Paid', 'Balance', 'Status']);

            foreach ($students as $student) {
                $classInfo = '';
                foreach ($student->studentClassEnrollments as $enrollment) {
                    $classInfo .= ($enrollment->classSection->schoolClass->name ?? '') . ' - ' . ($enrollment->classSection->section->name ?? '') . '; ';
                }

                fputcsv($file, [
                    $student->admission_no,
                    $student->full_name,
                    rtrim($classInfo, '; '),
                    number_format($student->total_fee, 2),
                    number_format($student->paid_fee, 2),
                    number_format($student->balance_fee, 2),
                    $student->payment_status,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
