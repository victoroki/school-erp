@extends('layouts.app')

@section('content')
<style>
/* ── Dashboard Design System ─────────────────────────────────────────── */
:root {
    --blue:    #2563eb;
    --green:   #16a34a;
    --yellow:  #d97706;
    --red:     #dc2626;
    --surface: #ffffff;
    --bg:      #f1f5f9;
    --text:    #0f172a;
    --muted:   #64748b;
    --border:  #e2e8f0;
    --radius:  14px;
    --shadow:  0 1px 3px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.04);
}

/* ── Layout ── */
.dash-wrap        { padding: 1.75rem 1.75rem 2rem; }
.dash-heading     { font-size: 1.5rem; font-weight: 800; color: var(--text); letter-spacing: -.02em; margin: 0; }
.dash-sub         { color: var(--muted); font-size: .875rem; margin-top: .2rem; }
.dash-row         { display: flex; gap: .75rem; flex-wrap: wrap; }

/* ── Buttons ── */
.btn-dash         { display: inline-flex; align-items: center; gap: .4rem; border: none; border-radius: 8px; padding: .5rem 1.1rem; font-size: .85rem; font-weight: 600; cursor: pointer; transition: filter .15s, transform .15s; text-decoration: none !important; white-space: nowrap; }
.btn-dash:hover   { filter: brightness(.92); transform: translateY(-1px); }
.btn-ghost        { background: #f1f5f9; color: #475569; }
.btn-primary-dash { background: var(--blue); color: #fff; box-shadow: 0 4px 12px rgba(37,99,235,.25); }

/* ── Alert banners ── */
.dash-alert       { display: flex; align-items: center; gap: 1rem; border-radius: var(--radius); padding: 1rem 1.25rem; border: 1px solid transparent; margin-bottom: .75rem; }
.da-icon          { width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1rem; }
.da-body          { flex: 1; }
.da-title         { font-weight: 700; font-size: .9rem; margin: 0 0 .15rem; }
.da-desc          { font-size: .8rem; margin: 0; opacity: .85; }
.da-action        { font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; white-space: nowrap; border-radius: 6px; padding: .35rem .75rem; text-decoration: none !important; border: 1px solid transparent; }

.alert-danger  { background: #fff1f2; border-color: #ffe4e6; }
.alert-danger .da-icon   { background: #ffe4e6; color: var(--red); }
.alert-danger .da-title  { color: #be123c; }
.alert-danger .da-desc   { color: #9f1239; }
.alert-danger .da-action { color: var(--red); border-color: #fecdd3; background: #fff; }
.alert-danger .da-action:hover { background: #ffe4e6; }

.alert-warning { background: #fffbeb; border-color: #fef3c7; }
.alert-warning .da-icon  { background: #fef3c7; color: var(--yellow); }
.alert-warning .da-title { color: #b45309; }
.alert-warning .da-desc  { color: #92400e; }
.alert-warning .da-action { color: var(--yellow); border-color: #fde68a; background: #fff; }
.alert-warning .da-action:hover { background: #fef3c7; }

.alert-info    { background: #eff6ff; border-color: #dbeafe; }
.alert-info .da-icon   { background: #dbeafe; color: var(--blue); }
.alert-info .da-title  { color: #1d4ed8; }
.alert-info .da-desc   { color: #1e40af; }
.alert-info .da-action { color: var(--blue); border-color: #bfdbfe; background: #fff; }
.alert-info .da-action:hover { background: #dbeafe; }

/* ── Stat cards ── */
.stat-grid        { display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: .875rem; }
.stat-card        { background: var(--surface); border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow); padding: 1.35rem 1.5rem; cursor: pointer; transition: transform .15s, box-shadow .15s; }
.stat-card:hover  { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,.09); }
.stat-card-top    { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem; }
.stat-icon        { width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }
.ic-blue   { background: #dbeafe; color: var(--blue); }
.ic-green  { background: #dcfce7; color: var(--green); }
.ic-yellow { background: #fef9c3; color: var(--yellow); }
.ic-red    { background: #fee2e2; color: var(--red); }
.ic-purple { background: #f3e8ff; color: #7c3aed; }
.ic-slate  { background: #f1f5f9; color: #475569; }

.stat-badge       { font-size: .72rem; font-weight: 700; padding: .25rem .55rem; border-radius: 20px; display: inline-flex; align-items: center; gap: .2rem; }
.sb-up    { background: #dcfce7; color: var(--green); }
.sb-down  { background: #fee2e2; color: var(--red); }
.sb-none  { background: #f1f5f9; color: var(--muted); }

.stat-title { font-size: .8rem; color: var(--muted); font-weight: 500; margin-bottom: .2rem; }
.stat-val   { font-size: 1.9rem; font-weight: 800; color: var(--text); line-height: 1.15; }
.stat-foot  { font-size: .75rem; color: var(--muted); margin-top: .35rem; }

/* ── Panel ── */
.dash-panel         { background: var(--surface); border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow); overflow: hidden; height: 100%; border-top: 4px solid var(--blue); } /* added colored border for modern look */
.dash-panel.panel-green { border-top-color: var(--green); }
.dash-panel-head    { display: flex; justify-content: space-between; align-items: center; padding: 1.1rem 1.4rem; border-bottom: 1px solid var(--border); }
.dash-panel-title   { font-size: 1rem; font-weight: 700; color: var(--text); margin: 0; }
.dash-panel-sub     { font-size: .78rem; color: var(--muted); margin: .15rem 0 0; }
.dash-panel-body    { padding: 1.25rem 1.4rem; height: calc(100% - 65px); }

/* ── Chart toggle buttons ── */
.tab-group          { display: flex; gap: .3rem; }
.tab-btn            { border: 1px solid var(--border); background: var(--bg); color: var(--muted); border-radius: 6px; padding: .3rem .75rem; font-size: .78rem; font-weight: 600; cursor: pointer; transition: all .15s; }
.tab-btn.active, .tab-btn:hover { background: var(--blue); color: #fff; border-color: var(--blue); }

/* ── Activity feed ── */
.activity-list      { list-style: none; margin: 0; padding: 0; }
.activity-item      { display: flex; gap: .875rem; padding: .85rem 0; border-bottom: 1px solid #f1f5f9; position: relative; }
.activity-item:last-child { border-bottom: none; }
.ai-dot             { width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: .8rem; flex-shrink: 0; margin-top: .1rem; }
.ai-body            { flex: 1; min-width: 0; }
.ai-title           { font-weight: 600; font-size: .875rem; color: var(--text); margin: 0 0 .1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ai-meta            { display: flex; gap: .5rem; align-items: center; flex-wrap: wrap; }
.ai-badge           { font-size: .69rem; font-weight: 600; padding: .15rem .45rem; border-radius: 4px; background: #f1f5f9; color: var(--muted); }
.ai-time            { font-size: .72rem; color: var(--muted); }

/* ── Quick actions ── */
.qa-grid            { display: grid; grid-template-columns: 1fr 1fr; gap: .625rem; }
.qa-btn             { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .45rem; padding: 1.1rem .5rem; background: #f8fafc; border: 1px solid var(--border); border-radius: 10px; cursor: pointer; text-decoration: none !important; color: var(--text); transition: all .15s; }
.qa-btn:hover       { background: #eff6ff; border-color: #bfdbfe; transform: translateY(-2px); color: var(--blue); }
.qa-btn i           { font-size: 1.3rem; color: var(--blue); margin-bottom: 4px; }
.qa-label           { font-size: .78rem; font-weight: 600; text-align: center; margin: 0; }

/* ── Section headers ── */
.section-label      { font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--muted); margin: 1.5rem 0 .625rem; }

/* ── Responsive grid ── */
.two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.main-aside { display: grid; grid-template-columns: 1fr 360px; gap: 1.5rem; align-items: start; }

@media (max-width: 1100px) {
    .main-aside { grid-template-columns: 1fr; }
}
@media (max-width: 768px) {
    .two-col { grid-template-columns: 1fr; }
    .stat-grid { grid-template-columns: 1fr 1fr; }
    .dash-wrap { padding: 1rem; }
}
@media (max-width: 480px) {
    .stat-grid { grid-template-columns: 1fr; }
}
</style>

<div class="dash-wrap">

    {{-- ① HEADER ────────────────────────────────────────────────────── --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            @if(in_array($roleName, ['finance','accountant','bursar']))
                <h1 class="dash-heading">Finance Dashboard</h1>
                <p class="dash-sub">Fee & revenue overview — {{ now()->format('F Y') }}</p>
            @elseif(in_array($roleName, ['hr','human resources','hr manager']))
                <h1 class="dash-heading">HR Dashboard</h1>
                <p class="dash-sub">Staff management &amp; leave overview</p>
            @else
                <h1 class="dash-heading">Good {{ now()->hour < 12 ? 'Morning' : (now()->hour < 17 ? 'Afternoon' : 'Evening') }}, {{ explode(' ', $user->name)[0] }}</h1>
                <p class="dash-sub">{{ now()->format('l, d F Y') }} &mdash; here is your system overview.</p>
            @endif
        </div>
        <div class="dash-row">
            @if(in_array($roleName, ['finance','accountant','bursar']))
                <a href="{{ route('financial-reports.index') }}" class="btn-dash btn-ghost"><i class="fas fa-file-alt"></i> Reports</a>
                <a href="{{ route('fee-management.index') }}" class="btn-dash btn-primary-dash"><i class="fas fa-money-check-alt"></i> Collect Fees</a>
            @elseif(in_array($roleName, ['hr','human resources','hr manager']))
                <a href="{{ route('leave-applications.index') }}" class="btn-dash btn-ghost"><i class="fas fa-calendar-alt"></i> Leave Requests</a>
                <a href="{{ route('hr.onboarding') }}" class="btn-dash btn-primary-dash"><i class="fas fa-user-plus"></i> Onboard Staff</a>
            @else
                <a href="{{ route('students.create') }}" class="btn-dash btn-ghost"><i class="fas fa-user-graduate"></i> Admit Student</a>
                <a href="{{ route('school-classes.index') }}" class="btn-dash btn-ghost"><i class="fas fa-chalkboard"></i> Classes</a>
                <a href="{{ route('fee-management.index') }}" class="btn-dash btn-primary-dash"><i class="fas fa-coins"></i> Fees</a>
            @endif
        </div>
    </div>

    {{-- ② SMART ALERTS ────────────────────────────────────────────── --}}
    @if($pendingFeeCount > 0)
    <div class="dash-alert alert-danger">
        <div class="da-icon"><i class="fas fa-file-invoice-dollar"></i></div>
        <div class="da-body">
            <p class="da-title">{{ number_format($pendingFeeCount) }} Accounts with Outstanding Fees</p>
            <p class="da-desc">Total unpaid: <strong>KES {{ number_format($pendingFeeAmount, 2) }}</strong></p>
        </div>
        <a href="{{ route('fee-management.index') }}" class="da-action">View List</a>
    </div>
    @endif

    @if($pendingLeaveCount > 0 && !in_array($roleName, ['teacher','educator']))
    <div class="dash-alert alert-warning">
        <div class="da-icon"><i class="fas fa-user-clock"></i></div>
        <div class="da-body">
            <p class="da-title">{{ $pendingLeaveCount }} Pending Leave {{ Str::plural('Application', $pendingLeaveCount) }}</p>
            <p class="da-desc">Awaiting review and approval</p>
        </div>
        <a href="{{ route('leave-applications.index') }}" class="da-action">Review</a>
    </div>
    @endif

    {{-- ③ STAT WIDGETS (role-based) ──────────────────────────────── --}}
    <p class="section-label">Key Metrics</p>
    <div class="stat-grid mb-4">

        @if(in_array($roleName, ['finance','accountant','bursar']))

            <div class="stat-card" onclick="window.location='{{ route('fee-management.index') }}'">
                <div class="stat-card-top">
                    <div class="stat-icon ic-green"><i class="fas fa-money-bill-wave"></i></div>
                    <span class="stat-badge sb-none">Today</span>
                </div>
                <p class="stat-title">Today's Collections</p>
                <div class="stat-val">KES {{ number_format($stats['todayRevenue'], 2) }}</div>
                <p class="stat-foot">Fee payments received today</p>
            </div>

            <div class="stat-card" onclick="window.location='{{ route('fee-management.index') }}'">
                <div class="stat-card-top">
                    <div class="stat-icon ic-blue"><i class="fas fa-calendar-check"></i></div>
                    <span class="stat-badge sb-none">{{ now()->format('M') }}</span>
                </div>
                <p class="stat-title">Monthly Revenue</p>
                <div class="stat-val">KES {{ number_format($stats['monthRevenue'], 2) }}</div>
                <p class="stat-foot">Total collected this month</p>
            </div>

            <div class="stat-card" onclick="window.location='{{ route('fee-management.index') }}'">
                <div class="stat-card-top">
                    <div class="stat-icon ic-red"><i class="fas fa-exclamation-circle"></i></div>
                    <span class="stat-badge sb-down"><i class="fas fa-arrow-up"></i> Urgent</span>
                </div>
                <p class="stat-title">Outstanding Fees</p>
                <div class="stat-val">KES {{ number_format($stats['pendingFees'], 2) }}</div>
                <p class="stat-foot">Across {{ number_format($stats['pendingAccounts']) }} accounts</p>
            </div>

        @elseif(in_array($roleName, ['hr','human resources','hr manager']))

            <div class="stat-card" onclick="window.location='{{ route('staff.index') }}'">
                <div class="stat-card-top">
                    <div class="stat-icon ic-blue"><i class="fas fa-users"></i></div>
                    <span class="stat-badge sb-none">Active</span>
                </div>
                <p class="stat-title">Total Staff</p>
                <div class="stat-val">{{ number_format($stats['totalStaff']) }}</div>
                <p class="stat-foot">All roles combined</p>
            </div>

            <div class="stat-card" onclick="window.location='{{ route('leave-applications.index') }}'">
                <div class="stat-card-top">
                    <div class="stat-icon ic-yellow"><i class="fas fa-user-clock"></i></div>
                    @if($stats['pendingLeaves'] > 0)
                        <span class="stat-badge sb-down">Action needed</span>
                    @else
                        <span class="stat-badge sb-up">Clear</span>
                    @endif
                </div>
                <p class="stat-title">Pending Leave Requests</p>
                <div class="stat-val">{{ $stats['pendingLeaves'] }}</div>
                <p class="stat-foot">Awaiting your approval</p>
            </div>

        @else

            {{-- Admin / default ─────────────────────────────────────── --}}
            <div class="stat-card" onclick="window.location='{{ route('students.index') }}'">
                <div class="stat-card-top">
                    <div class="stat-icon ic-blue"><i class="fas fa-user-graduate"></i></div>
                    <span class="stat-badge sb-up"><i class="fas fa-check"></i> {{ number_format($stats['activeStudents']) }} Active</span>
                </div>
                <p class="stat-title">Total Students</p>
                <div class="stat-val">{{ number_format($stats['totalStudents']) }}</div>
                <p class="stat-foot">Enrolled &amp; registered</p>
            </div>

            <div class="stat-card" onclick="window.location='{{ route('staff.index') }}'">
                <div class="stat-card-top">
                    <div class="stat-icon ic-purple"><i class="fas fa-users"></i></div>
                    <span class="stat-badge sb-none">Operational</span>
                </div>
                <p class="stat-title">Active Staff</p>
                <div class="stat-val">{{ number_format($stats['totalStaff']) }}</div>
                <p class="stat-foot">Academic &amp; support personnel</p>
            </div>

            <div class="stat-card" onclick="window.location='{{ route('school-classes.index') }}'">
                <div class="stat-card-top">
                    <div class="stat-icon ic-green"><i class="fas fa-chalkboard"></i></div>
                    <span class="stat-badge sb-none">Classes</span>
                </div>
                <p class="stat-title">Total Classes</p>
                <div class="stat-val">{{ number_format($stats['totalClasses']) }}</div>
                <p class="stat-foot">Academic class groups</p>
            </div>

            <div class="stat-card" onclick="window.location='{{ route('fee-management.index') }}'">
                <div class="stat-card-top">
                    <div class="stat-icon ic-yellow"><i class="fas fa-coins"></i></div>
                    @if($stats['pendingFees'] > 0)
                        <span class="stat-badge sb-down">Pending KES {{ number_format($stats['pendingFees'], 0) }}</span>
                    @else
                        <span class="stat-badge sb-up">All Clear</span>
                    @endif
                </div>
                <p class="stat-title">Monthly Revenue</p>
                <div class="stat-val">KES {{ number_format($stats['monthRevenue'], 2) }}</div>
                <p class="stat-foot">Collected this month</p>
            </div>

        @endif
    </div>

    {{-- ④ CHARTS + SIDEBAR ────────────────────────────────────────── --}}
    <div class="main-aside">

        {{-- Left: Charts ─────────────────────────────────────────────── --}}
        <div>
            {{-- Enrollment trend ──────────────────────────── --}}
            <div class="dash-panel mb-3">
                <div class="dash-panel-head">
                    <div>
                        <h3 class="dash-panel-title">Student Enrollment Trend</h3>
                        <p class="dash-panel-sub">New admissions over the last 12 months</p>
                    </div>
                </div>
                <div class="dash-panel-body" style="height:240px; position:relative;">
                    <canvas id="chartEnrollment"></canvas>
                </div>
            </div>

            {{-- Fee collection trend ─────────────────────── --}}
            <div class="dash-panel">
                <div class="dash-panel-head">
                    <div>
                        <h3 class="dash-panel-title">Fee Collection Trend</h3>
                        <p class="dash-panel-sub">Monthly revenue over the last 6 months</p>
                    </div>
                </div>
                <div class="dash-panel-body" style="height:220px; position:relative;">
                    <canvas id="chartFees"></canvas>
                </div>
            </div>
        </div>

        {{-- Right: Quick actions + Activity ─────────────────────────── --}}
        <div>

            {{-- Quick Actions ─────────────────────────────── --}}
            <div class="dash-panel mb-3">
                <div class="dash-panel-head">
                    <h3 class="dash-panel-title">Quick Actions</h3>
                </div>
                <div class="dash-panel-body">
                    <div class="qa-grid">
                        @if(in_array($roleName, ['finance','accountant','bursar']))
                            <a href="{{ route('fee-management.index') }}" class="qa-btn"><i class="fas fa-cash-register"></i><span class="qa-label">Collect Fee</span></a>
                            <a href="{{ route('expenses.create') }}" class="qa-btn"><i class="fas fa-receipt"></i><span class="qa-label">Record Expense</span></a>
                            <a href="{{ route('financial-reports.index') }}" class="qa-btn"><i class="fas fa-file-invoice"></i><span class="qa-label">Reports</span></a>
                            <a href="{{ route('audit-trail.index') }}" class="qa-btn"><i class="fas fa-history"></i><span class="qa-label">Audit Trail</span></a>
                        @elseif(in_array($roleName, ['hr','human resources','hr manager']))
                            <a href="{{ route('hr.onboarding') }}" class="qa-btn"><i class="fas fa-user-plus"></i><span class="qa-label">Onboard Staff</span></a>
                            <a href="{{ route('leave-applications.index') }}" class="qa-btn"><i class="fas fa-calendar-check"></i><span class="qa-label">Leave Apps</span></a>
                            <a href="{{ route('staff.index') }}" class="qa-btn"><i class="fas fa-users"></i><span class="qa-label">All Staff</span></a>
                            <a href="{{ route('payroll-processing.index') }}" class="qa-btn"><i class="fas fa-money-check-alt"></i><span class="qa-label">Payroll</span></a>
                        @else
                            <a href="{{ route('students.create') }}" class="qa-btn"><i class="fas fa-user-graduate"></i><span class="qa-label">Admit Student</span></a>
                            <a href="{{ route('student-attendance.index') }}" class="qa-btn"><i class="fas fa-clipboard-check"></i><span class="qa-label">Attendance</span></a>
                            <a href="{{ route('fee-management.index') }}" class="qa-btn"><i class="fas fa-coins"></i><span class="qa-label">Fees</span></a>
                            <a href="{{ route('exam-reports.generate') }}" class="qa-btn"><i class="fas fa-file-pdf"></i><span class="qa-label">Report Cards</span></a>
                            <a href="{{ route('exams.index') }}" class="qa-btn"><i class="fas fa-pen-alt"></i><span class="qa-label">Exams</span></a>
                            <a href="{{ route('timetables.index') }}" class="qa-btn"><i class="fas fa-calendar-alt"></i><span class="qa-label">Timetables</span></a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Recent Activity ────────────────────────────── --}}
            <div class="dash-panel">
                <div class="dash-panel-head">
                    <h3 class="dash-panel-title">Recent Activity</h3>
                    <a href="{{ route('audit-trail.index') }}" style="font-size:.8rem;font-weight:600;color:var(--blue);">View all</a>
                </div>
                <div class="dash-panel-body" style="padding-top:.5rem;">
                    @if($recentActivity->isEmpty())
                        <p style="color:var(--muted);font-size:.85rem;text-align:center;padding:1.5rem 0;">No recent activity recorded yet.</p>
                    @else
                    <ul class="activity-list">
                        @foreach($recentActivity as $act)
                        <li class="activity-item">
                            <div class="ai-dot ic-blue"><i class="fas fa-circle" style="font-size:.4rem;"></i></div>
                            <div class="ai-body">
                                <p class="ai-title">{{ $act['action'] }} — {{ $act['module'] }}</p>
                                <div class="ai-meta">
                                    <span class="ai-badge">{{ $act['user'] }}</span>
                                    <span class="ai-time">{{ $act['time'] }}</span>
                                </div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </div>
            </div>

        </div>
    </div>{{-- /.main-aside --}}

</div>{{-- /.dash-wrap --}}

<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
(function () {
    const BLUE   = '#2563eb';
    const GREEN  = '#16a34a';
    const border = '#e2e8f0';

    const defaultOpts = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#94a3b8' } },
            y: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 11 }, color: '#94a3b8' }, beginAtZero: true }
        }
    };

    // ── Enrollment Chart ─────────────────────────────────────────────
    const enrolLabels = @json($enrollmentTrend['labels']);
    const enrolData   = @json($enrollmentTrend['data']);

    new Chart(document.getElementById('chartEnrollment'), {
        type: 'bar',
        data: {
            labels: enrolLabels,
            datasets: [{
                label: 'New Students',
                data: enrolData,
                backgroundColor: 'rgba(37,99,235,.15)',
                borderColor: BLUE,
                borderWidth: 2,
                borderRadius: 6,
            }]
        },
        options: { ...defaultOpts }
    });

    // ── Fee Collection Chart ─────────────────────────────────────────
    const feeLabels = @json($feeTrend['labels']);
    const feeData   = @json($feeTrend['data']);

    new Chart(document.getElementById('chartFees'), {
        type: 'line',
        data: {
            labels: feeLabels,
            datasets: [{
                label: 'Revenue (KES)',
                data: feeData,
                borderColor: GREEN,
                backgroundColor: 'rgba(22,163,74,.08)',
                borderWidth: 2.5,
                fill: true,
                tension: 0.42,
                pointBackgroundColor: GREEN,
                pointRadius: 4,
            }]
        },
        options: {
            ...defaultOpts,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ' KES ' + Number(ctx.parsed.y).toLocaleString()
                    }
                }
            }
        }
    });
})();
</script>
@endsection