<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AcademicYear;
use App\Models\FeeStructure;
use App\Models\StudentFeeAssignment;
use App\Models\DiscountScheme;
use App\Models\StudentDiscount;
use App\Models\SchoolClass;
use DB;

class FeeDashboardController extends Controller
{
    public function index()
    {
        $currentYear = AcademicYear::where('is_current', true)->first();
        $yearId = $currentYear ? $currentYear->academic_year_id : null;

        // 1. Summary Cards
        $totalFeeStructures = FeeStructure::where('status', 'active')
            ->when($yearId, function($q) use ($yearId) {
                return $q->where('academic_year_id', $yearId);
            })->count();

        // Expected Revenue from Assignments
        $expectedRevenue = StudentFeeAssignment::where('status', 'active')
            ->when($yearId, function($q) use ($yearId) {
                return $q->where('academic_year_id', $yearId);
            })->sum('final_amount');

        // Total Discounts Given (Assigned)
        $totalDiscounts = StudentFeeAssignment::where('status', 'active')
            ->when($yearId, function($q) use ($yearId) {
                return $q->where('academic_year_id', $yearId);
            })->sum('discount_amount');

        // Pending Discount Approvals
        $pendingApprovals = StudentDiscount::where('approval_status', 'pending')
             ->when($yearId, function($q) use ($yearId) {
                return $q->where('academic_year_id', $yearId);
            })->count();

        // 2. Statistics
        // Assignments
        $totalStudents = \App\Models\Student::where('status', 'active')->count(); // Assuming active students
        
        // Count students with at least one active assignment in current year
        $studentsWithFees = StudentFeeAssignment::where('status', 'active')
            ->when($yearId, function($q) use ($yearId) {
                return $q->where('academic_year_id', $yearId);
            })
            ->distinct('student_id')
            ->count('student_id');

        $notAssignedCount = max(0, $totalStudents - $studentsWithFees);

        // 3. Expected Revenue by Class (Top 5)
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
            ->orderByDesc('total')
            ->take(5)
            ->get();

        // 4. Quick Actions (Just Links in View)

        // 5. Recent Activity (Approvals)
        $recentApprovals = StudentDiscount::with(['student', 'discountScheme', 'requester'])
            ->whereIn('approval_status', ['approved', 'rejected'])
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        return view('fee_management.dashboard', compact(
            'currentYear',
            'totalFeeStructures',
            'expectedRevenue',
            'totalDiscounts',
            'pendingApprovals',
            'notAssignedCount',
            'studentsWithFees',
            'revenueByClass',
            'recentApprovals'
        ));
    }
}
