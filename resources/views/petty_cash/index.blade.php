@extends('layouts.app')

@section('content')
<div class="dash-wrap">

    {{-- Header --}}
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h1 class="dash-heading"><i class="fas fa-coins mr-2 text-warning"></i> Petty Cash</h1>
            <p class="dash-sub">Track small cash disbursements and top-ups</p>
        </div>
        <div class="col-md-6 text-md-right mt-3 mt-md-0">
            @can('finance.manage')
            <a href="{{ route('petty-cash.create') }}" class="btn btn-warning shadow-sm font-weight-bold">
                <i class="fas fa-plus mr-1"></i> Log Entry
            </a>
            @endcan
        </div>
    </div>

    @include('flash::message')

    {{-- Balance Summary Cards --}}
    <div class="row mb-4">
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="stat-card {{ $balance >= 0 ? 'border-warning-light' : 'border-rose-light' }}">
                <div class="stat-icon bg-amber-light text-amber"><i class="fas fa-coins"></i></div>
                <div class="stat-info w-100">
                    <span class="stat-label">Current Balance</span>
                    <span class="stat-value {{ $balance >= 0 ? 'text-amber' : 'text-rose' }}">
                        KES {{ number_format($balance, 2) }}
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="stat-card">
                <div class="stat-icon bg-emerald-light text-emerald"><i class="fas fa-arrow-down"></i></div>
                <div class="stat-info w-100">
                    <span class="stat-label">Total Top-Ups</span>
                    <span class="stat-value text-emerald">KES {{ number_format($totalCredits, 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon bg-rose-light text-rose"><i class="fas fa-arrow-up"></i></div>
                <div class="stat-info w-100">
                    <span class="stat-label">Total Disbursed</span>
                    <span class="stat-value text-rose">KES {{ number_format($totalDebits, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form action="{{ route('petty-cash.index') }}" method="GET" class="form-inline justify-content-end flex-wrap gap-2">
                <select name="type" class="form-control form-control-sm mr-2">
                    <option value="">All Types</option>
                    <option value="credit" {{ request('type') == 'credit' ? 'selected' : '' }}>Top-Up (Credit)</option>
                    <option value="debit" {{ request('type') == 'debit' ? 'selected' : '' }}>Disbursement (Debit)</option>
                </select>
                <input type="date" name="from" class="form-control form-control-sm mr-2"
                       value="{{ request('from') }}" placeholder="From">
                <input type="date" name="to" class="form-control form-control-sm mr-2"
                       value="{{ request('to') }}" placeholder="To">
                <button type="submit" class="btn btn-sm btn-primary shadow-sm mr-1">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>
                @if(request()->hasAny(['type','from','to']))
                    <a href="{{ route('petty-cash.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                @endif
            </form>
        </div>
    </div>

    {{-- Ledger Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <h3 class="card-title font-weight-bold mb-0">
                <i class="fas fa-list mr-2 text-warning"></i> Transaction Ledger
            </h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="pl-4">Date</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Reference</th>
                            <th class="text-right">Debit (Out)</th>
                            <th class="text-right">Credit (In)</th>
                            <th>Recorded By</th>
                            @can('finance.manage')
                            <th class="text-right pr-4">Actions</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entries as $entry)
                            <tr>
                                <td class="pl-4 font-weight-bold">{{ $entry->date->format('d M Y') }}</td>
                                <td>
                                    @if($entry->type === 'credit')
                                        <span class="badge badge-success">
                                            <i class="fas fa-arrow-down mr-1"></i> Top-Up
                                        </span>
                                    @else
                                        <span class="badge badge-danger">
                                            <i class="fas fa-arrow-up mr-1"></i> Disbursement
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $entry->description }}</td>
                                <td class="text-muted small">{{ $entry->reference ?? '—' }}</td>
                                <td class="text-right">
                                    @if($entry->type === 'debit')
                                        <span class="font-weight-bold text-danger">
                                            KES {{ number_format($entry->amount, 2) }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if($entry->type === 'credit')
                                        <span class="font-weight-bold text-success">
                                            KES {{ number_format($entry->amount, 2) }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="small text-muted">
                                    {{ $entry->recordedBy?->name ?? 'System' }}
                                    <br>
                                    <span class="text-muted" style="font-size:0.75rem;">
                                        {{ $entry->created_at?->format('d M Y H:i') }}
                                    </span>
                                </td>
                                @can('finance.manage')
                                <td class="text-right pr-4">
                                    <form action="{{ route('petty-cash.destroy', $entry->id) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Delete this entry? This cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                                @endcan
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-coins fa-2x text-warning d-block mb-2"></i>
                                    No petty cash entries found.
                                    @can('finance.manage')
                                        <br>
                                        <a href="{{ route('petty-cash.create') }}" class="btn btn-sm btn-warning mt-2">
                                            <i class="fas fa-plus mr-1"></i> Log First Entry
                                        </a>
                                    @endcan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($entries->hasPages())
        <div class="card-footer bg-white">
            <div class="float-right">
                {{ $entries->links('pagination::bootstrap-4') }}
            </div>
        </div>
        @endif
    </div>

</div>

<style>
:root {
    --amber: #f59e0b; --amber-light: #fffbeb;
    --emerald: #10b981; --emerald-light: #ecfdf5;
    --rose: #f43f5e; --rose-light: #fff1f2;
    --border: #e2e8f0;
    --muted: #64748b;
    --ease-out: cubic-bezier(0.16,1,0.3,1);
}
.dash-wrap { padding: 1.5rem; }
.dash-heading { font-size: 1.5rem; font-weight: 800; color: #0f172a; letter-spacing: -0.02em; margin-bottom: 0.25rem; }
.dash-sub { font-size: 0.875rem; color: var(--muted); font-weight: 500; margin-bottom: 0; }

.stat-card { background: #fff; border: 1px solid var(--border); border-radius: 14px; padding: 1.25rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.02); transition: all 200ms var(--ease-out); height: 100%; }
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 16px rgba(0,0,0,0.05); }
.stat-icon { min-width: 52px; width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; }
.stat-info { display: flex; flex-direction: column; }
.stat-label { font-size: .7rem; color: var(--muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.2rem; }
.stat-value { font-size: 1.5rem; font-weight: 800; color: #0f172a; line-height: 1.1; }

.bg-amber-light { background: var(--amber-light); } .text-amber { color: var(--amber); }
.bg-emerald-light { background: var(--emerald-light); } .text-emerald { color: var(--emerald); }
.bg-rose-light { background: var(--rose-light); } .text-rose { color: var(--rose); }
.border-warning-light { border-color: #fcd34d !important; }
.border-rose-light { border-color: #fb7185 !important; }

.table thead th { font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; border-top: 0; padding: 0.75rem 1rem; }
.table tbody td { vertical-align: middle; padding: 0.875rem 1rem; border-bottom: 1px solid #f1f5f9; border-top: 0; font-size: 0.875rem; }
.table tbody tr:last-child td { border-bottom: 0; }
</style>
@endsection
