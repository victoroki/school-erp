<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\StudentFeeAssignment;
use App\Models\Student;
use App\Models\Parents;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class PortalFeeController extends Controller
{
    /**
     * List fee assignments for the authenticated user's child(ren).
     * Parent sees all linked children; student sees own only.
     */
    public function index(Request $request)
    {
        $user   = Auth::user();
        $student = $this->resolveStudent($user);

        if (!$student) {
            // The view reads $student in its header, so rendering it without
            // one was a 500 for every account with no linked learner. Same
            // answer as the report-card portal gives.
            abort(403, 'No student is linked to this account.');
        }

        $assignments = StudentFeeAssignment::with([
            'feeStructure.category',
            'feeStructure.academicYear',
            'payments',
            'discount',
        ])
            ->forStudent($student->student_id)
            ->latest()
            ->paginate(15);

        return view('portal.fees', compact('assignments', 'student'));
    }

    /**
     * Show a single fee assignment with payment history.
     */
    public function show(StudentFeeAssignment $studentFeeAssignment)
    {
        $user   = Auth::user();
        $student = $this->resolveStudent($user);

        if (!$student || $studentFeeAssignment->student_id !== $student->student_id) {
            abort(403, 'Unauthorized.');
        }

        $studentFeeAssignment->load([
            'feeStructure.category',
            'feeStructure.academicYear',
            'payments',
            'discount',
            'feeAdjustments',
        ]);

        return view('portal.fee-detail', ['assignment' => $studentFeeAssignment, 'student' => $student]);
    }

    /**
     * Download a payment receipt PDF for a specific fee assignment.
     */
    public function receipt(StudentFeeAssignment $studentFeeAssignment)
    {
        $user    = Auth::user();
        $student = $this->resolveStudent($user);

        if (!$student || $studentFeeAssignment->student_id !== $student->student_id) {
            abort(403, 'Unauthorized.');
        }

        $studentFeeAssignment->load([
            'feeStructure.category',
            'feeStructure.academicYear',
            'student',
            'payments',
        ]);

        $pdf = Pdf::loadView('portal.fee-receipt', ['assignment' => $studentFeeAssignment, 'student' => $student]);

        return $pdf->download("receipt-{$studentFeeAssignment->id}.pdf");
    }

    /**
     * Resolve the student record for the authenticated user.
     * Returns Student model for both parent and student user types.
     * Returns null if the user has no linked student.
     */
    /**
     * The learner whose fees this account may see: the student themself, or
     * a parent's first linked child.
     *
     * This used to read users.user_type, a legacy column the role system
     * never sets, so every parent and student was turned away from their own
     * fees. It now resolves the same way as the report-card portal.
     */
    protected function resolveStudent($user): ?Student
    {
        if ($student = \App\Services\PortalScopeService::selfStudent($user)) {
            return $student;
        }

        $children = app(\App\Services\PortalScopeService::class)->childrenFor($user);

        return $children->isEmpty() ? null : Student::find($children->first()['student_id']);
    }
}
