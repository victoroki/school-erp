<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\Department;
use App\Models\LeaveApplication;
use App\Models\StaffAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HRReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:hr.view');
    }

    public function headcount(Request $request)
    {
        // Staff by Department
        $byDepartment = Department::withCount(['staff' => function($q) {
            $q->where('employment_status', 'active');
        }])->get();

        // Staff by Employment Type
        $byEmploymentType = Staff::where('employment_status', 'active')
            ->select('employment_type', DB::raw('count(*) as count'))
            ->groupBy('employment_type')
            ->get();

        // Staff by Gender
        $byGender = Staff::where('employment_status', 'active')
            ->select('gender', DB::raw('count(*) as count'))
            ->groupBy('gender')
            ->get();

        // Staff by Staff Type (Teaching/Non-Teaching)
        $byStaffType = Staff::where('employment_status', 'active')
            ->select('staff_type', DB::raw('count(*) as count'))
            ->groupBy('staff_type')
            ->get();

        // Age Distribution
        $ageDistribution = Staff::where('employment_status', 'active')
            ->whereNotNull('date_of_birth')
            ->get()
            ->groupBy(function($staff) {
                $age = $staff->date_of_birth->age;
                if ($age < 25) return 'Under 25';
                if ($age < 35) return '25-34';
                if ($age < 45) return '35-44';
                if ($age < 55) return '45-54';
                return '55+';
            })
            ->map(function($group) {
                return $group->count();
            });

        return view('hr.reports.headcount', compact(
            'byDepartment',
            'byEmploymentType',
            'byGender',
            'byStaffType',
            'ageDistribution'
        ));
    }

    public function payroll(Request $request)
    {
        $month = $request->input('month', date('n'));
        $year = $request->input('year', date('Y'));

        // Payroll summary by department
        $byDepartment = Department::with(['staff' => function($q) {
            $q->where('employment_status', 'active');
        }])->get()->map(function($dept) {
            $totalSalary = $dept->staff->sum('basic_salary');
            return [
                'department' => $dept->name,
                'staff_count' => $dept->staff->count(),
                'total_salary' => $totalSalary,
                'average_salary' => $dept->staff->count() > 0 ? $totalSalary / $dept->staff->count() : 0,
            ];
        });

        // Total payroll cost
        $totalPayrollCost = Staff::where('employment_status', 'active')->sum('basic_salary');

        return view('hr.reports.payroll', compact('byDepartment', 'totalPayrollCost', 'month', 'year'));
    }

    public function leave(Request $request)
    {
        $year = $request->input('year', date('Y'));

        // Leave applications by type
        $byLeaveType = LeaveApplication::whereYear('start_date', $year)
            ->with('leaveType')
            ->get()
            ->groupBy('leave_type_id')
            ->map(function($group) {
                return [
                    'leave_type' => $group->first()->leaveType->name ?? 'Unknown',
                    'total_applications' => $group->count(),
                    'approved' => $group->where('final_status', 'approved')->count(),
                    'rejected' => $group->where('final_status', 'rejected')->count(),
                    'pending' => $group->where('application_status', 'pending')->count(),
                    'total_days' => $group->where('final_status', 'approved')->sum('working_days'),
                ];
            });

        // Leave applications by month
        $byMonth = LeaveApplication::whereYear('start_date', $year)
            ->select(DB::raw('MONTH(start_date) as month'), DB::raw('count(*) as count'))
            ->groupBy('month')
            ->get()
            ->pluck('count', 'month');

        return view('hr.reports.leave', compact('byLeaveType', 'byMonth', 'year'));
    }

    public function attendance(Request $request)
    {
        $month = $request->input('month', date('n'));
        $year = $request->input('year', date('Y'));

        // Attendance summary
        $summary = StaffAttendance::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        // Daily attendance trend
        $dailyTrend = StaffAttendance::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->select(DB::raw('DAY(date) as day'), 'status', DB::raw('count(*) as count'))
            ->groupBy('day', 'status')
            ->get();

        return view('hr.reports.attendance', compact('summary', 'dailyTrend', 'month', 'year'));
    }
}
