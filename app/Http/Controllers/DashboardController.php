<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\FeePayment;
use App\Models\StudentFee;
use App\Models\AuditTrail;
use App\Models\LeaveApplication;
use App\Models\InventoryItem;
use App\Models\Requisition;
use App\Models\Book;
use App\Models\BookIssue;
use App\Models\Hostel;
use App\Models\HostelAllocation;
use App\Models\Route as TransportRoute;
use App\Models\Vehicle;
use App\Models\Exam;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $user->load('roles');
        $roleName = strtolower($user->roles->first()->role_name ?? 'administrator');

        // Real stats filtered by role
        $stats = $this->getStats($roleName);

        // Enrollment chart — 12 months
        $enrollmentTrend = $this->getEnrollmentTrend();

        // Fee collection chart — 6 months
        $feeTrend = $this->getFeeTrend();

        // Real recent activity from audit_trails table
        $recentActivity = AuditTrail::with('user')
            ->latest()
            ->limit(6)
            ->get()
            ->map(fn($a) => [
                'module' => ucfirst($a->module ?? 'System'),
                'action' => ucfirst($a->action ?? 'Action'),
                'user'   => optional($a->user)->name ?? 'System',
                'time'   => $a->created_at->diffForHumans(),
            ]);

        // Alert counts from real data
        $pendingFeeQuery   = StudentFee::whereIn('status', ['unpaid', 'partially_paid']);
        $pendingFeeCount   = $pendingFeeQuery->count();
        $pendingFeeAmount  = $pendingFeeQuery->get()->sum(fn($fee) => $fee->balance);
        $pendingLeaveCount = LeaveApplication::where('application_status', 'pending')->count();
        
        // New Alerts
        $pendingRequisitionsCount = Requisition::where('status', 'Pending')->count();
        $lowStockCount = InventoryItem::whereRaw('quantity <= minimum_quantity')->count();
        
        // Module Health Data
        $moduleHealth = [
            'academic' => [
                'status' => SchoolClass::count() > 0 ? 'good' : 'warning',
                'count' => SchoolClass::count(),
                'label' => 'Classes Active'
            ],
            'students' => [
                'status' => Student::where('is_active', true)->count() > 0 ? 'good' : 'danger',
                'count' => Student::where('is_active', true)->count(),
                'label' => 'Active Students'
            ],
            'exams' => [
                'status' => Exam::where('end_date', '>=', now()->toDateString())->count() > 0 ? 'good' : 'info',
                'count' => Exam::where('end_date', '>=', now()->toDateString())->count(),
                'label' => 'Active Exams'
            ],
            'inventory' => [
                'status' => $lowStockCount > 0 ? 'warning' : 'good',
                'count' => $pendingRequisitionsCount,
                'label' => 'Pending Reqs'
            ],
            'library' => [
                'status' => 'good',
                'count' => BookIssue::whereNull('return_date')->where('due_date', '<', now())->count(),
                'label' => 'Overdue Books'
            ],
            'hr' => [
                'status' => $pendingLeaveCount > 0 ? 'warning' : 'good',
                'count' => User::whereHas('roles')->count(),
                'label' => 'Total Staff'
            ],
            'fees' => [
                'status' => $pendingFeeCount > 10 ? 'danger' : 'good',
                'count' => number_format(FeePayment::whereMonth('created_at', now()->month)->sum('amount') / 1000, 1) . 'k',
                'label' => 'Monthly Rev'
            ],
            'hostel' => [
                'status' => 'good',
                'count' => HostelAllocation::where('status', 'active')->count(),
                'label' => 'Occupants'
            ],
            'transport' => [
                'status' => 'good',
                'count' => Vehicle::where('status', 'active')->count(),
                'label' => 'Active Vehicles'
            ]
        ];

        return view('dashboard', compact(
            'user', 'roleName', 'stats',
            'enrollmentTrend', 'feeTrend',
            'recentActivity',
            'pendingFeeCount', 'pendingFeeAmount', 'pendingLeaveCount',
            'pendingRequisitionsCount', 'lowStockCount',
            'moduleHealth'
        ));
    }

    // Legacy AJAX endpoint — kept for compatibility
    public function getData(Request $request)
    {
        $months = [];
        $counts = [];
        for ($i = 11; $i >= 0; $i--) {
            $month    = Carbon::today()->subMonths($i);
            $months[] = $month->format('M');
            $counts[] = Student::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        }

        return response()->json([
            'statistics' => [
                'total_students'  => Student::count(),
                'total_teachers'  => User::whereHas('roles', fn($q) => $q->where('role_name', 'teacher'))->count(),
                'total_classes'   => SchoolClass::count(),
                'monthly_revenue' => FeePayment::whereMonth('created_at', now()->month)->sum('amount'),
            ],
            'charts' => [
                'enrollment_trend' => ['labels' => $months, 'data' => $counts],
            ],
        ]);
    }

    private function getStats(string $role): array
    {
        $isFinance = in_array($role, ['finance', 'accountant', 'bursar']);
        $isHR      = in_array($role, ['hr', 'human resources', 'hr manager']);

        if ($isFinance) {
            $pendingQuery = StudentFee::whereIn('status', ['unpaid', 'partially_paid']);
            return [
                'todayRevenue'    => FeePayment::whereDate('created_at', today())->sum('amount'),
                'monthRevenue'    => FeePayment::whereMonth('created_at', now()->month)->sum('amount'),
                'pendingFees'     => $pendingQuery->get()->sum(fn($fee) => $fee->balance),
                'pendingAccounts' => $pendingQuery->count(),
            ];
        }

        if ($isHR) {
            return [
                'totalStaff'    => User::whereHas('roles')->count(),
                'pendingLeaves' => LeaveApplication::where('application_status', 'pending')->count(),
            ];
        }

        // Admin / principal / default: full overview
        $pendingQuery = StudentFee::whereIn('status', ['unpaid', 'partially_paid']);
        $monthRevenueCollected = FeePayment::whereMonth('created_at', now()->month)->sum('amount');
        $monthRevenuePending = StudentFee::whereMonth('due_date', now()->month)
            ->whereIn('status', ['unpaid', 'partially_paid'])
            ->get()
            ->sum(fn($fee) => $fee->balance);

        return [
            'totalStudents'  => Student::count(),
            'activeStudents' => Student::where('is_active', true)->count(),
            'totalStaff'     => User::whereHas('roles')->count(),
            'totalClasses'   => SchoolClass::count(),
            'monthRevenue'   => $monthRevenueCollected,
            'monthRevenuePending' => $monthRevenuePending,
            'pendingFees'    => $pendingQuery->get()->sum(fn($fee) => $fee->balance),
        ];
    }

    private function getEnrollmentTrend(): array
    {
        $labels = [];
        $data   = [];
        for ($i = 11; $i >= 0; $i--) {
            $month    = Carbon::today()->subMonths($i);
            $labels[] = $month->format('M Y');
            $data[]   = Student::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        }
        return ['labels' => $labels, 'data' => $data];
    }

    private function getFeeTrend(): array
    {
        $labels = [];
        $data   = [];
        for ($i = 5; $i >= 0; $i--) {
            $month    = Carbon::today()->subMonths($i);
            $labels[] = $month->format('M');
            $data[]   = (float) FeePayment::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('amount');
        }
        return ['labels' => $labels, 'data' => $data];
    }
}