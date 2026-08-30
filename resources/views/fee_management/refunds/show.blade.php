@extends('layouts.app')

@section('content')
<div class="report-wrap">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="icon-box bg-amber-light text-amber"><i class="fas fa-hand-holding-usd"></i></div>
            <div>
                <h1 class="page-title mb-0">Refund #{{ $refund->id }}</h1>
                <p class="page-subtitle mb-0">{{ $refund->student->full_name ?? 'N/A' }} &middot; <span class="mono-sm">{{ $refund->student->admission_no ?? '' }}</span></p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('fees.refunds.index') }}" class="btn-ghost-custom"><i class="fas fa-arrow-left me-1"></i> Back</a>
        </div>
    </div>

    <div class="detail-grid">
        <div class="summary-column">
            <div class="status-card">
                <div class="status-card-label">Status</div>
                @php
                    $badge = match($refund->status) {
                        'requested' => 'status-requested',
                        'approved' => 'status-approved',
                        'rejected' => 'status-rejected',
                        default => 'status-completed',
                    };
                @endphp
                <span class="status-badge {{ $badge }}">{{ $refund->status_label }}</span>
                <div class="amount-display">
                    <span class="amount-label">Amount</span>
                    <span class="amount-value">KSh {{ number_format($refund->amount, 2) }}</span>
                </div>
            </div>

            <div class="info-card">
                <div class="info-row"><span class="info-label">Source Payment</span>
                    <span class="info-value">{{ $refund->payment ? ($refund->payment->receipt_number ?? 'RCP-'.$refund->payment->payment_id) : 'General' }}</span></div>
                <div class="info-row"><span class="info-label">Method</span>
                    <span class="info-value">{{ $refund->refund_method ? ucwords(str_replace('_',' ', $refund->refund_method)) : '—' }}</span></div>
                <div class="info-row"><span class="info-label">Reference</span>
                    <span class="info-value mono-sm">{{ $refund->refund_reference ?? '—' }}</span></div>
                <div class="info-row"><span class="info-label">Requested By</span>
                    <span class="info-value">{{ $refund->requestedBy->name ?? 'System' }}</span></div>
                <div class="info-row"><span class="info-label">Requested At</span>
                    <span class="info-value">{{ $refund->requested_at ? $refund->requested_at->format('d M Y H:i') : '—' }}</span></div>
                @if($refund->reviewedBy)
                <div class="info-row"><span class="info-label">Reviewed By</span>
                    <span class="info-value">{{ $refund->reviewedBy->name ?? '' }}</span></div>
                @endif
                @if($refund->completedBy)
                <div class="info-row"><span class="info-label">Completed By</span>
                    <span class="info-value">{{ $refund->completedBy->name ?? '' }} ({{ $refund->completed_at ? $refund->completed_at->format('d M Y H:i') : '' }})</span></div>
                @endif
            </div>
        </div>

        <div class="main-column">
            <div class="reason-card">
                <div class="reason-label">Reason</div>
                <p class="reason-text">{{ $refund->reason }}</p>
                @if($refund->supporting_info)
                    <div class="reason-label mt-3">Supporting Information</div>
                    <p class="reason-text">{{ $refund->supporting_info }}</p>
                @endif
                @if($refund->approval_notes)
                    <div class="reason-label mt-3">Approval Notes</div>
                    <p class="reason-text text-emerald">{{ $refund->approval_notes }}</p>
                @endif
                @if($refund->rejection_reason)
                    <div class="reason-label mt-3">Rejection Reason</div>
                    <p class="reason-text text-rose">{{ $refund->rejection_reason }}</p>
                @endif
            </div>

            {{-- Workflow Actions --}}
            @if($refund->status === 'requested' && auth()->user()->can('fees.approve'))
            <div class="workflow-card">
                <h4 class="workflow-title">Review Decision</h4>
                <form action="{{ route('fees.refunds.approve', $refund->id) }}" method="POST" class="wf-form mb-2">
                    @csrf
                    <input type="text" name="approval_notes" class="form-control" placeholder="Approval notes (optional)">
                    <button class="btn-approve"><i class="fas fa-check me-1"></i> Approve</button>
                </form>
                <form action="{{ route('fees.refunds.reject', $refund->id) }}" method="POST" class="wf-form">
                    @csrf
                    <input type="text" name="rejection_reason" class="form-control" placeholder="Rejection reason (required)" required>
                    <button class="btn-reject" onclick="return confirm('Reject this refund?')"><i class="fas fa-times me-1"></i> Reject</button>
                </form>
            </div>
            @endif

            @if($refund->status === 'approved' && auth()->user()->can('fees.manage'))
            <div class="workflow-card">
                <h4 class="workflow-title">Complete Refund</h4>
                <p class="wf-hint">Completing posts the refund to the student's ledger as a credit and records the payout.</p>
                <form action="{{ route('fees.refunds.complete', $refund->id) }}" method="POST" class="wf-form">
                    @csrf
                    <select name="refund_method" class="form-control" required>
                        <option value="">Refund method</option>
                        <option value="mpesa">M-Pesa</option>
                        <option value="bank">Bank Transfer</option>
                        <option value="cash">Cash</option>
                        <option value="cheque">Cheque</option>
                        <option value="other">Other</option>
                    </select>
                    <input type="text" name="refund_reference" class="form-control" placeholder="Transaction / reference (optional)">
                    <button class="btn-complete"><i class="fas fa-check-circle me-1"></i> Complete Refund</button>
                </form>
            </div>
            @endif

            @if($refund->status === 'completed' && $refund->ledgerEntry)
            <div class="info-card">
                <div class="info-row"><span class="info-label">Ledger Entry</span>
                    <span class="info-value mono-sm">#{{ $refund->ledgerEntry->id }} &middot; Balance KSh {{ number_format($refund->ledgerEntry->balance_after, 2) }}</span></div>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
:root{
    --indigo:#4f46e5; --indigo-light:#eef2ff; --amber:#f59e0b; --amber-600:#d97706; --amber-light:#fffbeb;
    --emerald:#10b981; --emerald-light:#ecfdf5; --rose:#f43f5e; --rose-light:#fff1f2;
    --slate-50:#f8fafc; --slate-100:#f1f5f9; --slate-200:#e2e8f0; --slate-300:#cbd5e1; --slate-400:#94a3b8;
    --slate-500:#64748b; --slate-600:#475569; --slate-700:#334155; --slate-800:#1e293b; --slate-900:#0f172a;
    --border:#e2e8f0; --ease-out: cubic-bezier(0.23,1,0.32,1);
}
.report-wrap{padding:1.5rem 2rem;background:#f9fafb;min-height:100vh;}
.page-title{font-size:1.25rem;font-weight:900;color:var(--slate-900);}
.page-subtitle{color:var(--slate-400);font-size:.8rem;font-weight:500;}
.mono-sm{font-family:monospace;font-size:.75rem;font-weight:600;}
.icon-box{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;}
.bg-amber-light{background:var(--amber-light);} .text-amber{color:var(--amber-600);}
.text-emerald{color:var(--emerald);} .text-rose{color:var(--rose);}
.btn-ghost-custom{display:inline-flex;align-items:center;padding:.5rem 1.25rem;border-radius:8px;font-size:.75rem;font-weight:700;text-decoration:none!important;cursor:pointer;background:#fff;border:1px solid var(--border);color:var(--slate-700);}
.detail-grid{display:grid;grid-template-columns:320px 1fr;gap:1.5rem;align-items:start;}
.summary-column{display:flex;flex-direction:column;gap:1rem;}
.main-column{display:flex;flex-direction:column;gap:1rem;}
.status-card,.info-card,.reason-card,.workflow-card{background:#fff;border:1px solid var(--border);border-radius:12px;padding:1.25rem;box-shadow:0 1px 3px rgba(0,0,0,.06);}
.status-card-label{font-size:.7rem;font-weight:700;color:var(--slate-400);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.5rem;}
.status-badge{display:inline-block;padding:4px 12px;border-radius:6px;font-size:.75rem;font-weight:700;}
.status-requested{background:var(--amber-light);color:var(--amber-600);}
.status-approved{background:var(--indigo-light);color:var(--indigo);}
.status-rejected{background:var(--rose-light);color:var(--rose);}
.status-completed{background:var(--emerald-light);color:var(--emerald);}
.amount-display{margin-top:1.25rem;border-top:1px solid var(--slate-100);padding-top:1rem;display:flex;flex-direction:column;}
.amount-label{font-size:.7rem;font-weight:700;color:var(--slate-400);text-transform:uppercase;}
.amount-value{font-size:1.5rem;font-weight:900;color:var(--slate-900);font-family:monospace;}
.info-row{display:flex;justify-content:space-between;padding:.55rem 0;border-bottom:1px solid var(--slate-100);font-size:.82rem;}
.info-row:last-child{border-bottom:none;}
.info-label{color:var(--slate-500);font-weight:600;}
.info-value{color:var(--slate-800);font-weight:700;text-align:right;}
.reason-label{font-size:.7rem;font-weight:700;color:var(--slate-400);text-transform:uppercase;letter-spacing:.05em;}
.reason-text{font-size:.9rem;color:var(--slate-700);margin:.4rem 0 0;line-height:1.5;}
.workflow-title{font-size:.85rem;font-weight:800;color:var(--slate-800);margin:0 0 .75rem;}
.wf-hint{font-size:.78rem;color:var(--slate-500);margin:0 0 .75rem;}
.wf-form{display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;}
.wf-form .form-control{flex:1;min-width:180px;border:1px solid var(--border);border-radius:8px;padding:.5rem .75rem;font-size:.82rem;}
.btn-approve,.btn-reject,.btn-complete{display:inline-flex;align-items:center;padding:.5rem 1rem;border-radius:8px;font-size:.72rem;font-weight:800;border:none;cursor:pointer;color:#fff;}
.btn-approve{background:var(--emerald);}
.btn-reject{background:var(--rose);}
.btn-complete{background:var(--indigo);}
@media (max-width:1024px){.detail-grid{grid-template-columns:1fr;}}

@media (max-width:768px) {
    .report-wrap { padding:1rem; }
    .d-flex.align-items-center.justify-content-between.mb-4 { flex-direction:column; align-items:flex-start!important; gap:0.75rem; }
    .d-flex.align-items-center.justify-content-between.mb-4 > .d-flex { width:100%; }
    .d-flex.align-items-center.justify-content-between.mb-4 .btn-ghost-custom { width:100%; justify-content:center; }
    .page-title { font-size:1.1rem; }
    .amount-value { font-size:1.2rem; }
    .info-row { font-size:0.78rem; }
    .info-value { font-size:0.78rem; }
    .wf-form { flex-direction:column; }
    .wf-form .form-control { min-width:0; width:100%; }
    .wf-form button { width:100%; justify-content:center; }
}

@media (max-width:420px) {
    .icon-box { width:34px; height:34px; font-size:0.85rem; }
    .page-title { font-size:1rem; }
}
</style>
@endsection
