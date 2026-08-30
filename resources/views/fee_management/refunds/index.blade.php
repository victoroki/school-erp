@extends('layouts.app')

@section('content')
<div class="report-wrap">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="icon-box bg-amber-light text-amber">
                <i class="fas fa-hand-holding-usd"></i>
            </div>
            <div>
                <h1 class="page-title mb-0">Refunds</h1>
                <p class="page-subtitle mb-0">Request, approve, reject and complete refunds</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('fees.refunds.export-csv', request()->query()) }}" class="btn-ghost-custom">
                <i class="fas fa-file-csv me-1"></i> CSV
            </a>
            <a href="{{ route('fees.refunds.export-pdf', request()->query()) }}" class="btn-ghost-custom">
                <i class="fas fa-file-pdf me-1"></i> PDF
            </a>
            @can('fees.collect')
            <a href="{{ route('fees.refunds.create') }}" class="btn-primary-custom">
                <i class="fas fa-plus me-1"></i> Request Refund
            </a>
            @endcan
        </div>
    </div>

    <div class="filter-bar mb-4">
        <form action="{{ route('fees.refunds.index') }}" method="GET" class="filter-form">
            <div class="filter-field">
                <label for="status">Status</label>
                <select name="status" id="status" class="filter-select">
                    <option value="">All Statuses</option>
                    @foreach(['requested','approved','rejected','completed'] as $s)
                        <option value="{{ $s }}" {{ (request('status') ?? '') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn-primary-custom"><i class="fas fa-filter me-1"></i> Filter</button>
                <a href="{{ route('fees.refunds.index') }}" class="btn-ghost-custom">Clear</a>
            </div>
        </form>
    </div>

    <div class="metrics-grid mb-4">
        <div class="metric-card">
            <div class="metric-icon bg-amber-light text-amber"><i class="fas fa-hourglass-half"></i></div>
            <div class="metric-content">
                <span class="metric-label">Requested</span>
                <span class="metric-value text-amber">KSh {{ number_format($metrics['requested'], 0) }}</span>
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-icon bg-indigo-light text-indigo"><i class="fas fa-check-double"></i></div>
            <div class="metric-content">
                <span class="metric-label">Approved</span>
                <span class="metric-value text-indigo">KSh {{ number_format($metrics['approved'], 0) }}</span>
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-icon bg-emerald-light text-emerald"><i class="fas fa-check-circle"></i></div>
            <div class="metric-content">
                <span class="metric-label">Completed</span>
                <span class="metric-value text-emerald">KSh {{ number_format($metrics['completed'], 0) }}</span>
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-icon bg-rose-light text-rose"><i class="fas fa-times-circle"></i></div>
            <div class="metric-content">
                <span class="metric-label">Rejected</span>
                <span class="metric-value text-rose">KSh {{ number_format($metrics['rejected'], 0) }}</span>
            </div>
        </div>
    </div>

    <div class="report-card">
        <div class="card-header-custom">
            <div class="card-title-group"><i class="fas fa-list"></i><span>Refund Register ({{ $refunds->total() }})</span></div>
        </div>
        <div class="table-section">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student</th>
                        <th class="text-right">Amount</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Requested</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($refunds as $refund)
                        <tr>
                            <td><span class="mono-sm">#{{ $refund->id }}</span></td>
                            <td class="font-semibold">{{ $refund->student->full_name ?? 'N/A' }} <span class="mono-sm text-muted">{{ $refund->student->admission_no ?? '' }}</span></td>
                            <td class="text-right mono font-semibold">KSh {{ number_format($refund->amount, 2) }}</td>
                            <td class="text-muted" style="max-width:260px;">{{ \Illuminate\Support\Str::limit($refund->reason, 60) }}</td>
                            <td>
                                @php
                                    $badge = match($refund->status) {
                                        'requested' => 'status-requested',
                                        'approved' => 'status-approved',
                                        'rejected' => 'status-rejected',
                                        default => 'status-completed',
                                    };
                                @endphp
                                <span class="status-badge {{ $badge }}">{{ $refund->status_label }}</span>
                            </td>
                            <td class="text-muted">{{ $refund->requested_at ? $refund->requested_at->format('d M Y') : 'N/A' }}</td>
                            <td class="text-center">
                                <a href="{{ route('fees.refunds.show', $refund->id) }}" class="btn-ghost-custom btn-xs">
                                    <i class="fas fa-eye me-1"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="empty-cell"><div class="empty-mini"><i class="fas fa-receipt"></i><p>No refunds found.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="table-footer">{{ $refunds->appends(request()->query())->links() }}</div>
        </div>
    </div>
</div>

<style>
:root {
    --indigo: #4f46e5; --indigo-light: #eef2ff; --amber: #f59e0b; --amber-600: #d97706; --amber-light: #fffbeb;
    --emerald: #10b981; --emerald-light: #ecfdf5; --rose: #f43f5e; --rose-light: #fff1f2;
    --slate-50: #f8fafc; --slate-100: #f1f5f9; --slate-200: #e2e8f0; --slate-300: #cbd5e1; --slate-400: #94a3b8;
    --slate-500: #64748b; --slate-600: #475569; --slate-700: #334155; --slate-800: #1e293b; --slate-900: #0f172a;
    --border: #e2e8f0; --ease-out: cubic-bezier(0.23,1,0.32,1);
}
.report-wrap { padding: 1.5rem 2rem; background: #f9fafb; min-height: 100vh; }
.page-title { font-size: 1.25rem; font-weight: 900; color: var(--slate-900); }
.page-subtitle { color: var(--slate-400); font-size: 0.8rem; font-weight: 500; }
.mono { font-family: monospace; font-size: 0.8rem; } .mono-sm { font-family: monospace; font-size: 0.75rem; font-weight: 600; }
.font-semibold { font-weight: 700; } .text-muted { color: var(--slate-400); } .text-right { text-align: right; } .text-center { text-align: center; }
.text-emerald { color: var(--emerald); } .text-rose { color: var(--rose); } .text-amber { color: var(--amber-600); } .text-indigo { color: var(--indigo); }
.icon-box { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; }
.bg-indigo-light { background: var(--indigo-light); } .bg-amber-light { background: var(--amber-light); } .bg-emerald-light { background: var(--emerald-light); } .bg-rose-light { background: var(--rose-light); }
.btn-primary-custom { display:inline-flex; align-items:center; padding:.5rem 1.25rem; border-radius:8px; font-size:.75rem; font-weight:800; border:none; text-decoration:none!important; cursor:pointer; background:var(--emerald); color:#fff; }
.btn-ghost-custom { display:inline-flex; align-items:center; padding:.5rem 1.25rem; border-radius:8px; font-size:.75rem; font-weight:700; text-decoration:none!important; cursor:pointer; background:#fff; border:1px solid var(--border); color:var(--slate-700); }
.btn-xs { padding:.3rem .75rem; font-size:.68rem; }
.filter-bar { background:#fff; border:1px solid var(--border); border-radius:12px; padding:1rem 1.25rem; }
.filter-form { display:flex; gap:1rem; align-items:flex-end; flex-wrap:wrap; }
.filter-field { display:flex; flex-direction:column; gap:.35rem; }
.filter-field label { font-size:.68rem; font-weight:700; color:var(--slate-500); text-transform:uppercase; }
.filter-select { padding:.5rem .75rem; border:1px solid var(--border); border-radius:8px; font-size:.8rem; color:var(--slate-700); background:#fff; }
.filter-actions { display:flex; gap:.5rem; align-items:flex-end; }
.metrics-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:1rem; }
.metric-card { background:#fff; border:1px solid var(--border); border-radius:12px; padding:1rem 1.25rem; display:flex; align-items:center; gap:1rem; }
.metric-icon { width:42px; height:42px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1.1rem; flex-shrink:0; }
.metric-content { display:flex; flex-direction:column; }
.metric-label { font-size:.7rem; font-weight:700; color:var(--slate-400); text-transform:uppercase; }
.metric-value { font-size:1.05rem; font-weight:900; color:var(--slate-900); font-family:monospace; }
.report-card { background:#fff; border:1px solid var(--border); border-radius:12px; overflow:hidden; }
.card-header-custom { padding:.9rem 1.25rem; border-bottom:1px solid var(--border); background:var(--slate-50); }
.card-title-group { display:flex; align-items:center; gap:.5rem; font-size:.78rem; font-weight:800; text-transform:uppercase; color:var(--slate-500); }
.table-section { overflow-x:auto; }
.data-table { width:100%; border-collapse:collapse; }
.data-table th { padding:.75rem 1rem; font-size:.7rem; font-weight:800; color:var(--slate-400); text-transform:uppercase; text-align:left; border-bottom:1px solid var(--border); background:var(--slate-50); }
.data-table td { padding:.75rem 1rem; border-bottom:1px solid var(--slate-100); vertical-align:middle; font-size:.82rem; color:var(--slate-700); }
.data-table tbody tr:hover { background:var(--slate-50); }
.status-badge { display:inline-block; padding:3px 10px; border-radius:5px; font-size:.7rem; font-weight:700; }
.status-requested { background:var(--amber-light); color:var(--amber-600); }
.status-approved { background:var(--indigo-light); color:var(--indigo); }
.status-rejected { background:var(--rose-light); color:var(--rose); }
.status-completed { background:var(--emerald-light); color:var(--emerald); }
.table-footer { padding:1rem 1.25rem; border-top:1px solid var(--border); }
.empty-mini { text-align:center; color:var(--slate-300); }
.empty-mini i { font-size:1.5rem; margin-bottom:.5rem; display:block; }
.empty-mini p { font-size:.82rem; color:var(--slate-400); margin:0; }

@media (max-width:768px) {
    .report-wrap { padding:1rem; }
    .d-flex.align-items-center.justify-content-between.mb-4 { flex-direction:column; align-items:flex-start!important; gap:0.75rem; }
    .d-flex.align-items-center.justify-content-between.mb-4 > .d-flex { width:100%; flex-wrap:wrap; gap:0.5rem; }
    .d-flex.align-items-center.justify-content-between.mb-4 .btn-ghost-custom,
    .d-flex.align-items-center.justify-content-between.mb-4 .btn-primary-custom { flex:1; justify-content:center; padding:0.5rem 0.625rem; font-size:0.7rem; }
    .page-title { font-size:1.1rem; }
    .filter-form { flex-direction:column; gap:0.625rem; }
    .filter-field { width:100%; }
    .filter-select { width:100%; min-width:0; }
    .filter-actions { width:100%; }
    .filter-actions .btn-primary-custom, .filter-actions .btn-ghost-custom { flex:1; justify-content:center; }
    .metrics-grid { grid-template-columns:1fr 1fr; gap:0.625rem; }
    .metric-card { padding:0.75rem 1rem; }
    .metric-icon { width:36px; height:36px; font-size:0.9rem; }
    .metric-value { font-size:0.9rem; }
    .data-table th:nth-child(1), .data-table td:nth-child(1),
    .data-table th:nth-child(4), .data-table td:nth-child(4),
    .data-table th:nth-child(6), .data-table td:nth-child(6) { display:none; }
    .data-table th { padding:0.6rem 0.625rem; font-size:0.6rem; }
    .data-table td { padding:0.6rem 0.625rem; font-size:0.75rem; }
}

@media (max-width:420px) {
    .metrics-grid { grid-template-columns:1fr; }
    .icon-box { width:34px; height:34px; font-size:0.85rem; }
    .page-title { font-size:1rem; }
}
</style>
@endsection
