@extends('layouts.app')

@section('content')
<style>
:root {
    --indigo:#4f46e5; --emerald:#10b981; --rose:#f43f5e; --amber:#d97706;
    --slate-50:#f8fafc; --slate-100:#f1f5f9; --slate-200:#e2e8f0; --slate-400:#94a3b8;
    --slate-500:#64748b; --slate-600:#475569; --slate-700:#334155; --slate-800:#1e293b; --slate-900:#0f172a;
    --border:#e2e8f0;
}
.ig-wrap { padding:1.5rem 2rem; }
.ig-title { font-size:1.25rem; font-weight:900; color:var(--slate-900); margin:0; }
.ig-sub { color:var(--slate-400); font-size:.8rem; font-weight:500; margin:.25rem 0 0; }
.ig-note {
    background:#fff; border:1px solid var(--border); border-left:4px solid var(--indigo);
    border-radius:10px; padding:1rem 1.25rem; font-size:.85rem; color:var(--slate-700); margin:1.25rem 0 1.5rem;
}
.ig-card { background:#fff; border:1px solid var(--border); border-radius:12px; padding:1.25rem 1.5rem; margin-bottom:1.25rem; }
.ig-card-head { display:flex; align-items:flex-start; gap:.75rem; margin-bottom:.75rem; }
.ig-badge { font-size:.65rem; font-weight:800; letter-spacing:.05em; text-transform:uppercase; padding:.2rem .5rem; border-radius:6px; }
.ig-badge--danger { background:#fff1f2; color:#be123c; }
.ig-badge--warning { background:#fffbeb; color:var(--amber); }
.ig-card-title { font-size:.95rem; font-weight:800; color:var(--slate-900); margin:0; }
.ig-card-summary { font-size:.85rem; color:var(--slate-600); margin:.25rem 0 0; }
.ig-detail { list-style:none; padding:0; margin:.75rem 0 0; }
.ig-detail li { font-size:.82rem; color:var(--slate-700); padding:.6rem .75rem; background:var(--slate-50); border-radius:8px; margin-bottom:.5rem; }
.ig-records { font-size:.75rem; color:var(--slate-500); margin-top:.75rem; }
.ig-records code { background:var(--slate-100); padding:.1rem .35rem; border-radius:4px; color:var(--slate-700); }
.ig-empty { background:#fff; border:1px solid var(--border); border-radius:12px; padding:2.5rem; text-align:center; color:var(--slate-500); }
.ig-empty i { font-size:1.75rem; color:var(--emerald); display:block; margin-bottom:.75rem; }
@media (max-width:700px){ .ig-wrap{padding:1rem;} }
</style>

<div class="ig-wrap">
    <div class="d-flex align-items-center justify-content-between mb-2">
        <div>
            <h1 class="ig-title">Fee Integrity Report</h1>
            <p class="ig-sub">Financial data that needs a decision, not a code change</p>
        </div>
        <a href="{{ route('fees.dashboard') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Dashboard
        </a>
    </div>

    <div class="ig-note">
        <strong>Nothing on this page has been changed automatically.</strong>
        Every item below is a record that already exists in the database and that the system will not rewrite on its
        own, because re-pointing a posted financial movement — or deciding that an overpayment was really a grant — is
        an accounting decision. Balances already reflect these rows correctly; it is the history that needs a ruling.
    </div>

    @forelse($findings as $finding)
        <div class="ig-card">
            <div class="ig-card-head">
                <span class="ig-badge ig-badge--{{ $finding['severity'] }}">{{ $finding['severity'] === 'danger' ? 'Needs review' : 'For information' }}</span>
                <div>
                    <h2 class="ig-card-title">{{ $finding['title'] }}</h2>
                    <p class="ig-card-summary">{{ $finding['summary'] }}</p>
                </div>
            </div>

            <ul class="ig-detail">
                @foreach($finding['detail'] as $line)
                    <li>{{ $line }}</li>
                @endforeach
            </ul>

            <div class="ig-records">
                Records involved:
                @foreach($finding['records'] as $label => $ids)
                    <span class="me-2">{{ str_replace('_', ' ', $label) }}:
                        <code>{{ $ids === [] ? 'none' : implode(', ', $ids) }}</code>
                    </span>
                @endforeach
            </div>
        </div>
    @empty
        <div class="ig-empty">
            <i class="fas fa-check-circle"></i>
            <p class="mb-0">No fee data needs review. Refunds are within the amounts paid, and every refund ledger entry
                is posted in the correct direction.</p>
        </div>
    @endforelse
</div>
@endsection
