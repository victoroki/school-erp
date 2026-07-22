<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\StudentClassEnrollment;
use Flash;

class StudentTransferController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:students.manage');
    }

    /**
     * Show the form for transferring a student.
     */
    public function index()
    {
        // Get only active students
        $students = Student::where('status', 'active')->get();
        return view('students.transfer.index', compact('students'));
    }

    /**
     * Process the student transfer.
     */
    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,student_id',
            'transfer_date' => 'required|date',
            'transfer_reason' => 'required|string',
            'transfer_certificate_no' => 'nullable|string'
        ]);

        $student = Student::findOrFail($request->student_id);
        
        // Update student status
        $student->status = 'transferred';
        $student->enrollment_status = 'transferred';
        $student->transfer_date = $request->transfer_date;
        $student->transfer_reason = $request->transfer_reason;
        $student->transfer_certificate_no = $request->transfer_certificate_no;
        $student->save();

        // Update active class enrollments to transferred
        StudentClassEnrollment::where('student_id', $student->student_id)
            ->where('status', 'active')
            ->update(['status' => 'transferred']);

        Flash::success('Student transferred successfully.');

        return redirect()->route('student-transfer.index');
    }
}
