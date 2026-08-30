<?php

namespace App\Http\Controllers;

use App\Models\Refund;
use App\Models\Student;
use App\Models\StudentFeeAssignment;
use App\Models\FeePayment;
use App\Models\AuditTrail;
use App\Services\LedgerService;
use Illuminate\Http\Request;
use Flash;
use Barryvdh\DomPDF\Facade\Pdf;

class RefundController extends Controller
{
    protected $ledgerService;

    public function __construct(LedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
        $this->middleware('can:fees.view')->only(['index', 'show', 'exportCsv', 'exportPdf']);
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
     * AJAX: return a student's payments to select as the refund source.
     */
    public function studentPayments($studentId)
    {
        $payments = FeePayment::with(['studentFeeAssignment.feeStructure.category'])
            ->whereHas('studentFeeAssignment', fn($q) => $q->where('student_id', $studentId))
            ->whereHas('studentFeeAssignment', fn($q) => $q->where('status', 'active'))
            ->orderByDesc('payment_date')
            ->get();

        return response()->json($payments->map(function ($p) {
            return [
                'payment_id' => $p->payment_id,
                'student_fee_assignment_id' => $p->student_fee_assignment_id,
                'label' => ($p->receipt_number ?? 'RCP-'. $p->payment_id) . ' — ' . $p->payment_date->format('d M Y') . ' — KSh ' . number_format($p->amount, 2) . ' (' . $p->payment_method . ')',
                'amount' => (float) $p->amount,
            ];
        }));
    }

    public function store(Request $request)
    {
        $data = $this->validate($request, \App\Models\Refund::$rules + [
            'payment_id' => 'nullable|exists:fee_payments,payment_id',
            'student_fee_assignment_id' => 'nullable|exists:student_fee_assignments,id',
        ]);

        $data['student_fee_assignment_id'] = $request->student_fee_assignment_id ?: null;
        $data['payment_id'] = $request->payment_id ?: null;
        $data['requested_by'] = auth()->id();
        $data['requested_at'] = now();
        $data['supporting_info'] = $request->supporting_info;

        $refund = Refund::create($data);

        AuditTrail::log('Fees', 'REFUND REQUEST', $refund->id, null, [
            'student_id' => $refund->student_id,
            'amount' => $refund->amount,
            'reason' => $refund->reason,
        ]);

        Flash::success('Refund request submitted for approval.');
        return redirect()->route('fees.refunds.show', $refund->id);
    }

    public function show($id)
    {
        $refund = Refund::with(['student', 'requestedBy', 'reviewedBy', 'completedBy', 'payment', 'studentFeeAssignment'])->findOrFail($id);
        return view('fee_management.refunds.show', compact('refund'));
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
        ]);

        $refund->update([
            'status' => 'completed',
            'completed_by' => auth()->id(),
            'completed_at' => now(),
            'refund_method' => $request->refund_method,
            'refund_reference' => $request->refund_reference,
        ]);

        // Post the refund to the student's ledger (credit).
        $entry = $this->ledgerService->postRefund($refund);
        $refund->update(['ledger_entry_id' => $entry->id]);

        // Recompute the student's running balances for good measure.
        $this->ledgerService->recomputeStudentBalance($refund->student_id);

        AuditTrail::log('Fees', 'REFUND COMPLETED', $refund->id, null, [
            'amount' => $refund->amount,
            'method' => $refund->refund_method,
            'reference' => $refund->refund_reference,
        ]);

        Flash::success('Refund completed and posted to the student ledger.');
        return redirect()->route('fees.refunds.show', $refund->id);
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
                    number_format($r->amount, 2),
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
