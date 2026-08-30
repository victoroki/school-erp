<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\ClassSection;
use App\Models\StudentClassEnrollment;
use App\Models\StudentFeeAssignment;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Pdf;
use DB;

class StudentReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:students.view');
    }

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
        $data = DB::table('student_attendance')
            ->join('students', 'student_attendance.student_id', '=', 'students.student_id')
            ->select('student_attendance.status as status', DB::raw('count(*) as count'))
            ->groupBy('student_attendance.status')
            ->get();

        return view('students.reports.attendance', compact('data'));
    }

    /**
     * Fee Status Report — students with outstanding balances
     */
    public function feeStatus(Request $request)
    {
        $currentYear = AcademicYear::where('is_current', true)->first();
        $yearId = $request->get('academic_year_id') ?: ($currentYear?->academic_year_id);
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();

        // Students with their fee summaries
        $students = Student::where('status', 'active')
            ->with(['studentClassEnrollments' => function ($q) use ($yearId) {
                $q->where('is_current', true)
                  ->with(['classSection.schoolClass', 'classSection.section']);
            }])
            ->get()
            ->filter(fn ($s) => $s->studentClassEnrollments->isNotEmpty())
            ->map(function ($student) use ($yearId) {
                $enrollment = $student->studentClassEnrollments->first();
                $classInfo = ($enrollment->classSection->schoolClass->name ?? 'N/A')
                    . ' - ' . ($enrollment->classSection->section->name ?? '');

                $assignments = StudentFeeAssignment::where('student_id', $student->student_id)
                    ->where('academic_year_id', $yearId)
                    ->where('status', 'active')
                    ->get();

                $totalAssigned = $assignments->sum('final_amount');
                $totalPaid = $student->payments()
                    ->whereIn('student_fee_assignment_id', $assignments->pluck('student_fee_assignment_id'))
                    ->sum('fee_payments.amount');
                $balance = $totalAssigned - $totalPaid;

                return (object) [
                    'student_id' => $student->student_id,
                    'full_name' => $student->full_name,
                    'admission_no' => $student->admission_no,
                    'class_info' => $classInfo,
                    'total_assigned' => $totalAssigned,
                    'total_paid' => $totalPaid,
                    'balance' => $balance,
                ];
            })
            ->sortBy('balance', SORT_REGULAR, true)
            ->values();

        $totalAssigned = $students->sum('total_assigned');
        $totalPaid = $students->sum('total_paid');
        $totalBalance = $students->sum('balance');
        $studentsWithArrears = $students->filter(fn ($s) => $s->balance > 0)->count();

        return view('students.reports.fee_status', compact(
            'students', 'academicYears', 'yearId', 'currentYear',
            'totalAssigned', 'totalPaid', 'totalBalance', 'studentsWithArrears'
        ));
    }

    /**
     * Age Distribution Report
     */
    public function ageDistribution()
    {
        $students = Student::where('status', 'active')
            ->whereNotNull('date_of_birth')
            ->get();

        $ageGroups = [
            'Under 5' => 0,
            '5-7' => 0,
            '8-10' => 0,
            '11-13' => 0,
            '14-16' => 0,
            '17-19' => 0,
            '20+' => 0,
        ];

        foreach ($students as $student) {
            $age = $student->age;
            if ($age === null) continue;

            if ($age < 5) $ageGroups['Under 5']++;
            elseif ($age <= 7) $ageGroups['5-7']++;
            elseif ($age <= 10) $ageGroups['8-10']++;
            elseif ($age <= 13) $ageGroups['11-13']++;
            elseif ($age <= 16) $ageGroups['14-16']++;
            elseif ($age <= 19) $ageGroups['17-19']++;
            else $ageGroups['20+']++;
        }

        $totalStudents = $students->count();
        $avgAge = $totalStudents > 0 ? round($students->avg('age'), 1) : 0;

        return view('students.reports.age_distribution', compact('ageGroups', 'totalStudents', 'avgAge'));
    }

    /**
     * Enrollment Trends — students enrolled per academic year
     */
    public function enrollmentTrends()
    {
        $academicYears = AcademicYear::orderBy('start_date', 'asc')->get();

        $trends = $academicYears->map(function ($year) {
            $count = StudentClassEnrollment::where('academic_year_id', $year->academic_year_id)
                ->where('is_current', true)
                ->distinct('student_id')
                ->count('student_id');

            $maleCount = StudentClassEnrollment::where('academic_year_id', $year->academic_year_id)
                ->where('is_current', true)
                ->whereHas('student', fn ($q) => $q->where('gender', 'male'))
                ->distinct('student_id')
                ->count('student_id');

            $femaleCount = StudentClassEnrollment::where('academic_year_id', $year->academic_year_id)
                ->where('is_current', true)
                ->whereHas('student', fn ($q) => $q->where('gender', 'female'))
                ->distinct('student_id')
                ->count('student_id');

            return (object) [
                'year_name' => $year->name,
                'year_id' => $year->academic_year_id,
                'total' => $count,
                'male' => $maleCount,
                'female' => $femaleCount,
                'is_current' => $year->is_current,
            ];
        });

        return view('students.reports.enrollment_trends', compact('trends'));
    }

    /**
     * Medical Report — students with medical conditions, allergies, medications
     */
    public function medicalReport()
    {
        $students = Student::where('status', 'active')
            ->where(function ($q) {
                $q->whereNotNull('medical_conditions')
                  ->where('medical_conditions', '!=', '')
                  ->orWhereNotNull('allergies')
                  ->where('allergies', '!=', '')
                  ->orWhereNotNull('medications')
                  ->where('medications', '!=', '');
            })
            ->with(['studentClassEnrollments' => function ($q) {
                $q->where('is_current', true)
                  ->with(['classSection.schoolClass', 'classSection.section']);
            }])
            ->get()
            ->map(function ($student) {
                $enrollment = $student->studentClassEnrollments->first();
                return (object) [
                    'student_id' => $student->student_id,
                    'full_name' => $student->full_name,
                    'admission_no' => $student->admission_no,
                    'gender' => $student->gender,
                    'blood_group' => $student->blood_group,
                    'class_info' => ($enrollment->classSection->schoolClass->name ?? 'N/A')
                        . ' - ' . ($enrollment->classSection->section->name ?? ''),
                    'medical_conditions' => $student->medical_conditions,
                    'allergies' => $student->allergies,
                    'medications' => $student->medications,
                    'emergency_contact' => $student->emergency_contact,
                    'emergency_contact_name' => $student->emergency_contact_name,
                ];
            })
            ->sortBy('full_name')
            ->values();

        $totalWithConditions = $students->filter(fn ($s) => !empty($s->medical_conditions))->count();
        $totalWithAllergies = $students->filter(fn ($s) => !empty($s->allergies))->count();
        $totalOnMedication = $students->filter(fn ($s) => !empty($s->medications))->count();

        return view('students.reports.medical', compact(
            'students', 'totalWithConditions', 'totalWithAllergies', 'totalOnMedication'
        ));
    }

    /**
     * Transport & Hostel Summary
     */
    public function transportHostelSummary()
    {
        $transportStudents = Student::where('status', 'active')
            ->where('uses_transport', true)
            ->with(['studentClassEnrollments' => function ($q) {
                $q->where('is_current', true)
                  ->with(['classSection.schoolClass', 'classSection.section']);
            }])
            ->get()
            ->map(function ($student) {
                $enrollment = $student->studentClassEnrollments->first();
                return (object) [
                    'full_name' => $student->full_name,
                    'admission_no' => $student->admission_no,
                    'class_info' => ($enrollment->classSection->schoolClass->name ?? 'N/A')
                        . ' - ' . ($enrollment->classSection->section->name ?? ''),
                    'route_id' => $student->route_id,
                    'pickup_point' => $student->pickup_point,
                ];
            })
            ->sortBy('full_name')
            ->values();

        $hostelStudents = Student::where('status', 'active')
            ->where('is_hosteller', true)
            ->with(['studentClassEnrollments' => function ($q) {
                $q->where('is_current', true)
                  ->with(['classSection.schoolClass', 'classSection.section']);
            }])
            ->get()
            ->map(function ($student) {
                $enrollment = $student->studentClassEnrollments->first();
                return (object) [
                    'full_name' => $student->full_name,
                    'admission_no' => $student->admission_no,
                    'class_info' => ($enrollment->classSection->schoolClass->name ?? 'N/A')
                        . ' - ' . ($enrollment->classSection->section->name ?? ''),
                ];
            })
            ->sortBy('full_name')
            ->values();

        $totalTransport = $transportStudents->count();
        $totalHostel = $hostelStudents->count();

        return view('students.reports.transport_hostel', compact(
            'transportStudents', 'hostelStudents', 'totalTransport', 'totalHostel'
        ));
    }

    // ─── CSV EXPORTS ──────────────────────────────────────────────────

    public function feeStatusCsv(Request $request)
    {
        $data = $this->getFeeStatusData($request);

        $headers = ['Content-Type' => 'text/csv'];
        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Admission No', 'Student Name', 'Class', 'Total Assigned', 'Total Paid', 'Balance', 'Status']);
            foreach ($data['students'] as $s) {
                $status = $s->balance <= 0 ? 'Paid' : ($s->balance < $s->total_assigned * 0.5 ? 'Partial' : 'Unpaid');
                fputcsv($file, [$s->admission_no, $s->full_name, $s->class_info, $s->total_assigned, $s->total_paid, $s->balance, $status]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function feeStatusPdf(Request $request)
    {
        $data = $this->getFeeStatusData($request);
        $pdf = Pdf::loadView('students.reports.fee_status_pdf', $data)->setPaper('a4', 'landscape');
        return $pdf->download('fee_status_report.pdf');
    }

    public function ageDistributionCsv()
    {
        $data = $this->getAgeDistributionData();
        $headers = ['Content-Type' => 'text/csv'];
        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Age Group', 'Student Count', 'Percentage']);
            foreach ($data['ageGroups'] as $label => $count) {
                $pct = $data['totalStudents'] > 0 ? round(($count / $data['totalStudents']) * 100, 1) : 0;
                fputcsv($file, [$label, $count, $pct . '%']);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function ageDistributionPdf()
    {
        $data = $this->getAgeDistributionData();
        $pdf = Pdf::loadView('students.reports.age_distribution_pdf', $data)->setPaper('a4', 'portrait');
        return $pdf->download('age_distribution_report.pdf');
    }

    public function enrollmentTrendsCsv()
    {
        $data = $this->getEnrollmentTrendsData();
        $headers = ['Content-Type' => 'text/csv'];
        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Academic Year', 'Total', 'Male', 'Female', 'Status']);
            foreach ($data['trends'] as $t) {
                fputcsv($file, [$t->year_name, $t->total, $t->male, $t->female, $t->is_current ? 'Current' : 'Completed']);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function enrollmentTrendsPdf()
    {
        $data = $this->getEnrollmentTrendsData();
        $pdf = Pdf::loadView('students.reports.enrollment_trends_pdf', $data)->setPaper('a4', 'landscape');
        return $pdf->download('enrollment_trends_report.pdf');
    }

    public function medicalReportCsv()
    {
        $data = $this->getMedicalData();
        $headers = ['Content-Type' => 'text/csv'];
        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Admission No', 'Student Name', 'Class', 'Blood Group', 'Medical Conditions', 'Allergies', 'Medications', 'Emergency Contact']);
            foreach ($data['students'] as $s) {
                fputcsv($file, [$s->admission_no, $s->full_name, $s->class_info, $s->blood_group ?? '', $s->medical_conditions ?? '', $s->allergies ?? '', $s->medications ?? '', $s->emergency_contact ?? '']);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function medicalReportPdf()
    {
        $data = $this->getMedicalData();
        $pdf = Pdf::loadView('students.reports.medical_pdf', $data)->setPaper('a4', 'landscape');
        return $pdf->download('medical_records_report.pdf');
    }

    public function transportHostelCsv()
    {
        $data = $this->getTransportHostelData();
        $headers = ['Content-Type' => 'text/csv'];
        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Admission No', 'Student Name', 'Class', 'Service Type', 'Route', 'Pickup Point']);
            foreach ($data['transportStudents'] as $s) {
                fputcsv($file, [$s->admission_no, $s->full_name, $s->class_info, 'Transport', $s->route_id ? 'Route #' . $s->route_id : '', $s->pickup_point ?? '']);
            }
            foreach ($data['hostelStudents'] as $s) {
                fputcsv($file, [$s->admission_no, $s->full_name, $s->class_info, 'Hostel', '', '']);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function transportHostelPdf()
    {
        $data = $this->getTransportHostelData();
        $pdf = Pdf::loadView('students.reports.transport_hostel_pdf', $data)->setPaper('a4', 'landscape');
        return $pdf->download('transport_hostel_report.pdf');
    }

    // ─── SHARED DATA METHODS ──────────────────────────────────────────

    private function getFeeStatusData(Request $request)
    {
        $currentYear = AcademicYear::where('is_current', true)->first();
        $yearId = $request->get('academic_year_id') ?: ($currentYear?->academic_year_id);
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();

        $students = Student::where('status', 'active')
            ->with(['studentClassEnrollments' => function ($q) use ($yearId) {
                $q->where('is_current', true)->with(['classSection.schoolClass', 'classSection.section']);
            }])
            ->get()
            ->filter(fn ($s) => $s->studentClassEnrollments->isNotEmpty())
            ->map(function ($student) use ($yearId) {
                $enrollment = $student->studentClassEnrollments->first();
                $classInfo = ($enrollment->classSection->schoolClass->name ?? 'N/A') . ' - ' . ($enrollment->classSection->section->name ?? '');
                $assignments = StudentFeeAssignment::where('student_id', $student->student_id)
                    ->where('academic_year_id', $yearId)->where('status', 'active')->get();
                $totalAssigned = $assignments->sum('final_amount');
                $totalPaid = $student->payments()->whereIn('student_fee_assignment_id', $assignments->pluck('student_fee_assignment_id'))->sum('fee_payments.amount');
                return (object) ['student_id' => $student->student_id, 'full_name' => $student->full_name, 'admission_no' => $student->admission_no, 'class_info' => $classInfo, 'total_assigned' => $totalAssigned, 'total_paid' => $totalPaid, 'balance' => $totalAssigned - $totalPaid];
            })
            ->sortBy('balance', SORT_REGULAR, true)->values();

        return compact('students', 'academicYears', 'yearId', 'currentYear',
            'totalAssigned', 'totalPaid', 'totalBalance', 'studentsWithArrears') + [
            'totalAssigned' => $students->sum('total_assigned'),
            'totalPaid' => $students->sum('total_paid'),
            'totalBalance' => $students->sum('balance'),
            'studentsWithArrears' => $students->filter(fn ($s) => $s->balance > 0)->count(),
        ];
    }

    private function getAgeDistributionData()
    {
        $students = Student::where('status', 'active')->whereNotNull('date_of_birth')->get();
        $ageGroups = ['Under 5' => 0, '5-7' => 0, '8-10' => 0, '11-13' => 0, '14-16' => 0, '17-19' => 0, '20+' => 0];
        foreach ($students as $s) {
            $age = $s->age;
            if ($age === null) continue;
            if ($age < 5) $ageGroups['Under 5']++;
            elseif ($age <= 7) $ageGroups['5-7']++;
            elseif ($age <= 10) $ageGroups['8-10']++;
            elseif ($age <= 13) $ageGroups['11-13']++;
            elseif ($age <= 16) $ageGroups['14-16']++;
            elseif ($age <= 19) $ageGroups['17-19']++;
            else $ageGroups['20+']++;
        }
        $totalStudents = $students->count();
        $avgAge = $totalStudents > 0 ? round($students->avg('age'), 1) : 0;
        return compact('ageGroups', 'totalStudents', 'avgAge');
    }

    private function getEnrollmentTrendsData()
    {
        $academicYears = AcademicYear::orderBy('start_date', 'asc')->get();
        $trends = $academicYears->map(function ($year) {
            $count = StudentClassEnrollment::where('academic_year_id', $year->academic_year_id)->where('is_current', true)->distinct('student_id')->count('student_id');
            $maleCount = StudentClassEnrollment::where('academic_year_id', $year->academic_year_id)->where('is_current', true)->whereHas('student', fn ($q) => $q->where('gender', 'male'))->distinct('student_id')->count('student_id');
            $femaleCount = StudentClassEnrollment::where('academic_year_id', $year->academic_year_id)->where('is_current', true)->whereHas('student', fn ($q) => $q->where('gender', 'female'))->distinct('student_id')->count('student_id');
            return (object) ['year_name' => $year->name, 'year_id' => $year->academic_year_id, 'total' => $count, 'male' => $maleCount, 'female' => $femaleCount, 'is_current' => $year->is_current];
        });
        return compact('trends');
    }

    private function getMedicalData()
    {
        $students = Student::where('status', 'active')
            ->where(function ($q) {
                $q->whereNotNull('medical_conditions')->where('medical_conditions', '!=', '')
                  ->orWhereNotNull('allergies')->where('allergies', '!=', '')
                  ->orWhereNotNull('medications')->where('medications', '!=', '');
            })
            ->with(['studentClassEnrollments' => function ($q) {
                $q->where('is_current', true)->with(['classSection.schoolClass', 'classSection.section']);
            }])
            ->get()
            ->map(function ($student) {
                $enrollment = $student->studentClassEnrollments->first();
                return (object) [
                    'student_id' => $student->student_id, 'full_name' => $student->full_name, 'admission_no' => $student->admission_no, 'gender' => $student->gender, 'blood_group' => $student->blood_group,
                    'class_info' => ($enrollment->classSection->schoolClass->name ?? 'N/A') . ' - ' . ($enrollment->classSection->section->name ?? ''),
                    'medical_conditions' => $student->medical_conditions, 'allergies' => $student->allergies, 'medications' => $student->medications,
                    'emergency_contact' => $student->emergency_contact, 'emergency_contact_name' => $student->emergency_contact_name,
                ];
            })
            ->sortBy('full_name')->values();
        $totalWithConditions = $students->filter(fn ($s) => !empty($s->medical_conditions))->count();
        $totalWithAllergies = $students->filter(fn ($s) => !empty($s->allergies))->count();
        $totalOnMedication = $students->filter(fn ($s) => !empty($s->medications))->count();
        return compact('students', 'totalWithConditions', 'totalWithAllergies', 'totalOnMedication');
    }

    private function getTransportHostelData()
    {
        $transportStudents = Student::where('status', 'active')->where('uses_transport', true)
            ->with(['studentClassEnrollments' => function ($q) {
                $q->where('is_current', true)->with(['classSection.schoolClass', 'classSection.section']);
            }])->get()
            ->map(function ($student) {
                $enrollment = $student->studentClassEnrollments->first();
                return (object) ['full_name' => $student->full_name, 'admission_no' => $student->admission_no,
                    'class_info' => ($enrollment->classSection->schoolClass->name ?? 'N/A') . ' - ' . ($enrollment->classSection->section->name ?? ''),
                    'route_id' => $student->route_id, 'pickup_point' => $student->pickup_point];
            })
            ->sortBy('full_name')->values();
        $hostelStudents = Student::where('status', 'active')->where('is_hosteller', true)
            ->with(['studentClassEnrollments' => function ($q) {
                $q->where('is_current', true)->with(['classSection.schoolClass', 'classSection.section']);
            }])->get()
            ->map(function ($student) {
                $enrollment = $student->studentClassEnrollments->first();
                return (object) ['full_name' => $student->full_name, 'admission_no' => $student->admission_no,
                    'class_info' => ($enrollment->classSection->schoolClass->name ?? 'N/A') . ' - ' . ($enrollment->classSection->section->name ?? '')];
            })
            ->sortBy('full_name')->values();
        $totalTransport = $transportStudents->count();
        $totalHostel = $hostelStudents->count();
        return compact('transportStudents', 'hostelStudents', 'totalTransport', 'totalHostel');
    }
}
