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

    .fa-summary {
        background: var(--fa-slate-50);
        border: 1px solid var(--fa-slate-200);
        border-radius: 12px;
        padding: 0.875rem 1rem;
    }
    .fa-summary-label {
        font-size: 0.6875rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--fa-slate-400);
    }
    .fa-summary-value { font-weight: 600; color: var(--fa-slate-900); font-size: 0.875rem; margin-top: 0.125rem; }

    /* ── Timeline ── */
    .fa-timeline { position: relative; padding-left: 2rem; }
    .fa-timeline::before {
        content: '';
        position: absolute;
        left: 0.5rem;
        top: 0.5rem;
        bottom: 0.5rem;
        width: 2px;
        background: var(--fa-slate-200);
        border-radius: 2px;
    }
    .fa-event { position: relative; padding-bottom: 1.25rem; }
    .fa-event:last-child { padding-bottom: 0; }
    .fa-event-dot {
        position: absolute;
        left: -2rem;
        top: 0.3rem;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        border: 3px solid var(--fa-surface);
        box-shadow: 0 0 0 2px var(--fa-slate-200);
    }
    .fa-event--created  .fa-event-dot { background: var(--fa-indigo-600); }
    .fa-event--approved .fa-event-dot { background: var(--fa-emerald-600); }
    .fa-event--rejected .fa-event-dot { background: var(--fa-rose-600); }
    .fa-event--default  .fa-event-dot { background: var(--fa-slate-400); }

    .fa-event-card {
        background: var(--fa-surface);
        border: 1px solid var(--fa-slate-200);
        border-radius: 12px;
        overflow: hidden;
    }
    .fa-event-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        flex-wrap: wrap;
        padding: 0.75rem 1rem;
        background: var(--fa-slate-50);
        border-bottom: 1px solid var(--fa-slate-100);
    }
    .fa-event-action {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.28rem 0.75rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: capitalize;
    }
    .fa-event--created  .fa-event-action { background: var(--fa-indigo-50);  color: var(--fa-indigo-600); }
    .fa-event--approved .fa-event-action { background: var(--fa-emerald-50); color: var(--fa-emerald-600); }
    .fa-event--rejected .fa-event-action { background: var(--fa-rose-50);   color: var(--fa-rose-600); }
    .fa-event--default  .fa-event-action { background: var(--fa-slate-100); color: var(--fa-slate-600); }

    .fa-event-time { font-size: 0.75rem; font-weight: 600; color: var(--fa-slate-400); }
    .fa-event-body { padding: 0.875rem 1rem; }

    .fa-detail-chip {
        display: inline-flex;
        align-items: baseline;
        gap: 0.375rem;
        background: var(--fa-slate-50);
        border: 1px solid var(--fa-slate-200);
        border-radius: 8px;
        padding: 0.375rem 0.625rem;
        margin: 0 0.375rem 0.375rem 0;
        font-size: 0.78rem;
    }
    .fa-detail-chip-key { font-weight: 700; color: var(--fa-slate-500); text-transform: capitalize; }
    .fa-detail-chip-val { font-family: var(--fa-mono); color: var(--fa-slate-900); word-break: break-all; }

    .fa-meta { font-size: 0.75rem; color: var(--fa-slate-400); }
    .fa-meta i { width: 1rem; text-align: center; }

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
                    <h1>Adjustment Audit Trail</h1>
                    <p class="fa-head-sub">
                        {{ $adjustment->student->full_name }} —
                        {{ $adjustment->studentFeeAssignment->feeStructure->category->name ?? 'Uncategorized' }}
                        ({{ $adjustment->studentFeeAssignment->term ?? '-' }})
                    </p>
                </div>
                <a class="btn btn-outline-secondary" href="{{ route('fees.adjustments.show', $adjustment->id) }}" style="border-radius: 8px; font-weight: 600;">
                    <i class="fas fa-arrow-left mr-1" style="font-size: 0.8rem;"></i> Back to Adjustment
                </a>
            </div>
        </div>
    </section>

    <div class="content px-0">
        @include('flash::message')

        {{-- Adjustment summary --}}
        <div class="row mb-4">
            <div class="col-md-3 col-6 mb-3">
                <div class="fa-summary">
                    <div class="fa-summary-label">Original</div>
                    <div class="fa-summary-value" style="font-family: var(--fa-mono);">KES {{ number_format($adjustment->original_amount, 2) }}</div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="fa-summary">
                    <div class="fa-summary-label">New Amount</div>
                    <div class="fa-summary-value" style="font-family: var(--fa-mono); color: var(--fa-emerald-600);">KES {{ number_format($adjustment->new_amount, 2) }}</div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="fa-summary">
                    <div class="fa-summary-label">Type</div>
                    <div class="fa-summary-value" style="text-transform: capitalize;">{{ str_replace('_', ' ', $adjustment->adjustment_type) }}</div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="fa-summary">
                    <div class="fa-summary-label">Status</div>
                    <div class="fa-summary-value" style="text-transform: capitalize;">{{ $adjustment->status }}</div>
                </div>
            </div>
        </div>

        {{-- Timeline --}}
        <div class="card">
            <div class="card-header bg-white" style="border-bottom: 1px solid var(--fa-slate-100); padding: 1rem 1.375rem;">
                <h3 class="mb-0" style="font-weight: 700; font-size: 0.95rem; color: var(--fa-slate-900);">
                    <span style="display:inline-flex; width:30px; height:30px; border-radius:8px; background:var(--fa-indigo-50); color:var(--fa-indigo-600); align-items:center; justify-content:center; font-size:0.8rem; margin-right:0.625rem;"><i class="fas fa-history"></i></span>
                    Audit Events
                </h3>
            </div>
            <div class="card-body" style="padding: 1.375rem;">
                @if($logs->isEmpty())
                    <div class="fa-empty">
                        <div style="width: 64px; height: 64px; background: var(--fa-slate-100); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                            <i class="fas fa-history" style="font-size: 26px; color: var(--fa-slate-400);"></i>
                        </div>
                        <p style="font-size: 0.9375rem; font-weight: 600; color: var(--fa-slate-700); margin-bottom: 0.25rem;">No audit events recorded</p>
                        <p style="font-size: 0.8125rem;">Actions on this adjustment will appear here.</p>
                    </div>
                @else
                    <div class="fa-timeline">
                        @foreach($logs as $log)
                            @php
                                $actionClass = in_array($log->action, ['created', 'approved', 'rejected']) ? $log->action : 'default';
                                $actionIcon = [
                                    'created' => 'fa-plus-circle',
                                    'approved' => 'fa-check-circle',
                                    'rejected' => 'fa-times-circle',
                                ][$log->action] ?? 'fa-circle';
                                $details = is_array($log->details) ? $log->details : [];
                            @endphp
                            <div class="fa-event fa-event--{{ $actionClass }}">
                                <div class="fa-event-dot"></div>
                                <div class="fa-event-card">
                                    <div class="fa-event-head">
                                        <span class="fa-event-action"><i class="fas {{ $actionIcon }} mr-1"></i>{{ $log->action }}</span>
                                        <span class="fa-event-time">
                                            <i class="far fa-clock mr-1"></i>{{ $log->created_at->format('d/m/Y H:i') }}
                                        </span>
                                    </div>
                                    <div class="fa-event-body">
                                        <p class="mb-2" style="font-size: 0.875rem; color: var(--fa-slate-700);">
                                            <i class="fas fa-user mr-1" style="color: var(--fa-slate-400);"></i>
                                            <strong>{{ $log->user->name ?? 'System' }}</strong>
                                        </p>

                                        @if(!empty($details))
                                            <div class="mb-2">
                                                @foreach($details as $key => $value)
                                                    <span class="fa-detail-chip">
                                                        <span class="fa-detail-chip-key">{{ ucwords(str_replace('_', ' ', $key)) }}:</span>
                                                        <span class="fa-detail-chip-val">{{ is_scalar($value) || $value === null ? ($value ?? '—') : json_encode($value) }}</span>
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif

                                        @if($log->ip_address || $log->user_agent)
                                            <div class="fa-meta">
                                                @if($log->ip_address)
                                                    <span class="mr-3"><i class="fas fa-network-wired mr-1"></i>{{ $log->ip_address }}</span>
                                                @endif
                                                @if($log->user_agent)
                                                    <span><i class="fas fa-desktop mr-1"></i>{{ \Illuminate\Support\Str::limit($log->user_agent, 80) }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
