<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StudentFeeAssignment;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\FeeCategory;
use App\Models\StudentFee;
use DB;

class FeeReportsController extends Controller
{
    public function expectedRevenue(Request $request)
    {
        $currentYear = AcademicYear::where('is_current', true)->first();
        $yearId = $request->get('academic_year_id', $currentYear ? $currentYear->academic_year_id : null);

        $query = StudentFeeAssignment::where('status', 'active')
            ->when($yearId, function($q) use ($yearId) {
                return $q->where('academic_year_id', $yearId);
            });

        $totalExpected = $query->sum('final_amount');
        $totalOriginal = $query->sum('amount');
        $totalDiscounts = $query->sum('discount_amount');

        // Revenue by Class
        $revenueByClass = StudentFeeAssignment::join('student_class_enrollments', 'student_fee_assignments.student_id', '=', 'student_class_enrollments.student_id')
            ->join('class_sections', 'student_class_enrollments.class_section_id', '=', 'class_sections.class_section_id')
            ->join('classes', 'class_sections.class_id', '=', 'classes.class_id')
            ->where('student_fee_assignments.status', 'active')
            ->where('student_class_enrollments.status', 'active')
            ->when($yearId, function($q) use ($yearId) {
                 return $q->where('student_fee_assignments.academic_year_id', $yearId)
                          ->where('student_class_enrollments.academic_year_id', $yearId);
            })
            ->select('classes.name as class_name', DB::raw('SUM(student_fee_assignments.final_amount) as total'))
            ->groupBy('classes.name')
            ->get();

        // Revenue by Category
        $revenueByCategory = StudentFeeAssignment::join('fee_structures', 'student_fee_assignments.fee_structure_id', '=', 'fee_structures.fee_structure_id')
            ->join('fee_categories', 'fee_structures.category_id', '=', 'fee_categories.category_id')
            ->where('student_fee_assignments.status', 'active')
            ->when($yearId, function($q) use ($yearId) {
                return $q->where('student_fee_assignments.academic_year_id', $yearId);
            })
            ->select('fee_categories.name as category_name', DB::raw('SUM(student_fee_assignments.final_amount) as total'))
            ->groupBy('fee_categories.name')
            ->get();

        $academicYears = AcademicYear::pluck('name', 'academic_year_id');

        return view('fee_management.reports.expected_revenue', compact(
            'totalExpected', 
            'totalOriginal', 
            'totalDiscounts', 
            'revenueByClass', 
            'revenueByCategory',
            'academicYears',
            'yearId'
        ));
    }

    public function assignmentStatus(Request $request)
    {
        $currentYear = AcademicYear::where('is_current', true)->first();
        $yearId = $request->get('academic_year_id', $currentYear ? $currentYear->academic_year_id : null);
        $classId = $request->get('class_id');

        $query = StudentFeeAssignment::with(['student', 'feeStructure.category'])
            ->where('status', 'active')
            ->when($yearId, function($q) use ($yearId) {
                return $q->where('academic_year_id', $yearId);
            });

        if ($classId) {
            $query->whereHas('student.studentClassEnrollments.classSection', function($q) use ($classId) {
                $q->where('class_id', $classId)->where('is_current', true);
            });
        }

        $assignments = $query->paginate(20);
        $classes = SchoolClass::pluck('name', 'class_id');
        $academicYears = AcademicYear::pluck('name', 'academic_year_id');

        return view('fee_management.reports.assignment_status', compact('assignments', 'classes', 'academicYears', 'yearId', 'classId'));
    }

    public function discountSummary(Request $request)
    {
        $currentYear = AcademicYear::where('is_current', true)->first();
        $yearId = $request->get('academic_year_id', $currentYear ? $currentYear->academic_year_id : null);

        $query = StudentFeeAssignment::with(['student', 'discount'])
            ->where('status', 'active')
            ->where('discount_amount', '>', 0)
            ->when($yearId, function($q) use ($yearId) {
                return $q->where('academic_year_id', $yearId);
            });

        $discounts = $query->paginate(20);
        $totalDiscounts = $query->sum('discount_amount');
        $academicYears = AcademicYear::pluck('name', 'academic_year_id');

        return view('fee_management.reports.discount_summary', compact('discounts', 'totalDiscounts', 'academicYears', 'yearId'));
    }
}
