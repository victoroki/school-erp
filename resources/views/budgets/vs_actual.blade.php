@extends('layouts.app')

@section('content')
    @php
        $expenseRows = $comparison->where('type', 'expense')->values();
        $incomeRows = $comparison->where('type', 'income')->values();

        $expBudgeted = (float) $expenseRows->sum('budgeted');
        $expActual = (float) $expenseRows->sum('actual');
        $expVariance = $expBudgeted - $expActual;
        $expPct = $expBudgeted > 0 ? ($expActual / $expBudgeted) * 100 : 0;

        $incomeBudgeted = (float) $incomeRows->sum('budgeted');
        $incomeActual = (float) $incomeRows->sum('actual');

        $empty = $comparison->isEmpty();
    @endphp

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-7">
                    <div class="detail-heading">
                        <div class="detail-heading-icon">
                            <i class="fas fa-balance-scale"></i>
                        </div>
                        <div>
                            <h1 class="detail-heading-title">Budget vs Actual</h1>
                            <p class="detail-heading-sub">{{ $activeYear->name }} &middot; as of {{ now()->format('d M, Y') }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-sm-5 detail-heading-actions">
                    <a href="{{ route('budgets.index') }}" class="btn-detail btn-detail--ghost">
                        <i class="fas fa-chart-pie mr-1"></i> Budgets
                    </a>
                    <button onclick="window.print()" class="btn-detail btn-detail--primary">
                        <i class="fas fa-print mr-1"></i> Print Report
                    </button>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @if($empty)
            <div class="card border-0 shadow-sm rounded-lg text-center py-5">
                <i class="fas fa-chart-pie fa-3x text-muted opacity-30 mb-3"></i>
                <h5 class="font-weight-bold text-dark">No budgets yet for {{ $activeYear->name }}</h5>
                <p class="text-muted mb-4">Create budget entries to compare them against actual income and spending.</p>
                <a href="{{ route('budgets.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm d-inline-block">
                    <i class="fas fa-plus mr-1"></i> Add First Budget
                </a>
            </div>
        @else
            <!-- KPI Summary (Expenses) -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 col-12 mb-3 mb-lg-0">
                    <div class="kpi-card">
                        <div class="kpi-label">Budgeted (Expenses)</div>
                        <div class="kpi-value slate">{{ \App\Support\Money::format($expBudgeted) }}</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-12 mb-3 mb-lg-0">
                    <div class="kpi-card">
                        <div class="kpi-label">Spent to Date</div>
                        <div class="kpi-value slate">{{ \App\Support\Money::format($expActual) }}</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-12 mb-3 mb-lg-0">
                    <div class="kpi-card">
                        <div class="kpi-label">Remaining Budget</div>
                        <div class="kpi-value {{ $expVariance < 0 ? 'rose' : 'emerald' }}">{{ \App\Support\Money::format(max($expVariance, 0)) }}{{ $expVariance < 0 ? ' Over' : '' }}</div>
                        <p class="kpi-note">{{ round($expPct, 1) }}% of budget used</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-12">
                    <div class="kpi-card">
                        <div class="kpi-label">Utilization</div>
                        <div class="kpi-value ml-0 {{ $expPct >= 100 ? 'rose' : ($expPct >= 80 ? 'amber' : 'emerald') }}">{{ round($expPct, 0) }}%</div>
                        <div class="util-track util-track--kpi">
                            <div class="util-fill" style="width: {{ min($expPct, 100) }}%; background: {{ $expPct >= 100 ? '#e11d48' : ($expPct >= 80 ? '#d97706' : '#059669') }};"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Expense Section -->
            <div class="card border-0 shadow-sm rounded-lg mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <div class="d-flex align-items-center justify-content-between flex-wrap">
                        <div>
                            <h5 class="mb-0 font-weight-bold text-dark"><i class="fas fa-receipt text-danger mr-2"></i>Expenses</h5>
                            <small class="text-muted">Budgeted {{ \App\Support\Money::format($expBudgeted) }} &middot; Spent {{ \App\Support\Money::format($expActual) }}</small>
                        </div>
                        <span class="badge badge-rose-light text-danger px-3 py-2 rounded-pill font-weight-bold">{{ $expenseRows->count() }} {{ \Illuminate\Support\Str::plural('entry', $expenseRows->count()) }}</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                            <tr class="bg-light text-muted small text-uppercase">
                                <th class="pl-4 border-0">Category</th>
                                <th class="border-0 text-right">Budgeted</th>
                                <th class="border-0 text-right">Spent</th>
                                <th class="border-0 text-right">Remaining</th>
                                <th class="border-0 pr-4" style="width: 220px;">Utilization</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($expenseRows as $row)
                                @php
                                    $color = $row->percentage >= 100 ? '#e11d48' : ($row->percentage >= (float) $row->threshold ? '#d97706' : '#059669');
                                @endphp
                                <tr>
                                    <td class="pl-4 py-3 align-middle font-weight-bold">{{ $row->category }}</td>
                                    <td class="py-3 align-middle text-right">{{ \App\Support\Money::format($row->budgeted) }}</td>
                                    <td class="py-3 align-middle text-right">{{ \App\Support\Money::format($row->actual) }}</td>
                                    <td class="py-3 align-middle text-right font-weight-bold {{ $row->variance < 0 ? 'text-danger' : 'text-success' }}">
                                        {{ \App\Support\Money::format(max($row->variance, 0)) }}{{ $row->variance < 0 ? ' over' : '' }}
                                    </td>
                                    <td class="py-3 align-middle pr-4">
                                        <div class="util-row">
                                            <div class="util-track">
                                                <div class="util-fill" style="width: {{ min($row->percentage, 100) }}%; background: {{ $color }};"></div>
                                                <span class="util-mark" style="left: {{ min((float) $row->threshold, 100) }}%;"></span>
                                            </div>
                                            <span class="util-pct {{ $row->percentage >= 100 ? 'rose' : ($row->percentage >= (float) $row->threshold ? 'amber' : 'emerald') }}">{{ round($row->percentage, 1) }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="fas fa-chart-pie fa-2x mb-2 opacity-30"></i><br>
                                        No expense budgets for this period.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                            @if($expenseRows->isNotEmpty())
                                <tfoot class="bg-light font-weight-bold">
                                    <tr>
                                        <td class="pl-4">EXPENSE TOTALS</td>
                                        <td class="text-right">{{ \App\Support\Money::format($expBudgeted) }}</td>
                                        <td class="text-right">{{ \App\Support\Money::format($expActual) }}</td>
                                        <td class="text-right {{ $expVariance < 0 ? 'text-danger' : 'text-success' }}">{{ \App\Support\Money::format(max($expVariance, 0)) }}</td>
                                        <td class="pr-4"><span class="{{ $expPct >= 100 ? 'rose' : ($expPct >= 80 ? 'amber' : 'emerald') }} font-weight-bold">{{ round($expPct, 1) }}%</span></td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

            <!-- Income Section -->
            <div class="card border-0 shadow-sm rounded-lg mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <div class="d-flex align-items-center justify-content-between flex-wrap">
                        <div>
                            <h5 class="mb-0 font-weight-bold text-dark"><i class="fas fa-hand-holding-usd text-success mr-2"></i>Income</h5>
                            <small class="text-muted">Budgeted {{ \App\Support\Money::format($incomeBudgeted) }} &middot; Received {{ \App\Support\Money::format($incomeActual) }}</small>
                        </div>
                        <span class="badge badge-success-light text-success px-3 py-2 rounded-pill font-weight-bold">{{ $incomeRows->count() }} {{ \Illuminate\Support\Str::plural('entry', $incomeRows->count()) }}</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                            <tr class="bg-light text-muted small text-uppercase">
                                <th class="pl-4 border-0">Category</th>
                                <th class="border-0 text-right">Budgeted</th>
                                <th class="border-0 text-right">Received</th>
                                <th class="border-0 text-right">Variance</th>
                                <th class="border-0 pr-4" style="width: 220px;">Received %</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($incomeRows as $row)
                                @php
                                    $color = $row->percentage >= 100 ? '#059669' : '#4338ca';
                                @endphp
                                <tr>
                                    <td class="pl-4 py-3 align-middle font-weight-bold">{{ $row->category }}</td>
                                    <td class="py-3 align-middle text-right">{{ \App\Support\Money::format($row->budgeted) }}</td>
                                    <td class="py-3 align-middle text-right">{{ \App\Support\Money::format($row->actual) }}</td>
                                    <td class="py-3 align-middle text-right font-weight-bold {{ $row->variance <= 0 ? 'text-success' : 'text-muted' }}">
                                        {{ \App\Support\Money::format($row->variance) }}{{ $row->variance > 0 ? ' short' : '' }}
                                    </td>
                                    <td class="py-3 align-middle pr-4">
                                        <div class="util-row">
                                            <div class="util-track">
                                                <div class="util-fill" style="width: {{ min($row->percentage, 100) }}%; background: {{ $color }};"></div>
                                            </div>
                                            <span class="util-pct {{ $row->percentage >= 100 ? 'emerald' : 'indigo' }}">{{ round($row->percentage, 1) }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="fas fa-hand-holding-usd fa-2x mb-2 opacity-30"></i><br>
                                        No income budgets for this period.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                            @if($incomeRows->isNotEmpty())
                                <tfoot class="bg-light font-weight-bold">
                                    <tr>
                                        <td class="pl-4">INCOME TOTALS</td>
                                        <td class="text-right">{{ \App\Support\Money::format($incomeBudgeted) }}</td>
                                        <td class="text-right">{{ \App\Support\Money::format($incomeActual) }}</td>
                                        <td class="text-right">{{ \App\Support\Money::format($incomeBudgeted - $incomeActual) }}</td>
                                        <td class="pr-4"><span class="indigo font-weight-bold">{{ $incomeBudgeted > 0 ? round(($incomeActual / $incomeBudgeted) * 100, 1) : 0 }}%</span></td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

            <p class="text-muted small mb-0">
                <i class="fas fa-info-circle mr-1"></i>
                Transfer on the expense bar marks each category's alert threshold. Bars turn amber at the threshold and rose when spent in full.
            </p>
        @endif
    </div>

    <style>
        .detail-heading { display: flex; align-items: center; gap: 14px; }
        .detail-heading-icon {
            width: 44px; height: 44px; border-radius: 10px; flex-shrink: 0;
            background: #eef2ff; color: #4338ca;
            display: flex; align-items: center; justify-content: center; font-size: 1.15rem;
        }
        .detail-heading-title { font-size: 1.35rem; font-weight: 800; color: #0f172a; margin: 0; line-height: 1.2; }
        .detail-heading-sub { color: #64748b; font-size: 0.85rem; font-weight: 500; margin: 2px 0 0; }
        .detail-heading-actions { text-align: right; }
        @media (max-width: 767px) { .detail-heading-actions { text-align: left; margin-top: 0.75rem; } }

        .btn-detail {
            display: inline-block; padding: 8px 18px; border-radius: 8px;
            font-size: 0.85rem; font-weight: 600; line-height: 1.4; text-decoration: none;
            transition: transform 160ms cubic-bezier(0.23, 1, 0.32, 1);
        }
        .btn-detail:active { transform: scale(0.97); }
        .btn-detail--primary { background: #4338ca; border: 1px solid #4338ca; color: #fff; }
        .btn-detail--primary:hover { background: #3730a3; border-color: #3730a3; color: #fff; }
        .btn-detail--ghost { background: #fff; border: 1px solid #e2e8f0; color: #334155; }
        .btn-detail--ghost:hover { background: #f8fafc; color: #0f172a; }
        @media (max-width: 575px) { .btn-detail { width: 100%; text-align: center; margin-bottom: 0.4rem; } }

        .kpi-card {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
            padding: 1.25rem 1.5rem; height: 100%;
        }
        .kpi-label { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; font-weight: 700; }
        .kpi-value {
            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
            font-size: 1.4rem; font-weight: 900; color: #0f172a; margin-top: 4px; line-height: 1.15;
        }
        .kpi-note { font-size: 0.8rem; color: #64748b; font-weight: 500; margin: 4px 0 0; }

        .util-row { display: flex; align-items: center; gap: 10px; }
        .util-track {
            position: relative; flex: 1; height: 8px;
            background: #f1f5f9; border-radius: 999px; overflow: hidden;
        }
        .util-track--kpi { margin-top: 10px; }
        .util-fill { position: absolute; left: 0; top: 0; height: 100%; border-radius: 999px; }
        .util-mark {
            position: absolute; top: -2px; bottom: -2px; width: 2px;
            background: #64748b; border-radius: 2px;
        }
        .util-pct {
            font-size: 0.8rem; font-weight: 700; min-width: 46px; text-align: right;
            font-family: 'SFMono-Regular', Consolas, monospace;
        }

        .emerald { color: #059669 !important; }
        .amber { color: #d97706 !important; }
        .rose { color: #e11d48 !important; }
        .indigo { color: #4338ca !important; }
        .slate { color: #0f172a !important; }

        .badge-rose-light { background-color: #fee2e2; }
        .badge-success-light { background-color: #d1fae5; }
        .bg-light { background-color: #f8fafc !important; }
        .rounded-pill { border-radius: 50rem !important; }
        .opacity-30 { opacity: 0.3; }
        .table-hover tbody tr:hover { background-color: #f1f5f9; }
        .card-header { background: #fff; }

        @media print {
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .btn-detail, .detail-heading-actions { display: none !important; }
            .card, .kpi-card { box-shadow: none !important; border: 1px solid #cbd5e1; }
            .content-wrapper { margin-left: 0 !important; }
            .main-sidebar, .navbar { display: none !important; }
        }
    </style>
@endsection