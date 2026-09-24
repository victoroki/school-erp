@extends('layouts.app')

@section('content')
<div class="dash-wrap">
    {{-- Header --}}
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('financial-reports.index') }}" class="btn-dash btn-ghost px-3 py-2">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="dash-heading">Balance Sheet</h1>
                    <p class="dash-sub">As at {{ \Carbon\Carbon::parse($endDate)->format('d M, Y') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <form action="{{ route('financial-reports.balance-sheet') }}" method="GET" class="d-flex justify-content-md-end gap-2 flex-wrap">
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}" style="max-width: 140px;">
                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}" style="max-width: 140px;">
                <button type="submit" class="btn-dash btn-indigo-dash">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                <button type="button" onclick="window.print()" class="btn-dash btn-slate-dash">
                    <i class="fas fa-print me-1"></i> Print
                </button>
            </form>
        </div>
    </div>

    {{-- Summary Stats --}}
    <div class="row mb-4">
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="stat-card">
                <div class="stat-icon bg-emerald-light text-emerald"><i class="fas fa-landmark"></i></div>
                <div class="stat-info w-100">
                    <span class="stat-label">Total Assets</span>
                    <span class="stat-value text-emerald">KES {{ number_format($totalAssets, 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="stat-card">
                <div class="stat-icon bg-rose-light text-rose"><i class="fas fa-exclamation-circle"></i></div>
                <div class="stat-info w-100">
                    <span class="stat-label">Total Liabilities</span>
                    <span class="stat-value text-rose">KES {{ number_format($totalLiabilities, 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card {{ $netAssets >= 0 ? 'border-emerald-light' : 'border-rose-light' }}">
                <div class="stat-icon {{ $netAssets >= 0 ? 'bg-indigo-light text-indigo' : 'bg-rose-light text-rose' }}">
                    <i class="fas fa-balance-scale"></i>
                </div>
                <div class="stat-info w-100">
                    <span class="stat-label">Net Assets (Equity)</span>
                    <span class="stat-value {{ $netAssets >= 0 ? 'text-indigo' : 'text-rose' }}">KES {{ number_format($netAssets, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Two-Column: Assets | Liabilities --}}
    <div class="row mb-4">
        {{-- ASSETS --}}
        <div class="col-lg-6 mb-4">
            <div class="dash-panel h-100">
                <div class="dash-panel-header bg-emerald-light border-0">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-plus-circle text-emerald"></i>
                        <h3 class="dash-panel-title text-emerald">Assets</h3>
                    </div>
                </div>
                <div class="dash-panel-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="pl-4">Description</th>
                                    <th class="text-right pr-4">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Bank Accounts --}}
                                <tr class="bg-slate-light">
                                    <td class="pl-4 font-weight-bold" colspan="2">
                                        <i class="fas fa-university me-2 text-slate"></i> Bank Accounts
                                    </td>
                                </tr>
                                @forelse($bankAccounts as $account)
                                    <tr>
                                        <td class="pl-4 pl-md-5 text-muted small">
                                            {{ $account->account_name }}
                                            @if($account->bank_name)
                                                <span class="text-slate opacity-50">— {{ $account->bank_name }}</span>
                                            @endif
                                        </td>
                                        <td class="text-right pr-4 font-weight-bold text-dark small">
                                            KES {{ number_format($account->current_balance, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center py-3 text-muted small">No active bank accounts.</td>
                                    </tr>
                                @endforelse
                                <tr>
                                    <td class="pl-4 pl-md-5 font-weight-bold">Total Bank Balances</td>
                                    <td class="text-right pr-4 font-weight-bold text-emerald">KES {{ number_format($totalBankBalance, 2) }}</td>
                                </tr>

                                {{-- Fee Receivables --}}
                                <tr class="bg-slate-light">
                                    <td class="pl-4 font-weight-bold" colspan="2">
                                        <i class="fas fa-graduation-cap me-2 text-slate"></i> Student Fee Receivables
                                    </td>
                                </tr>
                                <tr>
                                    <td class="pl-4 pl-md-5 text-muted small">Total Fees Billed</td>
                                    <td class="text-right pr-4 text-dark small">KES {{ number_format($totalBilled, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="pl-4 pl-md-5 text-muted small">Less: Fees Collected</td>
                                    <td class="text-right pr-4 text-muted small">(KES {{ number_format($totalPaidFees, 2) }})</td>
                                </tr>
                                <tr>
                                    <td class="pl-4 pl-md-5 font-weight-bold">Outstanding Fee Receivables</td>
                                    <td class="text-right pr-4 font-weight-bold text-emerald">KES {{ number_format($feeReceivables, 2) }}</td>
                                </tr>

                                {{-- Petty Cash --}}
                                <tr class="bg-slate-light">
                                    <td class="pl-4 font-weight-bold" colspan="2">
                                        <i class="fas fa-coins me-2 text-slate"></i> Petty Cash
                                    </td>
                                </tr>
                                <tr>
                                    <td class="pl-4 pl-md-5 font-weight-bold">Petty Cash on Hand</td>
                                    <td class="text-right pr-4 font-weight-bold text-emerald">KES {{ number_format($pettyCash, 2) }}</td>
                                </tr>

                                {{-- Total --}}
                                <tr class="border-top-thick">
                                    <td class="pl-4 font-weight-bold" style="font-size: 1.1rem;">TOTAL ASSETS</td>
                                    <td class="text-right pr-4 font-weight-bold text-emerald" style="font-size: 1.1rem;">KES {{ number_format($totalAssets, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- LIABILITIES --}}
        <div class="col-lg-6 mb-4">
            <div class="dash-panel h-100">
                <div class="dash-panel-header bg-rose-light border-0">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-minus-circle text-rose"></i>
                        <h3 class="dash-panel-title text-rose">Liabilities</h3>
                    </div>
                </div>
                <div class="dash-panel-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="pl-4">Description</th>
                                    <th class="text-right pr-4">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Pending / Approved Expenses --}}
                                <tr class="bg-slate-light">
                                    <td class="pl-4 font-weight-bold" colspan="2">
                                        <i class="fas fa-receipt me-2 text-slate"></i> Payables &amp; Accruals
                                    </td>
                                </tr>
                                <tr>
                                    <td class="pl-4 pl-md-5">
                                        Pending &amp; Approved Expenses (unpaid)
                                        <span class="badge badge-warning small ml-1">Payable</span>
                                    </td>
                                    <td class="text-right pr-4 font-weight-bold text-rose">KES {{ number_format($pendingExpenses, 2) }}</td>
                                </tr>

                                {{-- Pending Refunds --}}
                                <tr class="bg-slate-light">
                                    <td class="pl-4 font-weight-bold" colspan="2">
                                        <i class="fas fa-undo me-2 text-slate"></i> Student Refunds Payable
                                    </td>
                                </tr>
                                <tr>
                                    <td class="pl-4 pl-md-5">
                                        Approved Refunds (not yet disbursed)
                                        <span class="badge badge-info small ml-1">Pending Payout</span>
                                    </td>
                                    <td class="text-right pr-4 font-weight-bold text-rose">KES {{ number_format($pendingRefunds, 2) }}</td>
                                </tr>

                                {{-- Total Liabilities --}}
                                <tr class="border-top-thick">
                                    <td class="pl-4 font-weight-bold" style="font-size: 1.1rem;">TOTAL LIABILITIES</td>
                                    <td class="text-right pr-4 font-weight-bold text-rose" style="font-size: 1.1rem;">KES {{ number_format($totalLiabilities, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Net Assets (Equity) --}}
    <div class="row">
        <div class="col-12">
            <div class="dash-panel">
                <div class="dash-panel-header {{ $netAssets >= 0 ? 'bg-indigo-light' : 'bg-rose-light' }} border-0">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-balance-scale {{ $netAssets >= 0 ? 'text-indigo' : 'text-rose' }}"></i>
                        <h3 class="dash-panel-title {{ $netAssets >= 0 ? 'text-indigo' : 'text-rose' }}">Net Assets (Equity)</h3>
                    </div>
                </div>
                <div class="dash-panel-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <tbody>
                                <tr>
                                    <td class="pl-4 font-weight-bold" style="font-size: 1rem;">Total Assets</td>
                                    <td class="text-right pr-4 font-weight-bold text-emerald" style="font-size: 1rem;">KES {{ number_format($totalAssets, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="pl-4 font-weight-bold" style="font-size: 1rem;">Less: Total Liabilities</td>
                                    <td class="text-right pr-4 font-weight-bold text-rose" style="font-size: 1rem;">(KES {{ number_format($totalLiabilities, 2) }})</td>
                                </tr>
                                <tr class="{{ $netAssets >= 0 ? 'bg-indigo-light' : 'bg-rose-light' }} border-top-thick">
                                    <td class="pl-4 font-weight-bold {{ $netAssets >= 0 ? 'text-indigo' : 'text-rose' }}" style="font-size: 1.35rem;">
                                        NET ASSETS (EQUITY)
                                    </td>
                                    <td class="text-right pr-4 font-weight-bold {{ $netAssets >= 0 ? 'text-indigo' : 'text-rose' }}" style="font-size: 1.35rem;">
                                        KES {{ number_format($netAssets, 2) }}
                                    </td>
                                </tr>
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

.border-emerald-light { border-color: #34d399 !important; }
.border-rose-light { border-color: #fb7185 !important; }
.border-top-thick { border-top: 2px solid var(--border) !important; }

.dash-wrap { padding: 1.5rem; }
.dash-heading { font-size: 1.5rem; font-weight: 800; color: var(--text); letter-spacing: -0.02em; margin-bottom: 0.25rem; }
.dash-sub { font-size: 0.875rem; color: var(--muted); font-weight: 500; margin-bottom: 0; }

.dash-panel { background: #fff; border: 1px solid var(--border); border-radius: 14px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); overflow: hidden; display: flex; flex-direction: column; }
.dash-panel-header { padding: 1.25rem 1.5rem; background: #fff; border-bottom: 1px solid #f8fafc; display: flex; align-items: center; justify-content: space-between; }
.dash-panel-title { font-size: 1rem; font-weight: 800; color: var(--text); margin: 0; }
.dash-panel-body { flex: 1; display: flex; flex-direction: column; }

.stat-card { background: #fff; border: 1px solid var(--border); border-radius: 14px; padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.02); transition: all 200ms var(--ease-out); height: 100%; }
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 16px rgba(0,0,0,0.05); border-color: #cbd5e1; }
.stat-icon { min-width: 56px; width: 56px; height: 56px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
.stat-info { display: flex; flex-direction: column; }
.stat-label { font-size: .75rem; color: var(--muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.25rem; }
.stat-value { font-size: 1.75rem; font-weight: 800; color: var(--text); line-height: 1.1; }

.table { margin-bottom: 0; }
.table thead th { background: #f8fafc; border-bottom: 1px solid var(--border); border-top: 0; font-size: .688rem; font-weight: 800; text-transform: uppercase; color: var(--slate); letter-spacing: 0.05em; padding: .75rem 1.5rem; }
.table tbody td { padding: 1rem 1.5rem; vertical-align: middle; border-bottom: 1px solid #f1f5f9; border-top: 0; font-size: 0.875rem; }
.table tbody tr:last-child td { border-bottom: 0; }

.btn-dash { display: inline-flex; align-items: center; justify-content: center; padding: .625rem 1rem; border-radius: 8px; font-size: .875rem; font-weight: 600; transition: all 150ms var(--ease-out); border: 1px solid transparent; text-decoration: none !important; cursor: pointer; }
.btn-ghost { background: transparent; color: var(--muted); border-color: transparent; border: 1px solid var(--border); }
.btn-ghost:hover { background: #f1f5f9; color: var(--text); border-color: #cbd5e1; }
.btn-indigo-dash { background: var(--indigo); color: #fff; }
.btn-indigo-dash:hover { background: #4338ca; color: #fff; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(79,70,229,0.2); }
.btn-slate-dash { background: var(--slate); color: #fff; border: 1px solid var(--slate); }
.btn-slate-dash:hover { background: #475569; color: #fff; transform: translateY(-1px); }

@media print {
    .btn-dash, form { display: none !important; }
    .dash-wrap { padding: 0; }
    .stat-card:hover { transform: none; box-shadow: none; }
}
</style>
@endsection
