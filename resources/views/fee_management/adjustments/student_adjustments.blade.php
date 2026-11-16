@extends('layouts.app')

@section('content')
<style>
    .fa-cr {
        --fa-indigo-600: oklch(0.511 0.230 272);
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
        --fa-emerald-50:  oklch(0.979 0.021 166);
        --fa-rose-600:   oklch(0.575 0.210 22);
        --fa-rose-50:    oklch(0.969 0.015 12);
        --fa-amber-600:  oklch(0.666 0.179 58);
        --fa-amber-50:   oklch(0.980 0.022 95);
        --fa-violet-500: oklch(0.606 0.180 278);
        --fa-violet-50:  oklch(0.960 0.022 302);
        --fa-ease-out:   cubic-bezier(0.23, 1, 0.32, 1);
        --fa-mono: ui-monospace, "SFMono-Regular", "Cascadia Code", Menlo, Consolas, monospace;
    }

    .fa-head { padding: 0.25rem 0 1.25rem; }
    .fa-head h1 {
        font-size: 1.35rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        color: var(--fa-slate-900);
        margin: 0;
    }
    .fa-head-sub { color: var(--fa-slate-500); font-size: 0.813rem; margin-top: 0.125rem; }

    .fa-metric {
        background: var(--fa-surface);
        border: 1px solid var(--fa-slate-200);
        border-radius: 14px;
        padding: 1rem 1.125rem;
        height: 100%;
    }
    .fa-metric-label {
        font-size: 0.6875rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--fa-slate-500);
    }
    .fa-metric-value {
        font-size: 1.4rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        margin-top: 0.25rem;
        font-variant-numeric: tabular-nums;
        font-family: var(--fa-mono);
        color: var(--fa-slate-900);
    }

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
    }
    .fa-table tbody td {
        padding: 0.8125rem 1rem;
        border-color: var(--fa-slate-100);
        vertical-align: middle;
    }
    @media (hover: hover) and (pointer: fine) {
        .fa-table tbody tr:hover { background: oklch(0.970 0.003 264); }
    }
    .fa-table-amount { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; font-family: var(--fa-mono); }
    .value-muted { color: var(--fa-slate-500); }

    .fa-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.28rem 0.625rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .fa-badge--reduction { background: var(--fa-indigo-50); color: var(--fa-indigo-600); }
    .fa-badge--increase  { background: var(--fa-amber-50);  color: var(--fa-amber-600); }
    .fa-badge--waiver    { background: var(--fa-violet-50); color: var(--fa-violet-500); }

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
    .fa-pill--pending  { background: var(--fa-amber-50);   color: var(--fa-amber-600); }
    .fa-pill--approved { background: var(--fa-emerald-50); color: var(--fa-emerald-600); }
    .fa-pill--rejected { background: var(--fa-rose-50);    color: var(--fa-rose-600); }
    .fa-pill::before {
        content: '';
        width: 6px; height: 6px; border-radius: 50%;
        background: currentColor;
    }

    .fa-empty { padding: 4rem 1rem; text-align: center; color: var(--fa-slate-500); }

    @media (max-width: 768px) {
        .fa-head .d-flex { flex-direction: column; align-items: stretch !important; gap: 0.625rem; }
        .fa-head .btn { justify-content: center; }
    }
</style>

<div class="fa-cr">
    <section class="content-header">
        <div class="container-fluid px-0">
            <div class="fa-head d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <h1>Fee Adjustments — {{ $student->full_name }}</h1>
                    <p class="fa-head-sub">Admission No. {{ $student->admission_no }} · All adjustment requests on record</p>
                </div>
                <div class="d-flex gap-2">
                    <a class="btn btn-outline-secondary" href="{{ route('fees.adjustments.create', ['student_id' => $student->student_id]) }}" style="border-radius: 8px; font-weight: 600;">
                        <i class="fas fa-plus mr-1" style="font-size: 0.8rem;"></i> New Adjustment
                    </a>
                    <a class="btn btn-primary" href="{{ route('fees.adjustments.index') }}" style="border-radius: 8px; font-weight: 600;">
                        <i class="fas fa-arrow-left mr-1" style="font-size: 0.8rem;"></i> All Adjustments
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-0">
        @include('flash::message')

        {{-- Summary --}}
        <div class="row mb-4">
            <div class="col-lg-4 col-6 mb-3">
                <div class="fa-metric">
                    <div>
                        <div class="fa-metric-label">Total Requests</div>
                        <div class="fa-metric-value">{{ $adjustments->count() }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-6 mb-3">
                <div class="fa-metric">
                    <div>
                        <div class="fa-metric-label">Pending</div>
                        <div class="fa-metric-value" style="color: var(--fa-amber-600);">{{ $adjustments->where('status', 'pending')->count() }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-12 mb-3">
                <div class="fa-metric">
                    <div>
                        <div class="fa-metric-label">Net Adjustment (Approved)</div>
                        <div class="fa-metric-value" style="color: var(--fa-emerald-600);">
                            KES {{ number_format($adjustments->where('status', 'approved')->sum('adjustment_amount'), 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="card">
            <div class="card-header bg-white" style="border-bottom: 1px solid var(--fa-slate-100); padding: 1rem 1.375rem;">
                <h3 class="mb-0" style="font-weight: 700; font-size: 0.95rem; color: var(--fa-slate-900);">
                    <span style="display:inline-flex; width:30px; height:30px; border-radius:8px; background:var(--fa-indigo-50); color:var(--fa-indigo-600); align-items:center; justify-content:center; font-size:0.8rem; margin-right:0.625rem;"><i class="fas fa-sliders-h"></i></span>
                    Adjustment History
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table fa-table mb-0" style="min-width: 760px;">
                        <thead>
                            <tr>
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
                                <tr>
                                    <td class="value-muted">
                                        {{ $adjustment->studentFeeAssignment->feeStructure->category->name ?? 'Uncategorized' }}
                                        ({{ $adjustment->studentFeeAssignment->term ?? '-' }})
                                    </td>
                                    <td class="fa-table-amount">{{ number_format($adjustment->original_amount, 2) }}</td>
                                    <td class="fa-table-amount" style="font-weight: 700; color: var(--fa-emerald-600);">
                                        {{ number_format($adjustment->new_amount, 2) }}
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
                                    <td class="value-muted">{{ $adjustment->created_at->format('d/m/Y') }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('fees.adjustments.show', $adjustment->id) }}" class="btn btn-sm btn-light" style="border-radius: 8px;">
                                            <i class="far fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="fa-empty">
                                            <div style="width: 64px; height: 64px; background: var(--fa-slate-100); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                                                <i class="fas fa-sliders-h" style="font-size: 26px; color: var(--fa-slate-400);"></i>
                                            </div>
                                            <p style="font-size: 0.9375rem; font-weight: 600; color: var(--fa-slate-700); margin-bottom: 0.25rem;">No adjustments for this student</p>
                                            <p style="font-size: 0.8125rem; margin-bottom: 1rem;">Requests made against this student's fees will appear here.</p>
                                            <a href="{{ route('fees.adjustments.create', ['student_id' => $student->student_id]) }}" class="btn btn-primary">
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
        </div>
    </div>
</div>
@endsection
