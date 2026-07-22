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
            return view('portal.fees', ['assignments' => collect(), 'message' => 'No student profile found.']);
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
    protected function resolveStudent($user): ?Student
    {
        if ($user->user_type === 'student' && $user->student) {
            return $user->student;
        }

        if ($user->user_type === 'parent' && $user->parent) {
            // Return the first linked child
            return $user->parent->students->first();
        }

        return null;
    }
}
