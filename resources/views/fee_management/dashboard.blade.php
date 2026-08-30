@extends('layouts.app')

@section('content')
<style>
/* ── Design System Variables ── */
:root {
    --blue:    #2563eb;
    --green:   #16a34a;
    --yellow:  #d97706;
    --red:     #dc2626;
    --surface: #ffffff;
    --bg:      #f8fafc;
    --text:    #0f172a;
    --muted:   #64748b;
    --border:  #e2e8f0;
    --radius:  12px;
    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    --shadow-md: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
    
    /* Emil's easing curves — custom beats browser defaults */
    --ease-out:    cubic-bezier(0.23, 1, 0.32, 1);
    --ease-in-out: cubic-bezier(0.77, 0, 0.175, 1);
}

/* ── Layout ── */
.fee-dash-wrap { padding: 1.5rem; }
.fee-heading { font-size: 1.5rem; font-weight: 800; color: var(--text); letter-spacing: -.025em; margin: 0; }
.fee-sub { color: var(--muted); font-size: .875rem; margin-top: .25rem; }

/* ── Buttons ── */
.fee-btn { 
    display: inline-flex; align-items: center; gap: .5rem; border: none; border-radius: 8px; padding: .5rem 1rem; 
    font-size: .875rem; font-weight: 600; cursor: pointer; text-decoration: none !important; white-space: nowrap;
    transition: transform 150ms var(--ease-out), filter 150ms var(--ease-out), box-shadow 150ms var(--ease-out);
    outline: none;
}
.fee-btn:focus-visible { 
    box-shadow: 0 0 0 3px rgba(37,99,235,.3); 
}
@media (hover: hover) and (pointer: fine) {
    .fee-btn:hover { filter: brightness(.95); transform: translateY(-1px); }
}
.fee-btn:active { transform: scale(0.97); }
.fee-btn-ghost { background: #f1f5f9; color: #475569; }
.fee-btn-primary { background: var(--blue); color: #fff; box-shadow: 0 4px 12px rgba(37,99,235,.2); }

/* ── Alert banners ── */
.fee-alert { 
    display: flex; align-items: center; gap: 1rem; border-radius: var(--radius); padding: .75rem 1rem; 
    border: 1px solid transparent; margin-bottom: 1rem; transition: transform 200ms var(--ease-out);
}
.fee-alert-icon { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1rem; }
.fee-alert-body { flex: 1; }
.fee-alert-title { font-weight: 700; font-size: .875rem; margin: 0 0 .125rem; }
.fee-alert-desc { font-size: .75rem; margin: 0; opacity: .8; }
.fee-alert-action { font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; white-space: nowrap; border-radius: 6px; padding: .25rem .75rem; text-decoration: none !important; border: 1px solid transparent; transition: background 150ms var(--ease-out); }

.fee-alert-warning { background: #fffbeb; border-color: #fef3c7; }
.fee-alert-warning .fee-alert-icon { background: #fef3c7; color: var(--yellow); }
.fee-alert-warning .fee-alert-title { color: #92400e; }
.fee-alert-warning .fee-alert-desc { color: #a16207; }
.fee-alert-warning .fee-alert-action { color: var(--yellow); border-color: #fde68a; background: #fff; }
.fee-alert-warning .fee-alert-action:hover { background: #fef3c7; }

/* ── Summary Cards with Gradients ── */
.summary-card { 
    border-radius: var(--radius); padding: 1.5rem; color: #fff; position: relative; overflow: hidden;
    transition: transform 200ms var(--ease-out), box-shadow 200ms var(--ease-out);
    opacity: 0; transform: translateY(8px); animation: cardIn 0.4s var(--ease-out) forwards;
    outline: none; cursor: pointer;
}
.summary-card:focus-visible { 
    box-shadow: 0 0 0 3px rgba(255,255,255,.5), 0 0 0 6px rgba(37,99,235,.3); 
}
@keyframes cardIn { to { opacity: 1; transform: translateY(0); } }

@media (hover: hover) and (pointer: fine) {
    .summary-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }
}
.summary-card:active { transform: scale(0.98); }

.summary-card-bg { position: absolute; right: -15px; bottom: -15px; font-size: 6rem; opacity: 0.15; pointer-events: none; }
.summary-card-label { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; opacity: 0.85; margin-bottom: .25rem; }
.summary-card-value { font-size: 1.75rem; font-weight: 800; line-height: 1; margin-bottom: .75rem; }
.summary-card-progress { height: 4px; background: rgba(255,255,255,0.2); border-radius: 2px; overflow: hidden; margin-bottom: .75rem; }
.summary-card-progress-bar { height: 100%; background: #fff; border-radius: 2px; transition: width 1s var(--ease-out); }
.summary-card-footer { display: flex; justify-content: space-between; align-items: center; font-size: .75rem; }
.summary-card-link { color: #fff; font-weight: 700; text-decoration: none !important; transition: opacity 150ms var(--ease-out); }
@media (hover: hover) and (pointer: fine) {
    .summary-card-link:hover { opacity: 0.8; }
}

/* Gradient variants */
.grad-blue { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); }
.grad-green { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
.grad-yellow { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
.grad-red { background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%); }

/* ── Panel ── */
.fee-panel { 
    background: var(--surface); border-radius: var(--radius); border: 1px solid var(--border); 
    box-shadow: var(--shadow-sm); overflow: hidden; height: 100%;
    opacity: 0; transform: translateY(8px); animation: cardIn 0.4s var(--ease-out) forwards;
    animation-delay: 200ms;
}
.fee-panel-head { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.25rem; border-bottom: 1px solid var(--border); }
.fee-panel-title { font-size: 1rem; font-weight: 700; color: var(--text); margin: 0; }
.fee-panel-sub { font-size: .75rem; color: var(--muted); margin: .125rem 0 0; }
.fee-panel-body { padding: 1.25rem; }
.fee-panel-badge { 
    font-size: .65rem; font-weight: 700; padding: .25rem .75rem; border-radius: 20px; 
    background: #eff6ff; color: var(--blue); text-transform: uppercase; letter-spacing: .05em;
}

/* ── Progress Ring ── */
.progress-ring { transform: rotate(-90deg); }
.progress-ring__bg { stroke: #f1f5f9; }
.progress-ring__circle { 
    stroke: var(--blue); 
    transition: stroke-dashoffset 1s var(--ease-out); 
}

/* ── Table ── */
.fee-table { width: 100%; border-collapse: separate; border-spacing: 0; }
.fee-table thead th { 
    font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; 
    color: var(--muted); padding: .75rem 1rem; background: #f8fafc; border-bottom: 1px solid var(--border);
}
.fee-table thead th:first-child { border-radius: 8px 0 0 0; }
.fee-table thead th:last-child { border-radius: 0 8px 0 0; }
.fee-table tbody tr { 
    transition: background 150ms var(--ease-out); 
}
@media (hover: hover) and (pointer: fine) {
    .fee-table tbody tr:hover { background: #f8fafc; }
}
.fee-table tbody td { padding: 1rem; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.fee-table tbody tr:last-child td { border-bottom: none; }

.fee-class-icon { 
    width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center;
    font-size: .75rem; font-weight: 800; background: #eff6ff; color: var(--blue); flex-shrink: 0;
}

.fee-progress { height: 6px; background: #f1f5f9; border-radius: 3px; overflow: hidden; }
.fee-progress-bar { height: 100%; background: var(--blue); border-radius: 3px; transition: width 1s var(--ease-out); }

/* ── Action Cards ── */
.action-card { 
    display: block; text-decoration: none !important; color: var(--text);
    transition: transform 200ms var(--ease-out);
    outline: none;
}
.action-card:focus-visible { 
    box-shadow: 0 0 0 3px rgba(37,99,235,.3); 
    border-radius: var(--radius);
}
@media (hover: hover) and (pointer: fine) {
    .action-card:hover { transform: translateY(-3px); }
}
.action-card:active { transform: scale(0.98); }

.action-card-inner { 
    background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); 
    padding: 1.5rem; text-align: center; transition: border-color 200ms var(--ease-out), box-shadow 200ms var(--ease-out);
}
@media (hover: hover) and (pointer: fine) {
    .action-card:hover .action-card-inner { 
        border-color: var(--blue); 
        box-shadow: var(--shadow); 
    }
}

.action-card-icon { 
    width: 56px; height: 56px; border-radius: 12px; display: flex; align-items: center; justify-content: center;
    margin: 0 auto .75rem; font-size: 1.25rem; transition: transform 200ms var(--ease-out);
}
@media (hover: hover) and (pointer: fine) {
    .action-card:hover .action-card-icon { transform: scale(1.1); }
}

.action-card-icon.blue { background: #eff6ff; color: var(--blue); }
.action-card-icon.green { background: #f0fdf4; color: var(--green); }
.action-card-icon.yellow { background: #fffbeb; color: var(--yellow); }
.action-card-icon.red { background: #fef2f2; color: var(--red); }

.action-card-title { font-size: .875rem; font-weight: 700; color: var(--text); margin: 0 0 .25rem; }
.action-card-desc { font-size: .75rem; color: var(--muted); margin: 0; }

/* ── Section Labels ── */
.section-label { font-size: .688rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--muted); margin: 1.5rem 0 .75rem; }

/* ── Staggered animation delays ── */
.summary-card:nth-child(1) { animation-delay: 0ms; }
.summary-card:nth-child(2) { animation-delay: 50ms; }
.summary-card:nth-child(3) { animation-delay: 100ms; }
.summary-card:nth-child(4) { animation-delay: 150ms; }

/* ── Mobile ── */
@media (max-width: 768px) {
    .fee-dash-wrap { padding: 1rem; }
    .fee-heading { font-size: 1.25rem; }
    .fee-sub { font-size: 0.8rem; }
    .summary-card-value { font-size: 1.35rem; }
    .summary-card { padding: 1.25rem; }
    .summary-card-bg { font-size: 4rem; right: -10px; bottom: -10px; }
    .section-label { margin-top: 1.25rem; }
    .fee-panel-head { flex-direction: column; align-items: flex-start; gap: 0.5rem; }
    .fee-panel-body { padding: 1rem; }
    .fee-table thead th { padding: 0.6rem 0.75rem; font-size: 0.6rem; }
    .fee-table tbody td { padding: 0.75rem; font-size: 0.8rem; }
    .fee-class-icon { width: 32px; height: 32px; font-size: 0.65rem; }
    .action-card-inner { padding: 1rem; }
    .action-card-icon { width: 44px; height: 44px; font-size: 1rem; }
    .action-card-title { font-size: 0.8rem; }
}

@media (max-width: 420px) {
    .fee-heading { font-size: 1.1rem; }
    .summary-card-value { font-size: 1.15rem; }
    .summary-card-label { font-size: 0.6rem; }
    .summary-card-footer { font-size: 0.65rem; flex-direction: column; gap: 0.35rem; align-items: flex-start; }
}

/* ── Reduced motion ── */
@media (prefers-reduced-motion: reduce) {
    .summary-card, .fee-panel, .action-card { animation: none; opacity: 1; transform: none; transition: none; }
    .fee-btn { transition: none; }
    .summary-card:hover, .action-card:hover { transform: none; }
    .progress-ring__circle, .summary-card-progress-bar, .fee-progress-bar { transition: none; }
}
</style>

<div class="fee-dash-wrap">
    {{-- ① HEADER --}}
    <div class="row align-items-center mb-4">
        <div class="col-md-7">
            <h1 class="fee-heading">Financial Insights</h1>
            <p class="fee-sub">Revenue performance & fee distribution overview</p>
        </div>
        <div class="col-md-5 text-md-end mt-2 mt-md-0">
            @if($currentYear)
            <div class="d-inline-flex align-items-center bg-white border px-3 py-2 rounded-lg shadow-sm">
                <i class="fas fa-calendar-alt text-primary mr-2"></i>
                <span class="text-dark font-weight-bold small">{{ $currentYear->name }}</span>
                @if($currentYear->is_current)
                    <span class="badge bg-success bg-opacity-10 text-success ms-2" style="font-size: .6rem; font-weight: 700;">CURRENT</span>
                @endif
            </div>
            @endif
        </div>
    </div>

    @include('flash::message')
    
    @if(!$currentYear)
        <div class="fee-alert fee-alert-warning mb-4">
            <div class="fee-alert-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="fee-alert-body">
                <p class="fee-alert-title">Action Required</p>
                <p class="fee-alert-desc">Please configure an active academic year to see current financial data.</p>
            </div>
            <a href="{{ route('academic-years.index') }}" class="fee-alert-action">Configure</a>
        </div>
    @endif

    {{-- ② SUMMARY METRICS --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="summary-card grad-blue" onclick="window.location='{{ route('fees.reports.expected-revenue') }}'" tabindex="0">
                <i class="fas fa-money-check-alt summary-card-bg"></i>
                <div class="summary-card-label">Expected Revenue</div>
                <div class="summary-card-value">KES {{ number_format($expectedRevenue) }}</div>
                <div class="summary-card-progress">
                    <div class="summary-card-progress-bar" style="width: {{ $expectedRevenue > 0 ? ($metrics['total_collected'] / $expectedRevenue) * 100 : 0 }}%"></div>
                </div>
                <div class="summary-card-footer">
                    <span>Collected: KES {{ number_format($metrics['total_collected'], 0) }}</span>
                    <a href="{{ route('fees.reports.expected-revenue') }}" class="summary-card-link">View Report <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-sm-6">
            <div class="summary-card grad-green" onclick="window.location='{{ route('fee-management.index') }}'" tabindex="0">
                <i class="fas fa-check-circle summary-card-bg"></i>
                <div class="summary-card-label">Collection Rate</div>
                <div class="summary-card-value">{{ $metrics['collection_rate'] }}%</div>
                <div class="summary-card-progress">
                    <div class="summary-card-progress-bar" style="width: {{ $metrics['collection_rate'] }}%"></div>
                </div>
                <div class="summary-card-footer">
                    <span>KES {{ number_format($metrics['total_collected'], 0) }} collected</span>
                    <a href="{{ route('fee-management.index') }}" class="summary-card-link">Collect <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-sm-6">
            <div class="summary-card grad-yellow" onclick="window.location='{{ route('fees.reports.discount-summary') }}'" tabindex="0">
                <i class="fas fa-tags summary-card-bg"></i>
                <div class="summary-card-label">Total Discounts</div>
                <div class="summary-card-value">KES {{ number_format($totalDiscounts) }}</div>
                <div class="summary-card-progress">
                    <div class="summary-card-progress-bar" style="width: {{ $expectedRevenue > 0 ? ($totalDiscounts / $expectedRevenue) * 100 : 0 }}%"></div>
                </div>
                <div class="summary-card-footer">
                    <span>Fee Reductions</span>
                    <a href="{{ route('fees.reports.discount-summary') }}" class="summary-card-link">Details <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-sm-6">
            <div class="summary-card grad-red" onclick="window.location='{{ route('fees.assignments.unassigned') }}'" tabindex="0">
                <i class="fas fa-user-clock summary-card-bg"></i>
                <div class="summary-card-label">Pending Setup</div>
                <div class="summary-card-value">{{ $notAssignedCount }}</div>
                @php
                    $totalStd = ($studentsWithFees ?? 0) + $notAssignedCount;
                    $assignedPercent = $totalStd > 0 ? ( ($studentsWithFees ?? 0) / $totalStd) * 100 : 0;
                    $unassignedPercent = 100 - $assignedPercent;
                @endphp
                <div class="summary-card-progress">
                    <div class="summary-card-progress-bar" style="width: {{ $unassignedPercent }}%"></div>
                </div>
                <div class="summary-card-footer">
                    <span>Unassigned Students</span>
                    <a href="{{ route('fees.assignments.unassigned') }}" class="summary-card-link">Fix Now <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>

    {{-- ③ MAIN GRIDS --}}
    <div class="row g-4">
        {{-- Assignment Progress Card --}}
        <div class="col-lg-5">
            <div class="fee-panel">
                <div class="fee-panel-head">
                    <div>
                        <h3 class="fee-panel-title">Enrollment & Fees</h3>
                        <p class="fee-panel-sub">Tracking student fee assignments</p>
                    </div>
                </div>
                <div class="fee-panel-body text-center">
                    <div class="position-relative d-inline-block mb-4">
                        <svg class="progress-ring" width="140" height="140">
                            <circle class="progress-ring__bg" stroke-width="10" fill="transparent" r="64" cx="70" cy="70" />
                            <circle class="progress-ring__circle" stroke-width="10" 
                                stroke-dasharray="{{ 2 * pi() * 64 }}" 
                                stroke-dashoffset="{{ (1 - ($assignedPercent/100)) * (2 * pi() * 64) }}" 
                                stroke-linecap="round" 
                                fill="transparent" r="64" cx="70" cy="70" />
                        </svg>
                        <div class="position-absolute" style="top: 50%; left: 50%; transform: translate(-50%, -50%);">
                            <h2 class="fw-bold mb-0" style="font-size: 1.5rem;">{{ round($assignedPercent) }}%</h2>
                            <span class="small text-muted fw-bold">ASSIGNED</span>
                        </div>
                    </div>
                    
                    <div class="row g-2 text-start px-2">
                        <div class="col-12">
                            <div class="p-3 rounded border d-flex align-items-center justify-content-between" style="background: #f8fafc;">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle me-2" style="width: 10px; height: 10px; background: var(--blue);"></div>
                                    <span class="small fw-bold">Assigned</span>
                                </div>
                                <span class="fw-bold">{{ $studentsWithFees ?? 0 }} Students</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="p-3 rounded border d-flex align-items-center justify-content-between" style="background: #f8fafc;">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle me-2" style="width: 10px; height: 10px; background: var(--red);"></div>
                                    <span class="small fw-bold">Remaining</span>
                                </div>
                                <span class="fw-bold">{{ $notAssignedCount }} Students</span>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('fees.assignments.create') }}" class="fee-btn fee-btn-primary w-100 mt-4 py-3 justify-content-center">
                        <i class="fas fa-plus-circle me-1"></i> Start Bulk Assignment
                    </a>
                </div>
            </div>
        </div>

        {{-- Revenue Ranking Card --}}
        <div class="col-lg-7">
            <div class="fee-panel">
                <div class="fee-panel-head">
                    <div>
                        <h3 class="fee-panel-title">Revenue by Class</h3>
                        <p class="fee-panel-sub">Top classes by expected revenue</p>
                    </div>
                    <span class="fee-panel-badge">TOP PERFORMERS</span>
                </div>
                <div class="fee-panel-body p-0">
                    <div class="table-responsive">
                        <table class="fee-table">
                            <thead>
                                <tr>
                                    <th>CLASS</th>
                                    <th style="width: 200px;">PROGRESS</th>
                                    <th class="text-end">EXPECTED</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $maxRev = count($revenueByClass) > 0 ? $revenueByClass->max('total') : 1;
                                @endphp
                                @forelse($revenueByClass as $row)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="fee-class-icon me-3">
                                                    {{ substr($row->class_name, 0, 2) }}
                                                </div>
                                                <span class="fw-bold">{{ $row->class_name }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            @php $classPercent = ($row->total / $maxRev) * 100; @endphp
                                            <div class="fee-progress">
                                                <div class="fee-progress-bar" style="width: {{ $classPercent }}%"></div>
                                            </div>
                                        </td>
                                        <td class="text-end fw-bold">
                                            KES {{ number_format($row->total) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">
                                            <i class="fas fa-chart-bar mb-2" style="font-size: 2rem;"></i>
                                            <p class="mb-0 small">No revenue data available yet</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="text-center py-3 border-top">
                    <a href="{{ route('fees.reports.expected-revenue') }}" class="text-primary fw-bold small text-decoration-none">View Full Distribution <i class="fas fa-chevron-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>

    {{-- ④ ACTION GRID --}}
    <p class="section-label mt-5">Control Panel</p>
    <div class="row g-3">
        <div class="col-lg-3 col-md-6">
            <a href="{{ route('fees.assignments.create') }}" class="action-card">
                <div class="action-card-inner">
                    <div class="action-card-icon blue">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <h6 class="action-card-title">Bulk Assign</h6>
                    <p class="action-card-desc">Assign fees by class or group</p>
                </div>
            </a>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <a href="{{ route('fee-structures.create') }}" class="action-card">
                <div class="action-card-inner">
                    <div class="action-card-icon green">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <h6 class="action-card-title">Structure Setup</h6>
                    <p class="action-card-desc">Define new billing templates</p>
                </div>
            </a>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <a href="{{ route('fees.discounts.create') }}" class="action-card">
                <div class="action-card-inner">
                    <div class="action-card-icon yellow">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <h6 class="action-card-title">Fee Reliefs</h6>
                    <p class="action-card-desc">Manage scholarships & discounts</p>
                </div>
            </a>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <a href="{{ route('fee-management.index') }}" class="action-card">
                <div class="action-card-inner">
                    <div class="action-card-icon red">
                        <i class="fas fa-cash-register"></i>
                    </div>
                    <h6 class="action-card-title">Collection</h6>
                    <p class="action-card-desc">Record and verify payments</p>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection