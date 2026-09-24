@extends('layouts.app')

@section('content')
<style>
    .fa-adj {
        --fa-indigo-600: oklch(0.511 0.230 272);
        --fa-indigo-500: oklch(0.555 0.210 272);
        --fa-indigo-100: oklch(0.930 0.034 272);
        --fa-indigo-50:  oklch(0.962 0.018 272);
        --fa-slate-900:  oklch(0.206 0.010 264);
        --fa-slate-700:  oklch(0.372 0.016 264);
        --fa-slate-600:  oklch(0.446 0.018 264);
        --fa-slate-500:  oklch(0.554 0.018 264);
        --fa-slate-400:  oklch(0.704 0.015 264);
        --fa-slate-200:  oklch(0.928 0.008 264);
        --fa-slate-100:  oklch(0.967 0.005 264);
        --fa-slate-50:   oklch(0.984 0.003 264);
        --fa-surface:    oklch(0.995 0.003 264);
        --fa-emerald-600: oklch(0.596 0.145 163);
        --fa-emerald-500: oklch(0.696 0.170 162);
        --fa-emerald-50:  oklch(0.979 0.021 166);
        --fa-rose-600:   oklch(0.575 0.210 22);
        --fa-rose-500:   oklch(0.643 0.220 22);
        --fa-rose-50:    oklch(0.969 0.015 12);
        --fa-amber-600:  oklch(0.666 0.179 58);
        --fa-amber-50:   oklch(0.980 0.022 95);
        --fa-violet-500: oklch(0.606 0.180 278);
        --fa-violet-50:  oklch(0.960 0.022 302);
        --fa-ease-out:   cubic-bezier(0.23, 1, 0.32, 1);
        --fa-mono: ui-monospace, "SFMono-Regular", "Cascadia Code", Menlo, Consolas, monospace;
    }

    /* ── Page header ── */
    .fa-head { padding: 0.25rem 0 1.25rem; }
    .fa-head h1 {
        font-size: 1.35rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        color: var(--fa-slate-900);
        margin: 0;
    }
    .fa-head-sub {
        color: var(--fa-slate-500);
        font-size: 0.813rem;
        margin-top: 0.125rem;
    }

    /* ── Metric cards — clean, full border, tinted flyout ── */
    .fa-metric {
        background: var(--fa-surface);
        border: 1px solid var(--fa-slate-200);
        border-radius: 14px;
        box-shadow: 0 1px 2px oklch(0 0 0 / 0.04);
        padding: 1rem 1.125rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        height: 100%;
        opacity: 0;
        transform: translateY(8px);
        animation: faMetricIn 0.32s var(--fa-ease-out) forwards;
    }
    @keyframes faMetricIn {
        to { opacity: 1; transform: translateY(0); }
    }
    .fa-metric:nth-child(1) { animation-delay: 0ms; }
    .fa-metric:nth-child(2) { animation-delay: 45ms; }
    .fa-metric:nth-child(3) { animation-delay: 90ms; }
    .fa-metric:nth-child(4) { animation-delay: 135ms; }
    .fa-metric:active { transform: scale(0.99); transition: transform 0.12s var(--fa-ease-out); }

    .fa-metric-label {
        font-size: 0.6875rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--fa-slate-500);
    }
    .fa-metric-value {
        font-size: 1.6rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        line-height: 1.1;
        margin-top: 0.25rem;
        font-variant-numeric: tabular-nums;
    }
    .fa-metric-value--mono { font-family: var(--fa-mono); }
    .fa-metric-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
        flex-shrink: 0;
    }
    .fa-metric-icon i { color: inherit; }
    .fa-metric--indigo .fa-metric-icon  { background: var(--fa-indigo-50);  color: var(--fa-indigo-600); }
    .fa-metric--indigo .fa-metric-value { color: var(--fa-indigo-600); }
    .fa-metric--amber .fa-metric-icon   { background: var(--fa-amber-50);   color: var(--fa-amber-600); }
    .fa-metric--amber .fa-metric-value  { color: var(--fa-amber-600); }
    .fa-metric--emerald .fa-metric-icon { background: var(--fa-emerald-50); color: var(--fa-emerald-600); }
    .fa-metric--emerald .fa-metric-value{ color: var(--fa-emerald-600); }
    .fa-metric--rose .fa-metric-icon    { background: var(--fa-rose-50);    color: var(--fa-rose-600); }
    .fa-metric--rose .fa-metric-value   { color: var(--fa-rose-600); }

    /* ── Card shell ── */
    .fa-panel .card-header { padding: 1.125rem 1.375rem; }
    .fa-panel-title {
        font-weight: 700;
        font-size: 0.95rem;
        color: var(--fa-slate-900);
        display: flex;
        align-items: center;
        gap: 0.625rem;
        margin: 0;
    }
    .fa-panel-title .fa-panel-mark {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        background: var(--fa-indigo-50);
        color: var(--fa-indigo-600);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        flex-shrink: 0;
    }

    /* ── Filters ── */
    .fa-filters { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; }

    /* ── Table ── */
    .fa-table { margin: 0; }
    .fa-table thead th {
        background: var(--fa-slate-50);
        border-bottom: 1px solid var(--fa-slate-200);
        padding: 0.75rem 1rem;
        font-size: 0.6875rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--fa-slate-500);
        white-space: nowrap;
        vertical-align: middle;
    }
    .fa-table thead th:first-child { padding-left: 1.375rem; }
    .fa-table tbody td {
        padding: 0.8125rem 1rem;
        border-color: var(--fa-slate-100);
        vertical-align: middle;
    }
    .fa-table tbody td:first-child { padding-left: 1.375rem; }
    .fa-table tbody tr {
        transition: background-color 0.15s var(--fa-ease-out);
    }
    @media (hover: hover) and (pointer: fine) {
        .fa-table tbody tr:hover { background: oklch(0.970 0.003 264); }
    }
    .fa-table .amount { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; font-family: var(--fa-mono); }
    .fa-table-amount { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; font-family: var(--fa-mono); }
    .amount-original { color: var(--fa-slate-500); }
    .amount-new { font-weight: 700; color: var(--fa-emerald-600); }
    .amount-delta { display: block; font-size: 0.6875rem; font-weight: 700; margin-top: 0.125rem; }
    .delta-minus { color: var(--fa-rose-600); }
    .delta-plus { color: var(--fa-emerald-600); }
    .delta-zero { color: var(--fa-slate-400); }
    .value-muted { color: var(--fa-slate-500); }

    .fa-student-name { font-weight: 600; color: var(--fa-slate-900); font-size: 0.9rem; line-height: 1.3; }
    .fa-student-adm { font-size: 0.75rem; color: var(--fa-slate-500); }
    .fa-main-line { color: var(--fa-slate-900); font-weight: 600; font-size: 0.875rem; }
    .fa-sub-line { font-size: 0.75rem; color: var(--fa-slate-500); }

    /* Type badges */
    .fa-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.28rem 0.625rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .fa-badge--reduction { background: var(--fa-indigo-50);  color: var(--fa-indigo-600); border: 1px solid oklch(0.905 0.050 272 / 0.6); }
    .fa-badge--increase  { background: var(--fa-amber-50);   color: var(--fa-amber-600);  border: 1px solid oklch(0.900 0.065 70 / 0.6); }
    .fa-badge--waiver    { background: var(--fa-violet-50);  color: var(--fa-violet-500); border: 1px solid oklch(0.910 0.040 302 / 0.6); }

    /* Status pills */
    .fa-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.28rem 0.625rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .fa-pill--pending  { background: var(--fa-amber-50);  border: 1px solid oklch(0.900 0.065 70 / 0.6); color: var(--fa-amber-600); }
    .fa-pill--approved { background: var(--fa-emerald-50); border: 1px solid oklch(0.889 0.048 163); color: var(--fa-emerald-600); }
    .fa-pill--rejected { background: var(--fa-rose-50);   border: 1px solid oklch(0.897 0.030 12 / 0.6); color: var(--fa-rose-600); }
    .fa-pill--pending::before {
        content: '';
        width: 6px; height: 6px; border-radius: 50%;
        background: var(--fa-amber-600);
    }
    .fa-pill--approved::before {
        content: '';
        width: 6px; height: 6px; border-radius: 50%;
        background: var(--fa-emerald-500);
    }
    .fa-pill--rejected::before {
        content: '';
        width: 6px; height: 6px; border-radius: 50%;
        background: var(--fa-rose-500);
    }

    /* Row actions */
    .fa-actions { display: inline-flex; gap: 0.375rem; justify-content: center; }
    .fa-action {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        border: none;
        background: var(--fa-slate-100);
        color: var(--fa-slate-600);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        cursor: pointer;
        text-decoration: none;
        transition: transform 0.16s var(--fa-ease-out),
                    background-color 0.16s var(--fa-ease-out),
                    color 0.16s var(--fa-ease-out);
    }
    .fa-action:active { transform: scale(0.96); }
    .fa-action:focus-visible {
        outline: 2px solid var(--fa-indigo-600);
        outline-offset: 1px;
    }
    @media (hover: hover) and (pointer: fine) {
        .fa-action--view:hover { background: var(--fa-slate-200); color: var(--fa-slate-900); }
    }

    /* ── Empty state ── */
    .fa-empty { padding: 4rem 1rem; text-align: center; color: var(--fa-slate-500); }

    /* ── Responsive ── */
    @media (max-width: 768px) {
        .fa-head .d-flex { flex-direction: column; align-items: stretch !important; gap: 0.625rem; }
        .fa-head .btn { justify-content: center; }
        .fa-filters { width: 100%; }
        .fa-filters .form-control-sm, .fa-filters .btn { width: 100%; }
        .fa-table thead th:nth-child(n+5),
        .fa-table tbody td:nth-child(n+5) { display: none; }
        .fa-table thead th:first-child, .fa-table tbody td:first-child { padding-left: 0.875rem; }
    }

    @media (prefers-reduced-motion: reduce) {
        .fa-metric {
            animation: none;
            opacity: 1;
            transform: none;
        }
        .fa-action,
        .fa-metric:active { transition: none; }
    }
</style>

<div class="fa-adj">
    <section class="content-header">
        <div class="container-fluid px-0">
            <div class="fa-head d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <h1>Fee Adjustments</h1>
                    <p class="fa-head-sub">Request fee reductions, increases and waivers</p>
                </div>
                <div class="d-flex gap-2">
                    <a class="btn btn-outline-secondary" href="{{ route('fees.adjustments.pending') }}" style="border-radius: 8px; font-weight: 600;">
                        <i class="fas fa-inbox mr-1" style="font-size: 0.8rem;"></i> Pending Approvals
                    </a>
                    <a class="btn btn-primary" href="{{ route('fees.adjustments.create') }}">
                        <i class="fas fa-plus mr-1"></i> New Adjustment
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-0">
        @include('flash::message')

        {{-- Metric cards --}}
        <div class="row mb-4">
            <div class="col-lg-3 col-6 mb-3">
                <div class="fa-metric fa-metric--indigo">
                    <div>
                        <div class="fa-metric-label">Total</div>
                        <div class="fa-metric-value">{{ $adjustments->total() }}</div>
                    </div>
                    <div class="fa-metric-icon"><i class="fas fa-sliders-h"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-6 mb-3">
                <div class="fa-metric fa-metric--amber">
                    <div>
                        <div class="fa-metric-label">Pending</div>
                        <div class="fa-metric-value">{{ $adjustments->where('status', 'pending')->count() }}</div>
                    </div>
                    <div class="fa-metric-icon"><i class="fas fa-clock"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-6 mb-3">
                <div class="fa-metric fa-metric--emerald">
                    <div>
                        <div class="fa-metric-label">Approved</div>
                        <div class="fa-metric-value">{{ $adjustments->where('status', 'approved')->count() }}</div>
                    </div>
                    <div class="fa-metric-icon"><i class="fas fa-check-circle"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-6 mb-3">
                <div class="fa-metric fa-metric--rose">
                    <div>
                        <div class="fa-metric-label">Rejected</div>
                        <div class="fa-metric-value">{{ $adjustments->where('status', 'rejected')->count() }}</div>
                    </div>
                    <div class="fa-metric-icon"><i class="fas fa-times-circle"></i></div>
                </div>
            </div>
        </div>

        {{-- Main table card --}}
        <div class="card fa-panel">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 0.75rem;">
                    <h3 class="fa-panel-title">
                        <span class="fa-panel-mark"><i class="fas fa-list-ul"></i></span>
                        All Adjustments
                    </h3>
                    <form action="{{ route('fees.adjustments.index') }}" method="GET" class="fa-filters">
                        <select name="status" class="form-control form-control-sm" style="width: 140px;" aria-label="Filter by status">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                        <select name="adjustment_type" class="form-control form-control-sm" style="width: 140px;" aria-label="Filter by type">
                            <option value="">All Types</option>
                            <option value="reduction" {{ request('adjustment_type') == 'reduction' ? 'selected' : '' }}>Reduction</option>
                            <option value="increase" {{ request('adjustment_type') == 'increase' ? 'selected' : '' }}>Increase</option>
                            <option value="waiver" {{ request('adjustment_type') == 'waiver' ? 'selected' : '' }}>Waiver</option>
                        </select>
                        <select name="student_id" class="form-control form-control-sm" style="width: 200px;" aria-label="Filter by student">
                            <option value="">All Students</option>
                            @foreach($students as $id => $name)
                                <option value="{{ $id }}" {{ request('student_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-primary btn-sm" type="submit">
                            <i class="fas fa-filter mr-1" style="font-size: 0.75rem;"></i> Filter
                        </button>
                        @if(request('status') || request('adjustment_type') || request('student_id'))
                            <a href="{{ route('fees.adjustments.index') }}" class="btn btn-sm btn-link" style="color: var(--fa-slate-500); text-decoration: none; font-weight: 600;">
                                Reset
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table fa-table mb-0" style="min-width: 900px;">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Fee Category</th>
                                <th class="fa-table-amount">Original</th>
                                <th class="fa-table-amount">New Amount</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Requested</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($adjustments as $adjustment)
                                @php
                                    $delta = $adjustment->original_amount - $adjustment->new_amount;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fa-student-name">{{ $adjustment->student->full_name }}</div>
                                        <div class="fa-student-adm">{{ $adjustment->student->admission_no }}</div>
                                    </td>
                                    <td class="value-muted">{{ $adjustment->studentFeeAssignment->feeStructure->category->name ?? '-' }}</td>
                                    <td class="amount amount-original">{{ number_format($adjustment->original_amount, 2) }}</td>
                                    <td class="amount amount-new">
                                        {{ number_format($adjustment->new_amount, 2) }}
                                        @if($adjustment->adjustment_type == 'waiver')
                                            <span class="amount-delta delta-minus">Fully waived</span>
                                        @elseif($delta > 0)
                                            <span class="amount-delta delta-minus">-{{ number_format($delta, 2) }}</span>
                                        @elseif($delta < 0)
                                            <span class="amount-delta delta-plus">+{{ number_format(abs($delta), 2) }}</span>
                                        @else
                                            <span class="amount-delta delta-zero">unchanged</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($adjustment->adjustment_type == 'reduction')
                                            <span class="fa-badge fa-badge--reduction">Reduction</span>
                                        @elseif($adjustment->adjustment_type == 'increase')
                                            <span class="fa-badge fa-badge--increase">Increase</span>
                                        @else
                                            <span class="fa-badge fa-badge--waiver">Waiver</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($adjustment->status == 'pending')
                                            <span class="fa-pill fa-pill--pending">Pending</span>
                                        @elseif($adjustment->status == 'approved')
                                            <span class="fa-pill fa-pill--approved">Approved</span>
                                        @else
                                            <span class="fa-pill fa-pill--rejected">Rejected</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fa-main-line">{{ $adjustment->requested_at->format('d/m/Y') }}</div>
                                        <div class="fa-sub-line">by {{ $adjustment->requestedBy->name ?? 'System' }}</div>
                                    </td>
                                    <td class="text-center">
                                        <div class="fa-actions">
                                            <a href="{{ route('fees.adjustments.show', $adjustment->id) }}"
                                               class="fa-action fa-action--view"
                                               title="View details" aria-label="View adjustment for {{ $adjustment->student->full_name }}">
                                                <i class="far fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8">
                                        <div class="fa-empty">
                                            <div style="width: 64px; height: 64px; background: var(--fa-slate-100); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                                                <i class="fas fa-sliders-h" style="font-size: 26px; color: var(--fa-slate-400);"></i>
                                            </div>
                                            <p style="font-size: 0.9375rem; font-weight: 600; color: var(--fa-slate-700); margin-bottom: 0.25rem;">No adjustments found</p>
                                            <p style="font-size: 0.8125rem; margin-bottom: 1rem;">Adjust your filters or request a new adjustment.</p>
                                            <a href="{{ route('fees.adjustments.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus mr-1"></i> New Adjustment
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($adjustments->hasPages())
                <div class="card-footer bg-white" style="border-top: 1px solid var(--fa-slate-100); padding: 0.875rem 1.375rem;">
                    <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 0.5rem;">
                        <div style="color: var(--fa-slate-500); font-size: 0.8125rem;">
                            Showing {{ $adjustments->firstItem() ?? 0 }} to {{ $adjustments->lastItem() ?? 0 }} of {{ $adjustments->total() }} entries
                        </div>
                        <div>
                            {{ $adjustments->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection