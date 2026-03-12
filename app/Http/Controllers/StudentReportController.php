<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\ClassSection;
use App\Models\StudentClassEnrollment;
use Illuminate\Http\Request;
use DB;

class StudentReportController extends Controller
{
    public function index()
    {
        return view('students.reports.index');
    }

    public function studentStrength(Request $request)
    {
        $reportData = DB::table('student_class_enrollments')
            ->join('class_sections', 'student_class_enrollments.class_section_id', '=', 'class_sections.class_section_id')
            ->join('classes', 'class_sections.class_id', '=', 'classes.class_id')
            ->join('sections', 'class_sections.section_id', '=', 'sections.section_id')
            ->where('student_class_enrollments.is_current', true)
            ->select(
                'classes.name as class_name',
                'sections.name as section_name',
                DB::raw('SUM(CASE WHEN EXISTS (SELECT 1 FROM students WHERE students.student_id = student_class_enrollments.student_id AND gender = "male") THEN 1 ELSE 0 END) as male'),
                DB::raw('SUM(CASE WHEN EXISTS (SELECT 1 FROM students WHERE students.student_id = student_class_enrollments.student_id AND gender = "female") THEN 1 ELSE 0 END) as female'),
                DB::raw('count(*) as total')
            )
            ->groupBy('classes.class_id', 'classes.name', 'sections.section_id', 'sections.name')
            ->get();

        return view('students.reports.strength', compact('reportData'));
    }

    public function genderRatio()
    {
        $data = Student::select('gender', DB::raw('count(*) as count'))
            ->groupBy('gender')
            ->get();
            
        return view('students.reports.gender', compact('data'));
    }

    public function attendanceSummary(Request $request)
    {
        // Simple aggregate for now
        $data = DB::table('student_attendance')
            ->join('students', 'student_attendance.student_id', '=', 'students.student_id')
            ->select('student_attendance.status as status', DB::raw('count(*) as count'))
            ->groupBy('student_attendance.status')
            ->get();

        return view('students.reports.attendance', compact('data'));
    }
}
