@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><i class="fas fa-tachometer-alt mr-2"></i>Financial Dashboard</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <div class="btn-group">
                        <a href="{{ route('income.create') }}" class="btn btn-success rounded-pill px-3 mr-2">
                            <i class="fas fa-plus mr-1"></i> Record Income
                        </a>
                        <a href="{{ route('expenses.create') }}" class="btn btn-danger rounded-pill px-3 mr-2">
                            <i class="fas fa-minus mr-1"></i> Record Expense
                        </a>
                        <div class="dropdown d-inline">
                            <button class="btn btn-outline-primary dropdown-toggle rounded-pill px-3" type="button" id="reportMenu" data-toggle="dropdown">
                                <i class="fas fa-file-invoice mr-1"></i> Reports
                            </button>
                            <div class="dropdown-menu dropdown-menu-right shadow border-0">
                                <a class="dropdown-item" href="{{ route('financial-reports.cashflow') }}"><i class="fas fa-chart-line mr-2 text-primary"></i> Cashflow Statement</a>
                                <a class="dropdown-item" href="{{ route('financial-reports.p-and-l') }}"><i class="fas fa-calculator mr-2 text-success"></i> Profit & Loss</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="{{ route('financial-reports.index') }}"><i class="fas fa-file-alt mr-2 text-info"></i> More Reports</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="content">
        <div class="container-fluid">
            <!-- Metric Cards -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-white shadow-sm border-0 rounded-lg overflow-hidden">
                        <div class="inner p-4">
                            <p class="text-muted text-uppercase mb-1 small font-weight-bold">Income (This Month)</p>
                            <h3 class="font-weight-bold mb-1">KES {{ number_format($combinedIncomeThisMonth, 0) }}</h3>
                            <p class="mb-0 {{ $incomeChange >= 0 ? 'text-success' : 'text-danger' }} small">
                                <i class="fas {{ $incomeChange >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }} mr-1"></i>
                                <b>{{ abs(round($incomeChange, 1)) }}%</b> from last month
                            </p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-arrow-circle-down text-success opacity-20" style="font-size: 70px; right: 15px; top: 20px;"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-white shadow-sm border-0 rounded-lg overflow-hidden">
                        <div class="inner p-4">
                            <p class="text-muted text-uppercase mb-1 small font-weight-bold">Expenses (This Month)</p>
                            <h3 class="font-weight-bold mb-1">KES {{ number_format($totalExpensesThisMonth, 0) }}</h3>
                            <p class="mb-0 {{ $expenseChange <= 0 ? 'text-success' : 'text-danger' }} small">
                                <i class="fas {{ $expenseChange <= 0 ? 'fa-arrow-down' : 'fa-arrow-up' }} mr-1"></i>
                                <b>{{ abs(round($expenseChange, 1)) }}%</b> from last month
                            </p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-arrow-circle-up text-danger opacity-20" style="font-size: 70px; right: 15px; top: 20px;"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-white shadow-sm border-0 rounded-lg overflow-hidden">
                        <div class="inner p-4">
                            <p class="text-muted text-uppercase mb-1 small font-weight-bold">Net Cash Flow</p>
                            <h3 class="font-weight-bold mb-1 {{ $netCashFlow >= 0 ? 'text-success' : 'text-danger' }}">
                                KES {{ number_format($netCashFlow, 0) }}
                            </h3>
                            <p class="mb-0 text-muted small">
                                <b>{{ round($cashFlowPercentage, 1) }}%</b> of total income
                            </p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-line text-info opacity-20" style="font-size: 70px; right: 15px; top: 20px;"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-white shadow-sm border-0 rounded-lg overflow-hidden">
                        <div class="inner p-4">
                            <p class="text-muted text-uppercase mb-1 small font-weight-bold">Total Bank Balance</p>
                            <h3 class="font-weight-bold mb-1 {{ $totalBankBalance > 100000 ? 'text-primary' : 'text-warning' }}">
                                KES {{ number_format($totalBankBalance, 0) }}
                            </h3>
                            <p class="mb-0 {{ $lowBalanceAccounts > 0 ? 'text-danger' : 'text-muted' }} small">
                                <i class="fas fa-university mr-1"></i>
                                <b>{{ $lowBalanceAccounts }}</b> Accounts low balance
                            </p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-university text-primary opacity-20" style="font-size: 70px; right: 15px; top: 20px;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Main Chart -->
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm rounded-lg mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title font-weight-bold"><i class="fas fa-chart-area mr-2 text-primary"></i>Income vs Expenses (Last 6 Months)</h5>
                        </div>
                        <div class="card-body">
                            <div class="chart">
                                <canvas id="financeChart" style="min-height: 250px; height: 350px; max-height: 350px; max-width: 100%;"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Transactions -->
                    <div class="card border-0 shadow-sm rounded-lg">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h5 class="card-title font-weight-bold mb-0"><i class="fas fa-history mr-2 text-secondary"></i>Recent Transactions</h5>
                            <a href="{{ route('expenses.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">View All</a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr class="text-muted small text-uppercase">
                                            <th class="pl-4">Date</th>
                                            <th>Description</th>
                                            <th>Category</th>
                                            <th class="text-right">Amount</th>
                                            <th class="text-center pr-4">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentExpenses->take(5) as $expense)
                                            <tr>
                                                <td class="pl-4 py-3 small">{{ $expense->expense_date->format('d M, Y') }}</td>
                                                <td class="py-3">
                                                    <span class="font-weight-bold">{{ $expense->description ?: 'No description' }}</span><br>
                                                    <small class="text-muted">{{ $expense->payment_method }} • {{ $expense->reference_number ?: 'N/A' }}</small>
                                                </td>
                                                <td class="py-3"><span class="badge badge-light px-3 py-2">{{ $expense->category ? $expense->category->name : 'General' }}</span></td>
                                                <td class="py-3 text-right text-danger font-weight-bold">- KES {{ number_format($expense->amount, 0) }}</td>
                                                <td class="py-3 text-center pr-4">
                                                    @php
                                                        $statusClass = [
                                                            'draft' => 'secondary',
                                                            'pending' => 'warning',
                                                            'approved' => 'info',
                                                            'paid' => 'success',
                                                            'rejected' => 'danger'
                                                        ][$expense->status] ?? 'secondary';
                                                    @endphp
                                                    <span class="badge badge-{{ $statusClass }} px-2 py-1 text-uppercase small">{{ $expense->status }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                        @foreach($recentIncome->take(5) as $income)
                                            <tr>
                                                <td class="pl-4 py-3 small">{{ $income->income_date->format('d M, Y') }}</td>
                                                <td class="py-3">
                                                    <span class="font-weight-bold">{{ $income->payer_name ?: 'Unknown Payer' }}</span><br>
                                                    <small class="text-muted">{{ $income->description ?: 'Other Income' }}</small>
                                                </td>
                                                <td class="py-3"><span class="badge badge-light px-3 py-2">{{ $income->category ? $income->category->name : 'General' }}</span></td>
                                                <td class="py-3 text-right text-success font-weight-bold">+ KES {{ number_format($income->amount, 0) }}</td>
                                                <td class="py-3 text-center pr-4">
                                                    <span class="badge badge-success px-2 py-1 text-uppercase small">Active</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar items -->
                <div class="col-md-4">
                    <!-- Pending Approvals Widget -->
                    <div class="card border-0 shadow-sm rounded-lg mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title font-weight-bold"><i class="fas fa-clock mr-2 text-warning"></i>Pending Approvals</h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center py-3">
                                <h2 class="font-weight-bold mb-0">{{ $pendingApprovalsCount }}</h2>
                                <p class="text-muted mb-3 font-weight-bold small text-uppercase">Expenses awaiting approval</p>
                                <h4 class="text-warning font-weight-bold">KES {{ number_format($pendingApprovalsAmount, 0) }}</h4>
                                <a href="{{ route('expenses.pending') }}" class="btn btn-warning btn-block mt-4 rounded-pill font-weight-bold py-2">
                                    <i class="fas fa-check-circle mr-1"></i> Review Requests
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Alerts & Insights -->
                    <div class="card border-0 shadow-sm rounded-lg mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title font-weight-bold"><i class="fas fa-lightbulb mr-2 text-info"></i>Financial Insights</h5>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush small">
                                @if($lowBalanceAccounts > 0)
                                    <li class="list-group-item border-0 px-4 py-3">
                                        <div class="d-flex align-items-start">
                                            <div class="bg-danger-light p-2 rounded mr-3 mt-1">
                                                <i class="fas fa-exclamation-triangle text-danger"></i>
                                            </div>
                                            <div>
                                                <b class="d-block mb-1">Low Bank Balance</b>
                                                <span class="text-muted">{{ $lowBalanceAccounts }} account(s) have dropped below their minimum threshold.</span>
                                            </div>
                                        </div>
                                    </li>
                                @endif
                                
                                <li class="list-group-item border-0 px-4 py-3">
                                    <div class="d-flex align-items-start">
                                        <div class="bg-success-light p-2 rounded mr-3 mt-1">
                                            <i class="fas fa-calendar-check text-success"></i>
                                        </div>
                                        <div>
                                            <b class="d-block mb-1">Current Cash Runway</b>
                                            @php
                                                $avgExpense = $totalExpensesThisMonth > 0 ? $totalExpensesThisMonth : ($totalExpensesLastMonth ?: 1);
                                                $runway = $totalBankBalance / $avgExpense;
                                            @endphp
                                            <span class="text-muted">You have enough cash to operate for approx. <b>{{ round($runway, 1) }} months</b> at current burn rate.</span>
                                        </div>
                                    </div>
                                </li>

                                <li class="list-group-item border-0 px-4 py-3">
                                    <div class="d-flex align-items-start">
                                        <div class="bg-info-light p-2 rounded mr-3 mt-1">
                                            <i class="fas fa-info-circle text-info"></i>
                                        </div>
                                        <div>
                                            <b class="d-block mb-1">Fee Collection Status</b>
                                            <span class="text-muted">Total fees collected this month: KES {{ number_format($combinedIncomeThisMonth - $totalIncomeThisMonth, 0) }}.</span>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card border-0 shadow-sm rounded-lg">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title font-weight-bold font-weight-bold"><i class="fas fa-bolt mr-2 text-primary"></i>Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <a href="{{ route('bank-reconciliations.index') }}" class="btn btn-light btn-block p-3 border-0 rounded-lg shadow-sm">
                                        <i class="fas fa-balance-scale d-block mb-2 text-primary fa-lg"></i>
                                        <span class="small font-weight-bold">Reconcile</span>
                                    </a>
                                </div>
                                <div class="col-6 mb-3">
                                    <a href="{{ route('budgets.index') }}" class="btn btn-light btn-block p-3 border-0 rounded-lg shadow-sm">
                                        <i class="fas fa-clipboard-list d-block mb-2 text-info fa-lg"></i>
                                        <span class="small font-weight-bold">Budgets</span>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="{{ route('audit-trail.index') }}" class="btn btn-light btn-block p-3 border-0 rounded-lg shadow-sm">
                                        <i class="fas fa-history d-block mb-2 text-secondary fa-lg"></i>
                                        <span class="small font-weight-bold">Audit Log</span>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="{{ route('financial-years.index') }}" class="btn btn-light btn-block p-3 border-0 rounded-lg shadow-sm">
                                        <i class="fas fa-calendar-alt d-block mb-2 text-dark fa-lg"></i>
                                        <span class="small font-weight-bold">Setup Year</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .bg-danger-light { background-color: #fff1f2; }
        .bg-success-light { background-color: #f0fdf4; }
        .bg-info-light { background-color: #f0f9ff; }
        .opacity-20 { opacity: 0.1; }
        .list-group-item { transition: background 0.2s; }
        .list-group-item:hover { background-color: #f8fafc; }
        .small-box { border-radius: 12px; transition: transform 0.2s; }
        .small-box:hover { transform: translateY(-5px); }
    </style>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(function () {
            var ctx = document.getElementById('financeChart').getContext('2d');
            var chartData = @json($chartData);
            
            var gradientIncome = ctx.createLinearGradient(0, 0, 0, 400);
            gradientIncome.addColorStop(0, 'rgba(56, 189, 248, 0.4)');
            gradientIncome.addColorStop(1, 'rgba(56, 189, 248, 0)');

            var gradientExpense = ctx.createLinearGradient(0, 0, 0, 400);
            gradientExpense.addColorStop(0, 'rgba(239, 68, 68, 0.4)');
            gradientExpense.addColorStop(1, 'rgba(239, 68, 68, 0)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [
                        {
                            label: 'Income',
                            data: chartData.income,
                            backgroundColor: gradientIncome,
                            borderColor: '#38bdf8',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4
                        },
                        {
                            label: 'Expenses',
                            data: chartData.expenses,
                            backgroundColor: gradientExpense,
                            borderColor: '#ef4444',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                display: true,
                                color: 'rgba(0,0,0,0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        });
    </script>
@endsection
