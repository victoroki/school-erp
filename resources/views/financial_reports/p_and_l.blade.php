@extends('layouts.app')

@section('content')
<div class="dash-wrap">
    {{-- Header --}}
    <div class="row align-items-center mb-4">
        <div class="col-md-7">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('financial-reports.index') }}" class="btn-dash btn-ghost px-3 py-2">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="dash-heading">Profit & Loss Statement</h1>
                    <p class="dash-sub">Financial period: {{ \Carbon\Carbon::parse($startDate)->format('d M, Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d M, Y') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-5 text-md-end mt-3 mt-md-0">
            <form action="{{ route('financial-reports.p-and-l') }}" method="GET" class="d-flex justify-content-md-end gap-2">
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}" style="max-width: 140px;">
                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}" style="max-width: 140px;">
                <button type="submit" class="btn-dash btn-indigo-dash">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
            </form>
        </div>
    </div>

    @php
        $netProfit = $totalIncome - $totalExpenses;
    @endphp

    {{-- Stats Row --}}
    <div class="row mb-4">
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="stat-card">
                <div class="stat-icon bg-emerald-light text-emerald"><i class="fas fa-chart-line"></i></div>
                <div class="stat-info w-100">
                    <span class="stat-label">Total Revenue</span>
                    <span class="stat-value text-emerald">KES {{ number_format($totalIncome, 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="stat-card">
                <div class="stat-icon bg-rose-light text-rose"><i class="fas fa-calculator"></i></div>
                <div class="stat-info w-100">
                    <span class="stat-label">Total Expenses</span>
                    <span class="stat-value text-rose">KES {{ number_format($totalExpenses, 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card {{ $netProfit >= 0 ? 'border-emerald-light' : 'border-rose-light' }}">
                <div class="stat-icon {{ $netProfit >= 0 ? 'bg-indigo-light text-indigo' : 'bg-rose-light text-rose' }}">
                    <i class="fas {{ $netProfit >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                </div>
                <div class="stat-info w-100">
                    <span class="stat-label">Net Profit / Loss</span>
                    <span class="stat-value {{ $netProfit >= 0 ? 'text-indigo' : 'text-rose' }}">KES {{ number_format($netProfit, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Statement --}}
    <div class="row">
        <div class="col-lg-12">
            <div class="dash-panel">
                <div class="dash-panel-header">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-file-invoice-dollar text-slate"></i>
                        <h3 class="dash-panel-title">Statement Breakdown</h3>
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
                                <tr class="bg-emerald-light border-0">
                                    <td class="pl-4 font-weight-bold text-emerald" style="font-size: 1.1rem;" colspan="2"><i class="fas fa-plus-circle me-2"></i> Revenue</td>
                                </tr>
                                <tr>
                                    <td class="pl-4 pl-md-5 font-weight-bold">Total Operating Revenue (Fees & Income)</td>
                                    <td class="text-right pr-4 font-weight-bold text-emerald">KES {{ number_format($totalIncome, 2) }}</td>
                                </tr>
                                
                                <tr class="bg-rose-light border-0 mt-3">
                                    <td class="pl-4 font-weight-bold text-rose" style="font-size: 1.1rem;" colspan="2"><i class="fas fa-minus-circle me-2"></i> Operating Expenses</td>
                                </tr>
                                @forelse($expenseBreakdown as $expense)
                                    <tr>
                                        <td class="pl-4 pl-md-5">
                                            <i class="fas fa-tags me-2 text-slate opacity-50"></i>
                                            {{ $expense->category ? $expense->category->name : 'Uncategorized Expenses' }}
                                        </td>
                                        <td class="text-right pr-4 font-weight-bold text-rose">KES {{ number_format($expense->total, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center py-4 text-muted">No expenses recorded for this period.</td>
                                    </tr>
                                @endforelse
                                <tr>
                                    <td class="pl-4 pl-md-5 font-weight-bold text-dark border-top-thick">Total Operating Expenses</td>
                                    <td class="text-right pr-4 font-weight-bold text-rose border-top-thick">KES {{ number_format($totalExpenses, 2) }}</td>
                                </tr>

                                <tr class="{{ $netProfit >= 0 ? 'bg-indigo-light' : 'bg-rose-light' }} border-0 mt-4">
                                    <td class="pl-4 font-weight-bold {{ $netProfit >= 0 ? 'text-indigo' : 'text-rose' }}" style="font-size: 1.25rem;">
                                        NET {{ $netProfit >= 0 ? 'PROFIT' : 'LOSS' }}
                                    </td>
                                    <td class="text-right pr-4 font-weight-bold {{ $netProfit >= 0 ? 'text-indigo' : 'text-rose' }}" style="font-size: 1.25rem;">
                                        KES {{ number_format($netProfit, 2) }}
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
.btn-indigo-dash:hover { background: #4338ca; color: #fff; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2); }
</style>
@endsection
