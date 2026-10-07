<?php

namespace App\Http\Controllers;

use App\Exceptions\HostelAllocationException;
use App\Models\AcademicYear;
use App\Models\FeeBulkReceipt;
use App\Models\FeePayment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Services\BulkReceiptService;
use Flash;
use Illuminate\Http\Request;

/**
 * Sponsor / bursary bulk receipts: money received once, distributed to
 * students through the standard fee payment chain.
 */
class BulkReceiptController extends Controller
{
    public function __construct(private BulkReceiptService $service)
    {
        // Recording money and distributing it are manage actions; reversing
        // them is heavier still. Viewing follows the fee module.
        $this->middleware('can:fees.view')->only(['index', 'show', 'printReceipt']);
        $this->middleware('can:fees.manage')->only(['create', 'store', 'allocate', 'reverseAllocation', 'reverseReceipt']);
    }

    public function index(Request $request)
    {
        // One filter definition, applied to the page and to the reconciliation
        // totals, so the figures on the strip always match the rows below.
        $applyFilters = function ($q) use ($request) {
            $q->when($request->filled('sponsor_type'), fn ($w) => $w->where('sponsor_type', $request->sponsor_type))
                ->when($request->filled('search'), function ($w) use ($request) {
                    $term = '%' . $request->search . '%';
                    $w->where(function ($s) use ($term) {
                        $s->where('sponsor_name', 'like', $term)
                            ->orWhere('reference_number', 'like', $term);
                    });
                });
        };

        $receipts = FeeBulkReceipt::with(['term', 'academicYear', 'bankAccount', 'createdBy'])
            ->where($applyFilters)
            ->withCount(['allocations as active_allocations_count' => fn ($q) => $q->whereNull('reversed_at')])
            ->orderByDesc('received_date')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        // Whole-set reconciliation: money received = money allocated to
        // students + money still sitting on receipts. Reversed receipts are
        // excluded from received (they are reported separately).
        $totals = FeeBulkReceipt::query()
            ->where($applyFilters)
            ->selectRaw('COUNT(*) as receipts_count')
            ->selectRaw('COALESCE(SUM(CASE WHEN reversed_at IS NULL THEN amount ELSE 0 END), 0) as received_total')
            ->selectRaw('COALESCE(SUM(CASE WHEN reversed_at IS NOT NULL THEN amount ELSE 0 END), 0) as reversed_total')
            ->first();

        $receivedTotal = round((float) $totals->received_total, 2);

        $allocatedTotal = round((float) FeePayment::query()
            ->whereNull('reversed_at')
            ->whereIn('bulk_receipt_id', FeeBulkReceipt::query()->where($applyFilters)->select('id'))
            ->sum('amount'), 2);

        return view('fee_management.bulk_receipts.index', [
            'receipts' => $receipts,
            'sponsorTypes' => FeeBulkReceipt::SPONSOR_TYPES,
            'filters' => $request->only(['sponsor_type', 'search']),
            'totals' => $totals,
            'receivedTotal' => $receivedTotal,
            'allocatedTotal' => $allocatedTotal,
            'unallocatedTotal' => round($receivedTotal - $allocatedTotal, 2),
        ]);
    }

    public function create()
    {
        return view('fee_management.bulk_receipts.create', [
            'sponsorTypes' => FeeBulkReceipt::SPONSOR_TYPES,
            'academicYears' => AcademicYear::orderByDesc('start_date')->pluck('name', 'academic_year_id')->toArray(),
            'terms' => \App\Models\Term::orderBy('start_date')->get()->pluck('name', 'id')->toArray(),
            'bankAccounts' => \App\Models\BankAccount::where('status', 'active')->pluck('account_name', 'account_id')->toArray(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sponsor_name' => 'required|string|max:150',
            'sponsor_type' => 'required|in:' . implode(',', FeeBulkReceipt::SPONSOR_TYPES),
            'reference_number' => 'nullable|string|max:100',
            'amount' => 'required|numeric|min:0.01|max:9999999999',
            'payment_method' => 'required|in:cash,check,card,bank_transfer,online',
            'bank_account_id' => 'nullable|exists:bank_accounts,account_id',
            'transaction_id' => 'nullable|string|max:100',
            'received_date' => 'required|date',
            'academic_year_id' => 'nullable|exists:academic_years,academic_year_id',
            'term_id' => 'nullable|exists:terms,id',
            'remarks' => 'nullable|string|max:2000',
            // Prevent double-POST creating two receipts for one banked amount.
            'client_reference' => 'nullable|string|max:64',
        ]);

        if (! empty($validated['client_reference'])) {
            $exists = FeeBulkReceipt::where('remarks', 'like', '%client_ref:' . $validated['client_reference'] . '%')->exists();
            if ($exists) {
                Flash::info('This receipt was already recorded.');

                return redirect()->route('fees.bulk-receipts.index');
            }
            $validated['remarks'] = trim(($validated['remarks'] ?? '') . ' client_ref:' . $validated['client_reference']);
        }

        try {
            $receipt = $this->service->createReceipt($validated);
        } catch (\RuntimeException $e) {
            Flash::error($e->getMessage());

            return redirect()->back()->withInput();
        }

        Flash::success(sprintf(
            'Receipt of %s from %s recorded. Allocate it to students below.',
            \App\Support\Money::format((float) $receipt->amount),
            $receipt->sponsor_name
        ));

        return redirect()->route('fees.bulk-receipts.show', $receipt->id);
    }

    public function show(Request $request, $id)
    {
        $receipt = FeeBulkReceipt::with(['term', 'academicYear', 'bankAccount', 'createdBy', 'reversedBy'])->find($id);

        if (! $receipt) {
            Flash::error('Bulk receipt not found.');

            return redirect()->route('fees.bulk-receipts.index');
        }

        $receipt->load(['allocations' => fn ($q) => $q->with([
            'studentFeeAssignment.student',
            'studentFeeAssignment.feeStructure.category',
        ])->latest('payment_id')]);

        // Students eligible for allocation dropdown: active students with an
        // outstanding balance, searchable client-side; supports class filter.
        $studentsQuery = Student::where('is_active', true)
            ->whereHas('feeAssignments', fn ($q) => $q->where('status', 'active')
                ->whereRaw('COALESCE(paid_amount, 0) < final_amount'));

        if ($request->filled('class_id')) {
            $studentsQuery->whereHas('studentClassEnrollments.classSection', function ($q) use ($request) {
                $q->where('class_id', $request->class_id)->where('is_current', true);
            });
        }

        $students = $studentsQuery
            ->with(['feeAssignments' => fn ($q) => $q->where('status', 'active')])
            ->orderBy('admission_no')
            ->limit(500)
            ->get()
            ->map(function ($student) {
                $balance = $student->feeAssignments
                    ->where('status', 'active')
                    ->sum(fn ($a) => max(0, (float) $a->final_amount - (float) $a->paid_amount));

                return (object) [
                    'student_id' => $student->student_id,
                    'name' => trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')),
                    'admission_no' => $student->admission_no,
                    'balance' => round($balance, 2),
                ];
            })
            ->filter(fn ($s) => $s->balance > 0)
            ->values();

        return view('fee_management.bulk_receipts.show', [
            'receipt' => $receipt,
            'students' => $students,
            'classes' => SchoolClass::orderBy('numeric_value')->pluck('name', 'class_id')->toArray(),
            'classFilter' => $request->integer('class_id') ?: null,
        ]);
    }

    /**
     * Printable sponsor / bursary receipt.
     *
     * Prints the money received, how it was distributed to students, and the
     * unallocated remainder — so the document itself shows that
     * allocated + unallocated equals the amount received.
     */
    public function printReceipt($id)
    {
        $receipt = FeeBulkReceipt::with(['term', 'academicYear', 'bankAccount', 'createdBy', 'reversedBy'])->find($id);

        if (! $receipt) {
            Flash::error('Bulk receipt not found.');

            return redirect()->route('fees.bulk-receipts.index');
        }

        $receipt->load(['allocations' => fn ($q) => $q->with([
            'studentFeeAssignment.student',
            'studentFeeAssignment.feeStructure.category',
        ])->orderBy('payment_id')]);

        $allocated = $receipt->allocatedAmount();

        return view('fee_management.bulk_receipts.receipt', [
            'receipt' => $receipt,
            'allocated' => $allocated,
            'remaining' => $receipt->remainingAmount(),
            'amountInWords' => \App\Support\Money::inWords((float) $receipt->amount),
        ]);
    }

    /**
     * Allocate part (or all) of a receipt to students.
     */
    public function allocate(Request $request, $id)
    {
        $validated = $request->validate([
            'allocations' => 'required|array|min:1',
            'allocations.*.student_id' => 'required|integer|exists:students,student_id',
            'allocations.*.amount' => 'required|numeric|min:0.01',
        ]);

        $receipt = FeeBulkReceipt::find($id);

        if (! $receipt) {
            Flash::error('Bulk receipt not found.');

            return redirect()->route('fees.bulk-receipts.index');
        }

        try {
            $created = $this->service->allocate($receipt, $validated['allocations']);
        } catch (\RuntimeException $e) {
            Flash::error($e->getMessage());

            return redirect()->back()->withInput();
        }

        Flash::success(sprintf(
            '%s allocated to %d student%s. Remaining on receipt: %s.',
            \App\Support\Money::format(collect($validated['allocations'])->sum(fn ($a) => (float) $a['amount'])),
            count($created),
            count($created) === 1 ? '' : 's',
            \App\Support\Money::format($receipt->remainingAmount())
        ));

        return redirect()->route('fees.bulk-receipts.show', $receipt->id);
    }

    /**
     * Reverse one student allocation, returning the money to the receipt.
     */
    public function reverseAllocation(Request $request, $id, $paymentId)
    {
        $validated = $request->validate(['reason' => 'required|string|max:500']);

        $receipt = FeeBulkReceipt::find($id);

        if (! $receipt) {
            Flash::error('Bulk receipt not found.');

            return redirect()->route('fees.bulk-receipts.index');
        }

        $payment = \App\Models\FeePayment::where('payment_id', $paymentId)
            ->where('bulk_receipt_id', $receipt->id)
            ->first();

        if (! $payment) {
            Flash::error('That allocation does not belong to this receipt.');

            return redirect()->route('fees.bulk-receipts.show', $receipt->id);
        }

        try {
            $this->service->reverseAllocation($payment, $validated['reason']);
        } catch (\RuntimeException $e) {
            Flash::error($e->getMessage());

            return redirect()->back();
        }

        Flash::success('Allocation reversed. The amount is back on the receipt.');

        return redirect()->route('fees.bulk-receipts.show', $receipt->id);
    }

    /**
     * Reverse the parent receipt (only possible when nothing is allocated).
     */
    public function reverseReceipt(Request $request, $id)
    {
        $validated = $request->validate(['reason' => 'required|string|max:500']);

        $receipt = FeeBulkReceipt::find($id);

        if (! $receipt) {
            Flash::error('Bulk receipt not found.');

            return redirect()->route('fees.bulk-receipts.index');
        }

        try {
            $this->service->reverseReceipt($receipt, $validated['reason']);
        } catch (\RuntimeException $e) {
            Flash::error($e->getMessage());

            return redirect()->back();
        }

        Flash::success('Bulk receipt reversed.');

        return redirect()->route('fees.bulk-receipts.show', $receipt->id);
    }
}
