@extends('layouts.app')

@section('content')
<div class="dash-wrap">
    {{-- Header --}}
    <div class="row align-items-center mb-4">
        <div class="col-md-7">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('finance.dashboard') }}" class="btn-dash btn-ghost px-3 py-2">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="dash-heading">Bank Reconciliation</h1>
                    <p class="dash-sub">Match and reconcile your bank accounts against system records</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Bank Accounts List --}}
    <div class="row">
        <div class="col-12">
            <div class="dash-panel">
                <div class="dash-panel-header">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-university text-indigo"></i>
                        <h3 class="dash-panel-title">Available Bank Accounts</h3>
                    </div>
                </div>
                <div class="dash-panel-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="pl-4">Account Details</th>
                                    <th>Account Type</th>
                                    <th>Status</th>
                                    <th class="text-right">System Balance</th>
                                    <th class="text-right pr-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bankAccounts as $account)
                                    <tr>
                                        <td class="pl-4">
                                            <div class="font-weight-bold text-dark">{{ $account->account_name }}</div>
                                            <div class="text-muted small">{{ $account->bank_name }} &bull; {{ $account->account_number }}</div>
                                        </td>
                                        <td>
                                            <span class="badge-soft">{{ $account->account_type ?: 'Standard' }}</span>
                                        </td>
                                        <td>
                                            @if($account->status == 'active')
                                                <span class="badge-pill-soft bg-emerald-light text-emerald">Active</span>
                                            @else
                                                <span class="badge-pill-soft bg-slate-light text-slate">{{ ucfirst($account->status) }}</span>
                                            @endif
                                        </td>
                                        <td class="text-right font-weight-bold text-dark">
                                            {{ $account->currency ?: 'KES' }} {{ number_format($account->current_balance, 2) }}
                                        </td>
                                        <td class="text-right pr-4">
                                            <a href="{{ route('bank-reconciliations.show', $account->account_id) }}" class="btn-dash btn-indigo-dash px-3 py-1 text-sm">
                                                Reconcile
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No bank accounts found in the system.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* ── Emil Kowalski Utility Suite ── */
:root {
    --blue: #3b82f6; --blue-light: #eff6ff;
    --indigo: #4f46e5; --indigo-light: #eef2ff;
    --emerald: #10b981; --emerald-light: #ecfdf5;
    --amber: #f59e0b; --amber-light: #fffbeb;
    --amber-dark: #b45309;
    --rose: #f43f5e; --rose-light: #fff1f2;
    --slate: #64748b; --slate-light: #f1f5f9;
    --text: #0f172a;
    --muted: #64748b;
    --border: #e2e8f0;
    --ease-out: cubic-bezier(0.16, 1, 0.3, 1);
}

.bg-blue-light { background: var(--blue-light); } .text-blue { color: var(--blue); }
.bg-indigo-light { background: var(--indigo-light); } .text-indigo { color: var(--indigo); }
.bg-emerald-light { background: var(--emerald-light); } .text-emerald { color: var(--emerald); }
.bg-amber-light { background: var(--amber-light); } .text-amber { color: var(--amber); } .text-amber-dark { color: var(--amber-dark); }
.bg-rose-light { background: var(--rose-light); } .text-rose { color: var(--rose); }
.bg-slate-light { background: var(--slate-light); } .text-slate { color: var(--slate); }

.dash-wrap { padding: 1.5rem; }
.dash-heading { font-size: 1.5rem; font-weight: 800; color: var(--text); letter-spacing: -0.02em; margin-bottom: 0.25rem; }
.dash-sub { font-size: 0.875rem; color: var(--muted); font-weight: 500; margin-bottom: 0; }

.dash-panel { background: #fff; border: 1px solid var(--border); border-radius: 14px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); overflow: hidden; display: flex; flex-direction: column; }
.dash-panel-header { padding: 1.25rem 1.5rem; background: #fff; border-bottom: 1px solid #f8fafc; display: flex; align-items: center; justify-content: space-between; }
.dash-panel-title { font-size: 1rem; font-weight: 800; color: var(--text); margin: 0; }
.dash-panel-body { flex: 1; display: flex; flex-direction: column; }

.table { margin-bottom: 0; }
.table thead th { background: #f8fafc; border-bottom: 1px solid var(--border); border-top: 0; font-size: .688rem; font-weight: 800; text-transform: uppercase; color: var(--slate); letter-spacing: 0.05em; padding: .75rem 1.5rem; }
.table tbody td { padding: 1rem 1.5rem; vertical-align: middle; border-bottom: 1px solid #f1f5f9; border-top: 0; font-size: 0.875rem; }
.table tbody tr:last-child td { border-bottom: 0; }
.table-hover tbody tr:hover { background-color: #f8fafc; }

.badge-soft { background: #f1f5f9; color: #475569; font-size: .688rem; font-weight: 700; padding: .2rem .5rem; border-radius: 6px; }
.badge-pill-soft { font-size: .688rem; font-weight: 700; padding: .2rem .6rem; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.05em; display: inline-block; }

.btn-dash { display: inline-flex; align-items: center; justify-content: center; padding: .625rem 1rem; border-radius: 8px; font-size: .875rem; font-weight: 600; transition: all 150ms var(--ease-out); border: 1px solid transparent; text-decoration: none !important; cursor: pointer; }
.btn-ghost { background: transparent; color: var(--muted); border-color: transparent; border: 1px solid var(--border); }
.btn-ghost:hover { background: #f1f5f9; color: var(--text); border-color: #cbd5e1; }

.btn-indigo-dash { background: var(--indigo); color: #fff; }
.btn-indigo-dash:hover { background: #4338ca; color: #fff; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2); }
</style>
@endsection
