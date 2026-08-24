@extends('layouts.app')

@section('content')
<div class="dash-wrap">
    {{-- ① HEADER --}}
    <div class="row align-items-center mb-4">
        <div class="col-md-5">
            <h1 class="dash-heading">Financial Dashboard</h1>
            <p class="dash-sub">Monitor revenue, expenses, and cash flow</p>
        </div>
        <div class="col-md-7 text-md-end mt-3 mt-md-0">
            <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                <a href="{{ route('income.create') }}" class="btn-dash btn-emerald-dash">
                    <i class="fas fa-plus me-1"></i> Record Income
                </a>
                <a href="{{ route('expenses.create') }}" class="btn-dash btn-rose-dash">
                    <i class="fas fa-minus me-1"></i> Record Expense
                </a>
                <div class="dropdown d-inline-block">
                    <button class="btn-dash btn-ghost dropdown-toggle" type="button" id="reportMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="padding-right: 2rem;">
                        <i class="fas fa-file-invoice me-1"></i> Reports
                    </button>
                    <div class="dropdown-menu dropdown-menu-right shadow-sm border-0 border-radius-lg" aria-labelledby="reportMenu">
                        <a class="dropdown-item text-muted text-sm font-weight-bold" href="{{ route('financial-reports.cashflow') }}"><i class="fas fa-chart-line mr-2 text-primary"></i> Cashflow Statement</a>
                        <a class="dropdown-item text-muted text-sm font-weight-bold" href="{{ route('financial-reports.p-and-l') }}"><i class="fas fa-calculator mr-2 text-success"></i> Profit & Loss</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-muted text-sm font-weight-bold" href="{{ route('financial-reports.index') }}"><i class="fas fa-file-alt mr-2 text-info"></i> More Reports</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ② QUICK STATS --}}
    <div class="row mb-4">
        <div class="col-12 col-sm-6 col-md-3 mb-3 mb-md-0">
            <div class="stat-card">
                <div class="stat-icon bg-emerald-light text-emerald"><i class="fas fa-arrow-down"></i></div>
                <div class="stat-info w-100">
                    <span class="stat-label">Income (This Month)</span>
                    <span class="stat-value text-emerald" style="font-size: 1.25rem;">KES {{ number_format($combinedIncomeThisMonth, 0) }}</span>
                    <span class="mt-1 d-block text-muted" style="font-size: .688rem; font-weight: 600;">
                        <i class="fas {{ $incomeChange >= 0 ? 'fa-arrow-up text-emerald' : 'fa-arrow-down text-rose' }} mr-1"></i>
                        <b class="{{ $incomeChange >= 0 ? 'text-emerald' : 'text-rose' }}">{{ abs(round($incomeChange, 1)) }}%</b> from last month
                    </span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3 mb-3 mb-md-0">
            <div class="stat-card">
                <div class="stat-icon bg-rose-light text-rose"><i class="fas fa-arrow-up"></i></div>
                <div class="stat-info w-100">
                    <span class="stat-label">Expenses (This Month)</span>
                    <span class="stat-value text-rose" style="font-size: 1.25rem;">KES {{ number_format($totalExpensesThisMonth, 0) }}</span>
                    <span class="mt-1 d-block text-muted" style="font-size: .688rem; font-weight: 600;">
                        <i class="fas {{ $expenseChange <= 0 ? 'fa-arrow-down text-emerald' : 'fa-arrow-up text-rose' }} mr-1"></i>
                        <b class="{{ $expenseChange <= 0 ? 'text-emerald' : 'text-rose' }}">{{ abs(round($expenseChange, 1)) }}%</b> from last month
                    </span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3 mb-3 mb-md-0">
            <div class="stat-card">
                <div class="stat-icon bg-blue-light text-blue"><i class="fas fa-chart-line"></i></div>
                <div class="stat-info w-100">
                    <span class="stat-label">Net Cash Flow</span>
                    <span class="stat-value {{ $netCashFlow >= 0 ? 'text-emerald' : 'text-rose' }}" style="font-size: 1.25rem;">KES {{ number_format($netCashFlow, 0) }}</span>
                    <span class="mt-1 d-block text-muted" style="font-size: .688rem; font-weight: 600;">
                        <b>{{ round($cashFlowPercentage, 1) }}%</b> of total income
                    </span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="stat-card border-warning-light">
                <div class="stat-icon bg-amber-light text-amber"><i class="fas fa-university"></i></div>
                <div class="stat-info w-100">
                    <span class="stat-label">Total Bank Balance</span>
                    <span class="stat-value {{ $totalBankBalance > 100000 ? 'text-dark' : 'text-amber-dark' }}" style="font-size: 1.25rem;">KES {{ number_format($totalBankBalance, 0) }}</span>
                    <span class="mt-1 d-block {{ $lowBalanceAccounts > 0 ? 'text-rose' : 'text-muted' }}" style="font-size: .688rem; font-weight: 600;">
                        <i class="fas fa-exclamation-circle mr-1"></i>
                        <b>{{ $lowBalanceAccounts }}</b> Accounts low balance
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Content Column -->
        <div class="col-lg-8 mb-4 mb-lg-0">
            <!-- Main Chart -->
            <div class="dash-panel mb-4">
                <div class="dash-panel-header">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-chart-area text-indigo"></i>
                        <h3 class="dash-panel-title">Income vs Expenses (Last 6 Months)</h3>
                    </div>
                </div>
                <div class="dash-panel-body p-3">
                    <div class="chart-container" style="position: relative; height:320px; width:100%">
                        <canvas id="financeChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="dash-panel">
                <div class="dash-panel-header">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-history text-slate"></i>
                        <h3 class="dash-panel-title">Recent Transactions</h3>
                    </div>
                    <a href="{{ route('expenses.index') }}" class="btn-dash btn-ghost py-1 px-2" style="font-size: .688rem;">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="pl-3">Date</th>
                                <th>Description</th>
                                <th>Category</th>
                                <th class="text-right">Amount</th>
                                <th class="text-center pr-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentExpenses->take(5) as $expense)
                                <tr>
                                    <td class="pl-3 text-muted" style="font-size: .813rem;">{{ $expense->expense_date->format('d M, Y') }}</td>
                                    <td>
                                        <div class="font-weight-bold text-dark" style="font-size: .875rem;">{{ $expense->description ?: 'No description' }}</div>
                                        <div class="text-muted" style="font-size: .688rem;">{{ $expense->payment_method }} • {{ $expense->reference_number ?: 'N/A' }}</div>
                                    </td>
                                    <td><span class="badge-soft">{{ $expense->category ? $expense->category->name : 'General' }}</span></td>
                                    <td class="text-right text-rose font-weight-bold" style="font-size: .875rem;">- KES {{ number_format($expense->amount, 0) }}</td>
                                    <td class="text-center pr-3">
                                        @php
                                            $statusBadge = [
                                                'draft' => 'bg-slate-light text-slate',
                                                'pending' => 'bg-amber-light text-amber-dark',
                                                'approved' => 'bg-indigo-light text-indigo',
                                                'paid' => 'bg-emerald-light text-emerald',
                                                'rejected' => 'bg-rose-light text-rose'
                                            ][$expense->status] ?? 'bg-slate-light text-slate';
                                        @endphp
                                        <span class="badge-pill-soft {{ $statusBadge }}">{{ $expense->status }}</span>
                                    </td>
                                </tr>
                            @endforeach
                            @foreach($recentIncome->take(5) as $income)
                                <tr>
                                    <td class="pl-3 text-muted" style="font-size: .813rem;">{{ $income->income_date->format('d M, Y') }}</td>
                                    <td>
                                        <div class="font-weight-bold text-dark" style="font-size: .875rem;">{{ $income->payer_name ?: 'Unknown Payer' }}</div>
                                        <div class="text-muted" style="font-size: .688rem;">{{ $income->description ?: 'Other Income' }}</div>
                                    </td>
                                    <td><span class="badge-soft">{{ $income->category ? $income->category->name : 'General' }}</span></td>
                                    <td class="text-right text-emerald font-weight-bold" style="font-size: .875rem;">+ KES {{ number_format($income->amount, 0) }}</td>
                                    <td class="text-center pr-3">
                                        <span class="badge-pill-soft bg-emerald-light text-emerald">Active</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar Column -->
        <div class="col-lg-4">
            <!-- Pending Approvals Widget -->
            <div class="dash-panel mb-4 border-warning-light">
                <div class="dash-panel-header bg-amber-light border-0">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-clock text-amber-dark"></i>
                        <h3 class="dash-panel-title text-amber-dark">Pending Approvals</h3>
                    </div>
                </div>
                <div class="dash-panel-body text-center py-4">
                    <h2 class="font-weight-bold mb-0 text-dark" style="font-size: 2.5rem; line-height: 1;">{{ $pendingApprovalsCount }}</h2>
                    <p class="text-muted mb-3 font-weight-bold text-uppercase" style="font-size: .688rem; letter-spacing: 0.05em;">Expenses awaiting approval</p>
                    <h4 class="text-amber-dark font-weight-bold mb-4">KES {{ number_format($pendingApprovalsAmount, 0) }}</h4>
                    <a href="{{ route('expenses.pending') }}" class="btn-dash btn-amber-dash w-100 d-block">
                        <i class="fas fa-check-circle me-1"></i> Review Requests
                    </a>
                </div>
            </div>

            <!-- Alerts & Insights -->
            <div class="dash-panel mb-4">
                <div class="dash-panel-header">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-lightbulb text-blue"></i>
                        <h3 class="dash-panel-title">Financial Insights</h3>
                    </div>
                </div>
                <div class="dash-panel-body p-0">
                    <div class="insight-list">
                        @if($lowBalanceAccounts > 0)
                            <div class="insight-item d-flex gap-3 p-3 border-bottom">
                                <div class="insight-icon bg-rose-light text-rose"><i class="fas fa-exclamation-triangle"></i></div>
                                <div>
                                    <b class="d-block text-dark" style="font-size: .813rem;">Low Bank Balance</b>
                                    <span class="text-muted" style="font-size: .75rem;">{{ $lowBalanceAccounts }} account(s) have dropped below their minimum threshold.</span>
                                </div>
                            </div>
                        @endif
                        <div class="insight-item d-flex gap-3 p-3 border-bottom">
                            <div class="insight-icon bg-emerald-light text-emerald"><i class="fas fa-calendar-check"></i></div>
                            <div>
                                <b class="d-block text-dark" style="font-size: .813rem;">Current Cash Runway</b>
                                @php
                                    $avgExpense = $totalExpensesThisMonth > 0 ? $totalExpensesThisMonth : ($totalExpensesLastMonth ?: 1);
                                    $runway = $totalBankBalance / $avgExpense;
                                @endphp
                                <span class="text-muted" style="font-size: .75rem;">Enough cash to operate for approx. <b class="text-dark">{{ round($runway, 1) }} months</b> at current burn rate.</span>
                            </div>
                        </div>
                        <div class="insight-item d-flex gap-3 p-3">
                            <div class="insight-icon bg-blue-light text-blue"><i class="fas fa-info-circle"></i></div>
                            <div>
                                <b class="d-block text-dark" style="font-size: .813rem;">Fee Collection Status</b>
                                <span class="text-muted" style="font-size: .75rem;">Total fees collected this month: KES {{ number_format($combinedIncomeThisMonth - $totalIncomeThisMonth, 0) }}.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="dash-panel">
                <div class="dash-panel-header">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-bolt text-indigo"></i>
                        <h3 class="dash-panel-title">Quick Actions</h3>
                    </div>
                </div>
                <div class="dash-panel-body p-2">
                    <div class="row m-0">
                        <div class="col-6 p-1">
                            <a href="{{ route('bank-reconciliations.index') }}" class="qa-btn">
                                <i class="fas fa-balance-scale mb-1 text-blue"></i> Reconcile
                            </a>
                        </div>
                        <div class="col-6 p-1">
                            <a href="{{ route('budgets.index') }}" class="qa-btn">
                                <i class="fas fa-clipboard-list mb-1 text-emerald"></i> Budgets
                            </a>
                        </div>
                        @if(auth()->user()->hasAnyRole(['Super Admin']))
                        <div class="col-6 p-1">
                            <a href="{{ route('audit-trail.index') }}" class="qa-btn">
                                <i class="fas fa-history mb-1 text-slate"></i> Audit Log
                            </a>
                        </div>
                        @endif
                        <div class="col-6 p-1">
                            <a href="{{ route('financial-years.index') }}" class="qa-btn">
                                <i class="fas fa-calendar-alt mb-1 text-indigo"></i> Setup Year
                            </a>
                        </div>
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

.border-warning-light { border-color: #fcd34d !important; }
.border-bottom { border-bottom: 1px solid var(--border) !important; }

.dash-wrap { padding: 1rem; }
.dash-heading { font-size: 1.375rem; font-weight: 800; color: var(--text); letter-spacing: -0.02em; margin-bottom: 0.125rem; }
.dash-sub { font-size: 0.813rem; color: var(--muted); font-weight: 500; margin-bottom: 0; }

.dash-panel { background: #fff; border: 1px solid var(--border); border-radius: 14px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); overflow: hidden; display: flex; flex-direction: column; }
.dash-panel-header { padding: 1rem 1.25rem; background: #fff; border-bottom: 1px solid #f8fafc; display: flex; align-items: center; justify-content: space-between; }
.dash-panel-title { font-size: .875rem; font-weight: 800; color: var(--text); margin: 0; }
.dash-panel-body { padding: 1.25rem; flex: 1; }

/* Quick Stats */
.stat-card { background: #fff; border: 1px solid var(--border); border-radius: 12px; padding: 1.25rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.02); transition: all 200ms var(--ease-out); height: 100%; }
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 12px rgba(0,0,0,0.05); border-color: #cbd5e1; }
.stat-icon { min-width: 48px; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
.stat-info { display: flex; flex-direction: column; }
.stat-label { font-size: .7rem; color: var(--muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; }
.stat-value { font-size: 1.5rem; font-weight: 800; color: var(--text); line-height: 1.1; margin-top: .25rem; }

/* Table Styling */
.table { margin-bottom: 0; }
.table thead th { background: #f8fafc; border-bottom: 1px solid var(--border); font-size: .688rem; font-weight: 800; text-transform: uppercase; color: var(--slate); letter-spacing: 0.05em; padding: .625rem 1.25rem; }
.table tbody td { padding: .75rem 1.25rem; vertical-align: middle; border-bottom: 1px solid #f8fafc; border-top: 0; }
.table tbody tr:last-child td { border-bottom: 0; }
.table-hover tbody tr:hover { background-color: #f8fafc; }

.badge-soft { background: #f1f5f9; color: #475569; font-size: .688rem; font-weight: 700; padding: .2rem .5rem; border-radius: 6px; }
.badge-pill-soft { font-size: .688rem; font-weight: 700; padding: .2rem .6rem; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.05em; display: inline-block; }

/* Quick Actions */
.qa-btn { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 1rem .5rem; background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 10px; color: var(--slate); text-decoration: none !important; transition: all 150ms var(--ease-out); font-size: .75rem; font-weight: 600; text-align: center; }
.qa-btn i { font-size: 1.25rem; transition: all 150ms ease; }
.qa-btn:hover { background: #fff; border-color: #cbd5e1; color: var(--text); box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
.qa-btn:hover i { transform: translateY(-2px); }

/* Buttons */
.btn-dash { display: inline-flex; align-items: center; justify-content: center; padding: .5rem .875rem; border-radius: 8px; font-size: .813rem; font-weight: 600; transition: all 150ms var(--ease-out); border: 1px solid transparent; text-decoration: none !important; cursor: pointer; }
.btn-ghost { background: transparent; color: var(--muted); border-color: transparent; border: 1px solid var(--border); }
.btn-ghost:hover { background: #f1f5f9; color: var(--text); border-color: #cbd5e1; }

.btn-emerald-dash { background: var(--emerald); color: #fff; }
.btn-emerald-dash:hover { background: #059669; color: #fff; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2); }

.btn-rose-dash { background: var(--rose); color: #fff; }
.btn-rose-dash:hover { background: #e11d48; color: #fff; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(244, 63, 94, 0.2); }

.btn-amber-dash { background: var(--amber); color: #fff; }
.btn-amber-dash:hover { background: var(--amber-dark); color: #fff; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2); }

/* Insights List */
.insight-item { transition: background 150ms ease; }
.insight-item:hover { background: #f8fafc; }
.insight-icon { min-width: 36px; width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; }

/* Custom Dropdown Overrides for Bootstrap 4 */
.dropdown-menu { border-radius: 12px !important; padding: .5rem !important; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important; border: 1px solid var(--border) !important; }
.dropdown-item { border-radius: 8px; padding: .5rem 1rem; transition: all 150ms ease; font-size: .813rem; font-weight: 600; color: var(--slate); }
.dropdown-item:hover { background-color: #f1f5f9; color: var(--text); }
.dropdown-toggle::after { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); }
</style>
@endsection

@push('page_scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(function () {
            var ctx = document.getElementById('financeChart').getContext('2d');
            var chartData = @json($chartData);
            
            var gradientIncome = ctx.createLinearGradient(0, 0, 0, 400);
            gradientIncome.addColorStop(0, 'rgba(16, 185, 129, 0.2)');
            gradientIncome.addColorStop(1, 'rgba(16, 185, 129, 0)');

            var gradientExpense = ctx.createLinearGradient(0, 0, 0, 400);
            gradientExpense.addColorStop(0, 'rgba(244, 63, 94, 0.2)');
            gradientExpense.addColorStop(1, 'rgba(244, 63, 94, 0)');

            // Use common CSS variables
            Chart.defaults.font.family = "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif";
            Chart.defaults.color = '#64748b';

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [
                        {
                            label: 'Income',
                            data: chartData.income,
                            backgroundColor: gradientIncome,
                            borderColor: '#10b981', // emerald
                            borderWidth: 2,
                            pointBackgroundColor: '#10b981',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            fill: true,
                            tension: 0.4
                        },
                        {
                            label: 'Expenses',
                            data: chartData.expenses,
                            backgroundColor: gradientExpense,
                            borderColor: '#f43f5e', // rose
                            borderWidth: 2,
                            pointBackgroundColor: '#f43f5e',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            fill: true,
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            align: 'end',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 8,
                                padding: 20,
                                font: {
                                    size: 12,
                                    weight: '600'
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.9)',
                            titleFont: { size: 13 },
                            bodyFont: { size: 13 },
                            padding: 12,
                            cornerRadius: 8,
                            displayColors: true,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES', maximumSignificantDigits: 3 }).format(context.parsed.y);
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                display: true,
                                color: 'rgba(226, 232, 240, 1)', // var(--border)
                                drawBorder: false,
                                drawTicks: false
                            },
                            ticks: {
                                padding: 10,
                                callback: function(value) {
                                    if (value >= 1000000) {
                                        return (value / 1000000).toFixed(1) + 'M';
                                    } else if (value >= 1000) {
                                        return (value / 1000).toFixed(0) + 'K';
                                    }
                                    return value;
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                padding: 10
                            }
                        }
                    }
                }
            });
        });
    </script>
@endpush
