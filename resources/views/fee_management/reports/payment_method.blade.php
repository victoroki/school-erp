@extends('layouts.app')

@section('content')
<div class="report-wrap">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="icon-box bg-indigo-light text-indigo"><i class="fas fa-credit-card"></i></div>
            <div>
                <h1 class="page-title mb-0">Payment Methods</h1>
                <p class="page-subtitle mb-0">Collections grouped by payment method</p>
            </div>
        </div>
    </div>

    <div class="filter-bar mb-4">
        <form action="{{ route('fees.reports.payment-method') }}" method="GET" class="filter-form">
            <div class="filter-field">
                <label for="academic_year_id">Academic Year</label>
                <select name="academic_year_id" id="academic_year_id" class="filter-select">
                    <option value="">All Years</option>
                    @foreach($academicYears as $id => $name)
                        <option value="{{ $id }}" {{ $yearId == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-field">
                <label for="from">From</label>
                <input type="date" name="from" id="from" value="{{ request('from') }}" class="filter-select">
            </div>
            <div class="filter-field">
                <label for="to">To</label>
                <input type="date" name="to" id="to" value="{{ request('to') }}" class="filter-select">
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn-primary-custom"><i class="fas fa-filter me-1"></i> Filter</button>
                <a href="{{ route('fees.reports.payment-method') }}" class="btn-ghost-custom">Clear</a>
            </div>
        </form>
    </div>

    <div class="metrics-grid mb-4">
        <div class="metric-card">
            <div class="metric-icon bg-indigo-light text-indigo"><i class="fas fa-coins"></i></div>
            <div class="metric-content">
                <span class="metric-label">Grand Total</span>
                <span class="metric-value text-indigo">KSh {{ number_format($grandTotal, 2) }}</span>
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-icon bg-emerald-light text-emerald"><i class="fas fa-wallet"></i></div>
            <div class="metric-content">
                <span class="metric-label">Methods Used</span>
                <span class="metric-value text-emerald">{{ $byMethod->count() }}</span>
            </div>
        </div>
    </div>

    <div class="report-card">
        <div class="card-header-custom">
            <div class="card-title-group"><i class="fas fa-list"></i><span>Collection by Method</span></div>
        </div>
        <div class="table-section">
            <table class="data-table">
                <thead>
                    <tr><th>Payment Method</th><th class="text-right">Payments</th><th class="text-right">Share</th><th class="text-right">Total</th></tr>
                </thead>
                <tbody>
                    @forelse($byMethod as $m)
                        @php $share = $grandTotal > 0 ? round(($m->total / $grandTotal) * 100, 1) : 0; @endphp
                        <tr>
                            <td class="font-semibold">{{ ucwords(str_replace('_',' ', $m->payment_method)) }}</td>
                            <td class="text-right">{{ number_format($m->count) }}</td>
                            <td class="text-right text-muted">{{ $share }}%</td>
                            <td class="text-right mono font-semibold">KSh {{ number_format($m->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><div class="empty-mini"><i class="fas fa-inbox"></i><p>No payments found.</p></div></td></tr>
                    @endforelse
                </tbody>
                @if($byMethod->count())
                <tfoot>
                    <tr class="totals-row">
                        <td class="font-semibold">Total</td>
                        <td class="text-right">{{ number_format($byMethod->sum('count')) }}</td>
                        <td class="text-right">100%</td>
                        <td class="text-right mono font-semibold">KSh {{ number_format($grandTotal, 2) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

<style>
:root {
    --indigo:#4f46e5; --indigo-light:#eef2ff; --amber:#f59e0b; --amber-600:#d97706; --amber-light:#fffbeb;
    --emerald:#10b981; --emerald-light:#ecfdf5; --rose:#f43f5e; --rose-light:#fff1f2;
    --slate-50:#f8fafc; --slate-100:#f1f5f9; --slate-200:#e2e8f0; --slate-300:#cbd5e1; --slate-400:#94a3b8;
    --slate-500:#64748b; --slate-600:#475569; --slate-700:#334155; --slate-800:#1e293b; --slate-900:#0f172a;
    --border:#e2e8f0;
}
.report-wrap{padding:1.5rem 2rem;background:#f9fafb;min-height:100vh;}
.page-title{font-size:1.25rem;font-weight:900;color:var(--slate-900);}
.page-subtitle{color:var(--slate-400);font-size:.8rem;font-weight:500;}
.mono{font-family:monospace;font-size:.8rem;} .font-semibold{font-weight:700;} .text-muted{color:var(--slate-400);} .text-right{text-align:right;}
.text-emerald{color:var(--emerald);} .text-indigo{color:var(--indigo);}
.icon-box{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;}
.bg-indigo-light{background:var(--indigo-light);} .bg-emerald-light{background:var(--emerald-light);}
.btn-primary-custom{display:inline-flex;align-items:center;padding:.5rem 1.25rem;border-radius:8px;font-size:.75rem;font-weight:800;border:none;text-decoration:none!important;cursor:pointer;background:var(--emerald);color:#fff;}
.btn-ghost-custom{display:inline-flex;align-items:center;padding:.5rem 1.25rem;border-radius:8px;font-size:.75rem;font-weight:700;text-decoration:none!important;cursor:pointer;background:#fff;border:1px solid var(--border);color:var(--slate-700);}
.filter-bar{background:#fff;border:1px solid var(--border);border-radius:12px;padding:1rem 1.25rem;}
.filter-form{display:flex;gap:1rem;align-items:flex-end;flex-wrap:wrap;}
.filter-field{display:flex;flex-direction:column;gap:.35rem;}
.filter-field label{font-size:.68rem;font-weight:700;color:var(--slate-500);text-transform:uppercase;}
.filter-select{padding:.5rem .75rem;border:1px solid var(--border);border-radius:8px;font-size:.8rem;color:var(--slate-700);background:#fff;}
.filter-actions{display:flex;gap:.5rem;align-items:flex-end;}
.metrics-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;}
.metric-card{background:#fff;border:1px solid var(--border);border-radius:12px;padding:1rem 1.25rem;display:flex;align-items:center;gap:1rem;}
.metric-icon{width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;}
.metric-content{display:flex;flex-direction:column;}
.metric-label{font-size:.7rem;font-weight:700;color:var(--slate-400);text-transform:uppercase;}
.metric-value{font-size:1.05rem;font-weight:900;color:var(--slate-900);font-family:monospace;}
.report-card{background:#fff;border:1px solid var(--border);border-radius:12px;overflow:hidden;}
.card-header-custom{padding:.9rem 1.25rem;border-bottom:1px solid var(--border);background:var(--slate-50);}
.card-title-group{display:flex;align-items:center;gap:.5rem;font-size:.78rem;font-weight:800;text-transform:uppercase;color:var(--slate-500);}
.table-section{overflow-x:auto;}
.data-table{width:100%;border-collapse:collapse;}
.data-table th{padding:.75rem 1rem;font-size:.7rem;font-weight:800;color:var(--slate-400);text-transform:uppercase;text-align:left;border-bottom:1px solid var(--border);background:var(--slate-50);}
.data-table td{padding:.75rem 1rem;border-bottom:1px solid var(--slate-100);vertical-align:middle;font-size:.82rem;color:var(--slate-700);}
.data-table tbody tr:hover{background:var(--slate-50);}
.data-table tfoot .totals-row td{border-top:2px solid var(--border);background:var(--slate-50);}
.empty-mini{text-align:center;color:var(--slate-300);}
.empty-mini i{font-size:1.5rem;margin-bottom:.5rem;display:block;}
.empty-mini p{font-size:.82rem;color:var(--slate-400);margin:0;}

@media (max-width:768px) {
    .report-wrap { padding:1rem; }
    .d-flex.align-items-center.justify-content-between.mb-4 { flex-direction:column; align-items:flex-start!important; gap:0.75rem; }
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
