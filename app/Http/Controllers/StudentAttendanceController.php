<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\ClassSection;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Flash;

class StudentAttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:academics.view')->only(['index', 'show']);
        $this->middleware('can:academics.manage')->only(['store', 'update']);
    }

    public function index(Request $request)
    {
        $classSections = ClassSection::with(['schoolClass', 'section'])->get();
        $date = $request->get('date', date('Y-m-d'));
        $classSectionId = $request->get('class_section_id');

        $attendanceSummary = [];
        if ($classSectionId) {
            $students = Student::whereHas('studentClassEnrollments', function ($query) use ($classSectionId) {
                $query->where('class_section_id', $classSectionId)->where('is_current', true);
            })->get();

            $attendanceData = StudentAttendance::where('class_section_id', $classSectionId)
                ->whereDate('date', $date)
                ->get()
                ->keyBy('student_id');
            
            return view('student_attendance.mark', compact('classSections', 'students', 'attendanceData', 'date', 'classSectionId'));
        }

        return view('student_attendance.index', compact('classSections', 'date'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'class_section_id' => 'required|exists:class_sections,class_section_id',
            'date' => 'required|date',
            'attendance' => 'required|array',
        ]);

        $classSectionId = $request->class_section_id;
        $date = $request->date;
        $markedBy = auth()->id();

        foreach ($request->attendance as $studentId => $status) {
            StudentAttendance::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'class_section_id' => $classSectionId,
                    'date' => $date,
                ],
                [
                    'status' => $status,
                    'marked_by' => $markedBy,
                    'remarks' => $request->remarks[$studentId] ?? null,
                ]
            );
        }

        Flash::success('Attendance marked successfully for ' . $date);
        return redirect()->back();
    }

    public function report(Request $request)
    {
        $classSections = ClassSection::with(['schoolClass', 'section'])->get();
        $classSectionId = $request->get('class_section_id');
        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));

        if ($classSectionId) {
            $students = Student::whereHas('studentClassEnrollments', function ($query) use ($classSectionId) {
                $query->where('class_section_id', $classSectionId)->where('is_current', true);
            })->with(['studentAttendances' => function($q) use ($month, $year) {
                $q->whereMonth('date', $month)->whereYear('date', $year);
            }])->get();

            return view('student_attendance.report', compact('classSections', 'students', 'classSectionId', 'month', 'year'));
        }

        return view('student_attendance.report_filter', compact('classSections'));
    }
}
