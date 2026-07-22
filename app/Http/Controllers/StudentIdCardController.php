<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class StudentIdCardController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:students.view');
    }

    public function generate($id)
    {
        $student = Student::with(['studentClassEnrollments.classSection.schoolClass', 'studentClassEnrollments.classSection.section', 'studentClassEnrollments.academicYear'])->findOrFail($id);
        
        return view('students.id_card', compact('student'));
    }

    public function bulk(Request $request)
    {
        $studentIds = $request->get('student_ids', []);
        if (empty($studentIds)) {
            return redirect()->back()->with('error', 'No students selected');
        }

        $students = Student::with(['studentClassEnrollments.classSection.schoolClass', 'studentClassEnrollments.classSection.section'])->whereIn('student_id', $studentIds)->get();

        return view('students.bulk_id_cards', compact('students'));
    }
}
