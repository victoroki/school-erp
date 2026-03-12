<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\Department;
use App\Models\LeaveApplication;
use App\Models\StaffDocument;
use App\Models\JobPosition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HRDashboardController extends Controller
{
    public function index()
    {
        // Summary Cards
        $totalStaff = Staff::count();
        $activeStaff = Staff::where('employment_status', 'active')->count();
        $onLeaveToday = LeaveApplication::where('final_status', 'approved')
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date', '>=', today())
            ->count();
        
        $pendingLeaveRequests = LeaveApplication::where('application_status', 'pending')->count();
        
        $vacantPositions = JobPosition::where('status', 'active')
            ->get()
            ->sum(function($position) {
                $filled = Staff::where('job_position_id', $position->position_id)
                    ->where('employment_status', 'active')
                    ->count();
                return max(0, $position->number_of_positions - $filled);
            });
        
        $contractsExpiringSoon = Staff::where('employment_status', 'active')
            ->where('employment_type', 'contract')
            ->whereNotNull('contract_end_date')
            ->whereDate('contract_end_date', '<=', now()->addDays(30))
            ->count();
        
        // This month's payroll (placeholder - would come from actual payroll)
        $thisMonthPayroll = Staff::where('employment_status', 'active')
            ->sum('basic_salary');

        // Staff by Department
        $staffByDepartment = Department::withCount(['staff' => function($q) {
            $q->where('employment_status', 'active');
        }])->get();

        // Staff by Employment Type
        $staffByType = Staff::where('employment_status', 'active')
            ->select('employment_type', DB::raw('count(*) as count'))
            ->groupBy('employment_type')
            ->get();

        // Gender Distribution
        $genderDistribution = Staff::where('employment_status', 'active')
            ->select('gender', DB::raw('count(*) as count'))
            ->groupBy('gender')
            ->get();

        // Staff on Leave Today
        $staffOnLeaveToday = LeaveApplication::with(['staff', 'leaveType'])
            ->where('final_status', 'approved')
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date', '>=', today())
            ->limit(10)
            ->get();

        // Recent Hires (This Month)
        $recentHires = Staff::whereMonth('date_of_joining', now()->month)
            ->whereYear('date_of_joining', now()->year)
            ->with('department', 'jobPosition')
            ->latest('date_of_joining')
            ->limit(5)
            ->get();

        // Upcoming Birthdays (Next 7 days)
        $upcomingBirthdays = Staff::where('employment_status', 'active')
            ->whereRaw('DAYOFYEAR(date_of_birth) BETWEEN DAYOFYEAR(NOW()) AND DAYOFYEAR(DATE_ADD(NOW(), INTERVAL 7 DAY))')
            ->orderByRaw('DAYOFYEAR(date_of_birth)')
            ->limit(5)
            ->get();

        // Documents Expiring Soon
        $documentsExpiringSoon = StaffDocument::with('staff')
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now()->addDays(30))
            ->whereDate('expiry_date', '>=', today())
            ->orderBy('expiry_date')
            ->limit(10)
            ->get();

        // Probation Ending Soon
        $probationEndingSoon = Staff::where('confirmation_status', 'on_probation')
            ->whereNotNull('probation_end_date')
            ->whereDate('probation_end_date', '<=', now()->addDays(30))
            ->with('department', 'jobPosition')
            ->orderBy('probation_end_date')
            ->limit(5)
            ->get();

        return view('hr.dashboard', compact(
            'totalStaff',
            'activeStaff',
            'onLeaveToday',
            'pendingLeaveRequests',
            'vacantPositions',
            'contractsExpiringSoon',
            'thisMonthPayroll',
            'staffByDepartment',
            'staffByType',
            'genderDistribution',
            'staffOnLeaveToday',
            'recentHires',
            'upcomingBirthdays',
            'documentsExpiringSoon',
            'probationEndingSoon'
        ));
    }
}
