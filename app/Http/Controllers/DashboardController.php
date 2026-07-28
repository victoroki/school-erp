<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\FeePayment;
use App\Models\StudentFeeAssignment;
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
use App\Services\MenuService;
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

        $isSuperAdmin = $user->hasAnyRole(['Super Admin']);

        $widgetConfig = config('dashboard_widgets.modules', []);
        $alertConfig  = config('dashboard_widgets.alerts', []);

        $visibleModules = [];
        foreach ($widgetConfig as $key => $widget) {
            if (MenuService::canSee($user, $widget['permission'])) {
                $visibleModules[$key] = $widget;
                $visibleModules[$key]['data'] = ($widget['summary'])();
            }
        }

        $counts = $this->getAlertCounts();

        $visibleAlerts = [];
        foreach ($alertConfig as $alert) {
            if (!MenuService::canSee($user, $alert['permission'])) {
                continue;
            }
            if (!($alert['visible'])($counts)) {
                continue;
            }
            $visibleAlerts[] = [
                'type'   => $alert['type'],
                'icon'   => $alert['icon'],
                'title'  => ($alert['title'])($counts),
                'desc'   => ($alert['desc'])($counts),
                'action' => $alert['action'],
                'actionRoute' => $alert['actionRoute'],
            ];
        }

        $recentActivity = [];
        if ($isSuperAdmin || $user->hasAnyRole(['Admin'])) {
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
        }

        $keyMetrics = $this->getKeyMetrics($user, $roleName);
        $chartSeries = $this->getChartSeries($user);

        return view('dashboard', compact(
            'user', 'roleName',
            'keyMetrics', 'chartSeries',
            'recentActivity',
            'visibleModules', 'visibleAlerts'
        ));
    }

    public function getData(Request $request)
    {
        $user = Auth::user();

        // Each statistic is tagged with the permission that governs it and
        // only included when the user holds it — never leak global numbers
        // to roles without module access.
        $statistics = [];
        $charts = [];

        if (MenuService::canSee($user, ['students.view'])) {
            $statistics['total_students'] = Student::count();

            $months = [];
            $counts = [];
            for ($i = 11; $i >= 0; $i--) {
                $month    = Carbon::today()->subMonths($i);
                $months[] = $month->format('M');
                $counts[] = Student::whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count();
            }
            $charts['enrollment_trend'] = ['labels' => $months, 'data' => $counts];
        }

        if (MenuService::canSee($user, ['hr.view'])) {
            $statistics['total_teachers'] = User::whereHas('roles', fn($q) => $q->where('role_name', 'teacher'))->count();
        }

        if (MenuService::canSee($user, ['academics.view'])) {
            $statistics['total_classes'] = SchoolClass::count();
        }

        if (MenuService::canSee($user, ['fees.view', 'fees.collect', 'finance.view'])) {
            $statistics['monthly_revenue'] = FeePayment::whereMonth('created_at', now()->month)->sum('amount');
        }

        return response()->json([
            'statistics' => $statistics,
            'charts'     => $charts,
        ]);
    }

    private function getAlertCounts(): array
    {
        $pendingFeeQuery = StudentFeeAssignment::where('status', 'active')
            ->whereRaw('COALESCE(paid_amount, 0) < final_amount');

        return [
            'pendingFeeCount'          => $pendingFeeQuery->count(),
            'pendingFeeAmount'         => $pendingFeeQuery->get()->sum(fn($fee) => $fee->balance),
            'pendingLeaveCount'        => LeaveApplication::where('application_status', 'pending')->count(),
            'pendingRequisitionsCount' => Requisition::where('status', 'Pending')->count(),
            'lowStockCount'            => InventoryItem::whereRaw('quantity <= minimum_quantity')->count(),
        ];
    }

    /**
     * Build the "Key Metrics" summary cards as permission-tagged widget
     * definitions, then filter them server-side before the view sees them.
     *
     * Role-specific variants (finance / HR) keep their tailored cards; the
     * default set shows one card per governed module. Any card whose
     * permission the user lacks is dropped — nothing is hidden via CSS/JS,
     * and card values are evaluated only after the permission check passes.
     */
    private function getKeyMetrics(User $user, string $roleName): array
    {
        $isFinance = in_array($roleName, ['finance', 'accountant', 'bursar']);
        $isHR      = in_array($roleName, ['hr', 'human resources', 'hr manager']);

        if ($isFinance && MenuService::canSee($user, ['fees.view', 'fees.collect'])) {
            $cards = [
                [
                    'permission' => ['fees.view', 'fees.collect'],
                    'col' => 'col-md-4', 'route' => 'fee-management.index',
                    'icon' => 'fa-money-bill-wave', 'color' => 'ic-green',
                    'badge' => 'Today', 'badgeClass' => 'sb-none',
                    'title' => 'Daily Collections',
                    'value' => fn() => 'KES ' . number_format(FeePayment::whereDate('created_at', today())->sum('amount'), 0),
                    'foot'  => 'Payments received today',
                ],
                [
                    'permission' => ['fees.view', 'fees.collect'],
                    'col' => 'col-md-4', 'route' => 'fee-management.index',
                    'icon' => 'fa-calendar-check', 'color' => 'ic-blue',
                    'badge' => now()->format('M'), 'badgeClass' => 'sb-none',
                    'title' => 'Monthly Revenue',
                    'value' => fn() => 'KES ' . number_format(FeePayment::whereMonth('created_at', now()->month)->sum('amount'), 0),
                    'foot'  => 'Total collected this month',
                ],
                [
                    'permission' => ['fees.view', 'fees.collect'],
                    'col' => 'col-md-4', 'route' => 'fee-management.index',
                    'icon' => 'fa-exclamation-circle', 'color' => 'ic-red',
                    'badge' => 'Urgent', 'badgeClass' => 'sb-down',
                    'title' => 'Outstanding Fees',
                    'value' => fn() => 'KES ' . number_format(
                        StudentFeeAssignment::where('status', 'active')
                            ->whereRaw('COALESCE(paid_amount, 0) < final_amount')
                            ->get()->sum(fn($fee) => $fee->balance),
                        0
                    ),
                    'foot' => fn() => 'Across ' . number_format(
                        StudentFeeAssignment::where('status', 'active')
                            ->whereRaw('COALESCE(paid_amount, 0) < final_amount')
                            ->count()
                    ) . ' accounts',
                ],
            ];
        } elseif ($isHR && MenuService::canSee($user, ['hr.view'])) {
            $cards = [
                [
                    'permission' => ['hr.view'],
                    'col' => 'col-md-6', 'route' => 'staff.index',
                    'icon' => 'fa-users', 'color' => 'ic-blue',
                    'badge' => 'Active', 'badgeClass' => 'sb-none',
                    'title' => 'Total Staff',
                    'value' => fn() => number_format(User::whereHas('roles')->count()),
                    'foot'  => 'Academic & support combined',
                ],
                [
                    'permission' => ['hr.view'],
                    'col' => 'col-md-6', 'route' => 'leave-applications.index',
                    'icon' => 'fa-user-clock', 'color' => 'ic-yellow',
                    'badge' => fn() => LeaveApplication::where('application_status', 'pending')->count() > 0 ? 'Pending' : 'Clear',
                    'badgeClass' => fn() => LeaveApplication::where('application_status', 'pending')->count() > 0 ? 'sb-down' : 'sb-up',
                    'title' => 'Leave Requests',
                    'value' => fn() => LeaveApplication::where('application_status', 'pending')->count(),
                    'foot'  => 'Awaiting your review',
                ],
            ];
        } else {
            $cards = [
                [
                    'permission' => ['students.view'],
                    'col' => 'col-xl-3 col-sm-6', 'route' => 'students.index',
                    'icon' => 'fa-user-graduate', 'color' => 'ic-blue',
                    'badge' => fn() => number_format(Student::where('is_active', true)->count()),
                    'badgeIcon' => 'fa-check', 'badgeClass' => 'sb-up',
                    'title' => 'Total Students',
                    'value' => fn() => number_format(Student::count()),
                    'foot'  => 'Enrolled students',
                ],
                [
                    'permission' => ['hr.view'],
                    'col' => 'col-xl-3 col-sm-6', 'route' => 'staff.index',
                    'icon' => 'fa-users', 'color' => 'ic-purple',
                    'badge' => 'Active', 'badgeClass' => 'sb-none',
                    'title' => 'Active Staff',
                    'value' => fn() => number_format(User::whereHas('roles')->count()),
                    'foot'  => 'Academic & support',
                ],
                [
                    'permission' => ['academics.view'],
                    'col' => 'col-xl-3 col-sm-6', 'route' => 'school-classes.index',
                    'icon' => 'fa-chalkboard', 'color' => 'ic-green',
                    'badge' => 'Units', 'badgeClass' => 'sb-none',
                    'title' => 'Total Classes',
                    'value' => fn() => number_format(SchoolClass::count()),
                    'foot'  => 'Academic groups',
                ],
                [
                    'permission' => ['fees.view', 'fees.collect'],
                    'col' => 'col-xl-3 col-sm-6', 'route' => 'fee-management.index',
                    'icon' => 'fa-coins', 'color' => 'ic-yellow',
                    'badge' => now()->format('M'), 'badgeClass' => 'sb-none',
                    'title' => 'Monthly Rev',
                    'value' => fn() => 'KES ' . number_format(FeePayment::whereMonth('created_at', now()->month)->sum('amount') / 1000, 1) . 'k',
                    'foot'  => fn() => 'Pending: ' . number_format(
                        StudentFeeAssignment::where('status', 'active')
                            ->whereRaw('COALESCE(paid_amount, 0) < final_amount')
                            ->get()->sum(fn($fee) => $fee->balance) / 1000,
                        1
                    ) . 'k',
                ],
            ];
        }

        // Filter by permission BEFORE evaluating any card data.
        return collect($cards)
            ->filter(fn($card) => MenuService::canSee($user, $card['permission']))
            ->map(function ($card) {
                foreach (['value', 'badge', 'badgeClass', 'foot'] as $key) {
                    if (($card[$key] ?? null) instanceof \Closure) {
                        $card[$key] = ($card[$key])();
                    }
                }
                unset($card['permission']);
                return $card;
            })
            ->values()
            ->all();
    }

    /**
     * Build the "Growth & Revenue" chart datasets, one per governed module.
     * A dataset is computed and exposed to the view only when the user holds
     * the underlying permission — the chart panel itself is hidden when no
     * dataset survives filtering.
     */
    private function getChartSeries(User $user): array
    {
        $series = [];

        if (MenuService::canSee($user, ['students.view'])) {
            $series['admissions'] = $this->getEnrollmentTrend();
        }

        if (MenuService::canSee($user, ['fees.view', 'fees.collect', 'finance.view'])) {
            $series['revenue'] = $this->getFeeTrend();
        }

        return $series;
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
