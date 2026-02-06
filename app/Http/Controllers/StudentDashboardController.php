<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentClassEnrollment;
use App\Models\StudentDocument;
use App\Models\StudentFee;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StudentDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Get current academic year
        $currentAcademicYear = AcademicYear::where('is_current', true)->first();
        
        // Total Students
        $totalStudents = Student::where('status', 'active')->count();
        $previousTotal = Student::where('status', 'active')
            ->where('created_at', '<', Carbon::now()->subMonth())
            ->count();
        $studentTrend = $previousTotal > 0 ? (($totalStudents - $previousTotal) / $previousTotal) * 100 : 0;

        // New Admissions This Term
        $newAdmissions = Student::where('created_at', '>=', Carbon::now()->startOfMonth())
            ->count();

        // Gender Distribution
        $maleCount = Student::where('status', 'active')->where('gender', 'male')->count();
        $femaleCount = Student::where('status', 'active')->where('gender', 'female')->count();

        // Status Distribution
        $activeStudents = Student::where('status', 'active')->count();
        $inactiveStudents = Student::where('status', 'inactive')->count();
        $graduatedStudents = Student::where('status', 'graduated')->count();

        // Students with Pending Documents
        $studentsWithPendingDocs = Student::whereHas('studentDocuments', function($q) {
            $q->where('status', 'pending');
        })->count();

        // Fee Defaulters
        $feeDefaulters = StudentFee::where('status', 'unpaid')
            ->where('due_date', '<', Carbon::now())
            ->distinct('student_id')
            ->count('student_id');

        // Students by Class
        $studentsByClass = DB::table('student_class_enrollments')
            ->join('class_sections', 'student_class_enrollments.class_section_id', '=', 'class_sections.class_section_id')
            ->join('classes', 'class_sections.class_id', '=', 'classes.class_id')
            ->where('student_class_enrollments.is_current', true)
            ->select('classes.name as class_name', DB::raw('count(*) as total'))
            ->groupBy('classes.class_id', 'classes.name')
            ->get();

        // Recent Admissions
        $recentAdmissions = Student::with(['studentClassEnrollments' => function($q) {
                $q->where('is_current', true)->with('classSection.class', 'classSection.section');
            }])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Admission Trend (Last 6 months)
        $admissionTrend = Student::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('count(*) as total')
            )
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Age Distribution
        $ageDistribution = Student::select(
                DB::raw('FLOOR(DATEDIFF(CURDATE(), date_of_birth) / 365.25) as age'),
                DB::raw('count(*) as total')
            )
            ->where('status', 'active')
            ->whereNotNull('date_of_birth')
            ->groupBy('age')
            ->orderBy('age')
            ->get();

        return view('students.dashboard', compact(
            'totalStudents',
            'studentTrend',
            'newAdmissions',
            'maleCount',
            'femaleCount',
            'activeStudents',
            'inactiveStudents',
            'graduatedStudents',
            'studentsWithPendingDocs',
            'feeDefaulters',
            'studentsByClass',
            'recentAdmissions',
            'admissionTrend',
            'ageDistribution'
        ));
    }
}
