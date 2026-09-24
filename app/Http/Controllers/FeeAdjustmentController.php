<?php

namespace App\Http\Controllers;

use App\Models\FeeAdjustment;
use App\Models\StudentFeeAssignment;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\FeeAdjustmentAuditLog;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Flash;
use DB;

class FeeAdjustmentController extends Controller
{
    public function __construct()
    {
        // Reads: the list, one adjustment, the pending-approval queue, a
        // student's adjustment history, the audit log and the AJAX fee lookup
        // that feeds the create form. All of these previously had no gate.
        $this->middleware('can:fees.view')->only([
            'index', 'show', 'pendingApprovals', 'studentAdjustments', 'auditLog', 'getFeeAssignmentsForStudent',
        ]);
        $this->middleware('can:fees.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
        // Approving and rejecting are the two halves of one review decision.
        // Only 'approve' was gated, so any authenticated user could kill a
        // legitimate adjustment by POSTing to reject.
        $this->middleware('can:fees.approve')->only(['approve', 'reject']);
    }

    public function index(Request $request)
    {
        $query = FeeAdjustment::with(['student', 'studentFeeAssignment.feeStructure.category', 'requestedBy', 'approvedBy'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->filled('adjustment_type')) {
            $query->where('adjustment_type', $request->adjustment_type);
        }

        $adjustments = $query->paginate(20)->withQueryString();
        $students = Student::where('is_active', true)
            ->get()
            ->mapWithKeys(fn($s) => [$s->student_id => $s->full_name]);

        return view('fee_management.adjustments.index', compact('adjustments', 'students'));
    }

    public function create(Request $request)
    {
        $student = null;
        $feeAssignments = collect();

        // After a validation error the form re-renders on a plain GET with no
        // query string, so restore the student/year cascade from old() input —
        // otherwise the fee-category dropdown repopulates empty.
        $studentId = $request->input('student_id', old('student_id'));
        $academicYearId = $request->input('academic_year_id', old('academic_year_id'))
            ?? AcademicYear::where('is_current', true)->value('academic_year_id');

        if ($studentId) {
            $student = Student::find($studentId);

            if ($student) {
                $feeAssignments = StudentFeeAssignment::with(['feeStructure.category'])
                    ->where('student_id', $student->student_id)
                    ->where('academic_year_id', $academicYearId)
                    ->where('status', 'active')
                    ->get();
            }
        }

        $academicYears = AcademicYear::orderBy('start_date', 'desc')->pluck('name', 'academic_year_id');
        $currentYearId = AcademicYear::where('is_current', true)->value('academic_year_id');
        // Alias must not be `full_name`: Student::getFullNameAttribute() would
        // shadow the SQL alias and, with first_name/last_name not selected,
        // resolve every option to an empty string.
        $students = Student::where('is_active', true)
            ->selectRaw("student_id, CONCAT(first_name, ' ', last_name, ' (', admission_no, ')') as dropdown_name")
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->pluck('dropdown_name', 'student_id');

        return view('fee_management.adjustments.create', compact('student', 'feeAssignments', 'academicYears', 'currentYearId', 'students'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,student_id',
            'student_fee_assignment_id' => 'required|exists:student_fee_assignments,id',
            'adjustment_type' => 'required|in:reduction,increase,waiver',
            'new_amount' => 'required|numeric|min:0',
            'reason' => 'required|string|max:1000',
        ]);

        $assignment = StudentFeeAssignment::findOrFail($request->student_fee_assignment_id);

        if ($assignment->student_id != $request->student_id) {
            Flash::error('Fee assignment does not belong to the selected student.');
            return redirect()->back()->withInput();
        }

        $originalAmount = $assignment->amount;
        $newAmount = $request->new_amount;
        $adjustmentAmount = $originalAmount - $newAmount;

        if ($request->adjustment_type === 'waiver') {
            $newAmount = 0;
            $adjustmentAmount = $originalAmount;
        }

        DB::beginTransaction();
        try {
            $adjustment = FeeAdjustment::create([
                'student_fee_assignment_id' => $request->student_fee_assignment_id,
                'student_id' => $request->student_id,
                'original_amount' => $originalAmount,
                'new_amount' => $newAmount,
                'adjustment_amount' => $adjustmentAmount,
                'adjustment_type' => $request->adjustment_type,
                'reason' => $request->reason,
                'status' => 'pending',
                'requested_by' => auth()->id(),
                'requested_at' => now(),
            ]);

            $adjustment->logAction('created', auth()->id(), [
                'original_amount' => $originalAmount,
                'new_amount' => $newAmount,
                'adjustment_type' => $request->adjustment_type,
            ]);

            AuditTrail::log('Fee Adjustment', 'CREATE', $adjustment->id, null, $adjustment->toArray());

            DB::commit();

            Flash::success('Fee adjustment request created successfully and pending approval.');
            return redirect()->route('fees.adjustments.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Flash::error('Error creating adjustment: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function show($id)
    {
        $adjustment = FeeAdjustment::with([
            'student.studentClassEnrollments.classSection.schoolClass',
            'studentFeeAssignment.feeStructure.category',
            'studentFeeAssignment.academicYear',
            'requestedBy',
            'approvedBy',
            'auditLogs.user',
        ])->findOrFail($id);

        return view('fee_management.adjustments.show', compact('adjustment'));
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'approval_notes' => 'nullable|string|max:500',
        ]);

        $adjustment = FeeAdjustment::findOrFail($id);

        if ($adjustment->status !== 'pending') {
            Flash::error('This adjustment has already been processed.');
            return redirect()->back();
        }

        DB::beginTransaction();
        try {
            $adjustment->approve(auth()->id(), $request->approval_notes);

            $adjustment->logAction('approved', auth()->id(), [
                'notes' => $request->approval_notes,
            ]);

            AuditTrail::log('Fee Adjustment', 'APPROVE', $adjustment->id, ['status' => 'pending'], $adjustment->toArray());

            DB::commit();

            Flash::success('Fee adjustment approved successfully.');
            return redirect()->route('fees.adjustments.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Flash::error('Error approving adjustment: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $adjustment = FeeAdjustment::findOrFail($id);

        if ($adjustment->status !== 'pending') {
            Flash::error('This adjustment has already been processed.');
            return redirect()->back();
        }

        DB::beginTransaction();
        try {
            $adjustment->reject(auth()->id(), $request->rejection_reason);

            $adjustment->logAction('rejected', auth()->id(), [
                'reason' => $request->rejection_reason,
            ]);

            AuditTrail::log('Fee Adjustment', 'REJECT', $adjustment->id, ['status' => 'pending'], $adjustment->toArray());

            DB::commit();

            Flash::success('Fee adjustment rejected.');
            return redirect()->route('fees.adjustments.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Flash::error('Error rejecting adjustment: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function pendingApprovals()
    {
        $adjustments = FeeAdjustment::with(['student', 'studentFeeAssignment.feeStructure.category', 'requestedBy'])
            ->pending()
            ->orderBy('created_at', 'asc')
            ->paginate(20);

        return view('fee_management.adjustments.pending', compact('adjustments'));
    }

    public function studentAdjustments($studentId)
    {
        $student = Student::findOrFail($studentId);

        $adjustments = FeeAdjustment::with(['studentFeeAssignment.feeStructure.category', 'approvedBy'])
            ->where('student_id', $studentId)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('fee_management.adjustments.student_adjustments', compact('student', 'adjustments'));
    }

    public function auditLog($id)
    {
        $adjustment = FeeAdjustment::findOrFail($id);
        $logs = FeeAdjustmentAuditLog::with('user')
            ->where('fee_adjustment_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('fee_management.adjustments.audit_log', compact('adjustment', 'logs'));
    }

    public function getFeeAssignmentsForStudent(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,student_id',
            'academic_year_id' => 'nullable|exists:academic_years,academic_year_id',
        ]);

        $academicYearId = $request->academic_year_id ?? AcademicYear::where('is_current', true)->value('academic_year_id');

        $assignments = StudentFeeAssignment::with(['feeStructure.category'])
            ->where('student_id', $request->student_id)
            ->where('academic_year_id', $academicYearId)
            ->where('status', 'active')
            ->get()
            ->map(function ($assignment) {
                return [
                    'id' => $assignment->id,
                    'fee_name' => $assignment->feeStructure->category->name ?? 'Uncategorized',
                    'original_amount' => $assignment->amount,
                    'current_final_amount' => $assignment->final_amount,
                    'term' => $assignment->term,
                ];
            });

        return response()->json($assignments);
    }
}
