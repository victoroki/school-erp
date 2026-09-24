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
    public function __construct()
    {
        $this->middleware('can:fees.view');
    }

    public function index()
    {
        $currentYear = AcademicYear::where('is_current', true)->first();
        $yearId = $currentYear ? $currentYear->academic_year_id : null;

        // Finance Service metrics
        $financeService = new \App\Services\FinanceService();
        $metrics = $financeService->getMetrics();

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

        // Outstanding comes from the same engine every other screen uses, so the
        // dashboard cannot disagree with a student's own balance. It deliberately
        // replaces `expectedRevenue - total_collected`: that compared the
        // receivable against gross payments received, so after a refund it still
        // counted money the school had already handed back and reported less
        // outstanding than the student's own page.
        $outstanding = app(\App\Services\FeeBalanceService::class)->outstandingForAssignments(
            StudentFeeAssignment::where('status', 'active')
                ->when($yearId, function ($q) use ($yearId) {
                    return $q->where('academic_year_id', $yearId);
                })
                ->pluck('id')
                ->all()
        );

        // Collected net of refunds, derived from the two figures above rather
        // than from a separate payment sum — so Expected − Collected =
        // Outstanding holds by construction instead of usually holding.
        $collected = round($expectedRevenue - $outstanding, 2);

        // The rate uses the same net figures as the card beside it, or the
        // percentage would not match the shillings printed underneath it.
        $collectionRate = $expectedRevenue > 0
            ? round(max(0, min(100, ($collected / $expectedRevenue) * 100)), 1)
            : 0;

        // Pending Discount Approvals
        $pendingApprovals = StudentDiscount::where('approval_status', 'pending')
             ->when($yearId, function($q) use ($yearId) {
                return $q->where('academic_year_id', $yearId);
            })->count();

        // 2. Statistics
        $totalStudents = \App\Models\Student::where('status', 'active')->count();
        
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

        // 5. Recent Activity (Approvals)
        $recentApprovals = StudentDiscount::with(['student', 'discountScheme', 'requester'])
            ->whereIn('approval_status', ['approved', 'rejected'])
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        // Fee data that needs a human decision (a refund paid out for more than
        // the student paid, a refund ledger row posted in the old direction).
        // Surfaced as a warning; never corrected automatically.
        $integrityFindings = app(\App\Services\FeeIntegrityService::class)->findings();

        return view('fee_management.dashboard', compact(
            'currentYear',
            'metrics',
            'integrityFindings',
            'totalFeeStructures',
            'expectedRevenue',
            'outstanding',
            'collected',
            'collectionRate',
            'totalDiscounts',
            'pendingApprovals',
            'notAssignedCount',
            'studentsWithFees',
            'revenueByClass',
            'recentApprovals'
        ));
    }
}
