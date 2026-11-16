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
                    <h1 class="dash-heading">Fee Collection Trends</h1>
                    <p class="dash-sub">Last 12 months — expected vs collected</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Summary Stats --}}
    <div class="row mb-4">
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="stat-card">
                <div class="stat-icon bg-emerald-light text-emerald"><i class="fas fa-hand-holding-usd"></i></div>
                <div class="stat-info w-100">
                    <span class="stat-label">Total Collected (12m)</span>
                    <span class="stat-value text-emerald">KES {{ number_format($totalCollected, 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="stat-card">
                <div class="stat-icon bg-amber-light text-amber"><i class="fas fa-file-invoice-dollar"></i></div>
                <div class="stat-info w-100">
                    <span class="stat-label">Expected (Current Month)</span>
                    <span class="stat-value text-amber">KES {{ number_format($totalExpected, 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card {{ $overallRate >= 80 ? 'border-emerald-light' : ($overallRate >= 50 ? 'border-amber-light' : 'border-rose-light') }}">
                <div class="stat-icon {{ $overallRate >= 80 ? 'bg-indigo-light text-indigo' : ($overallRate >= 50 ? 'bg-amber-light text-amber' : 'bg-rose-light text-rose') }}">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="stat-info w-100">
                    <span class="stat-label">Collection Rate (This Month)</span>
                    <span class="stat-value {{ $overallRate >= 80 ? 'text-indigo' : ($overallRate >= 50 ? 'text-amber' : 'text-rose') }}">{{ $overallRate }}%</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Bar Chart --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="dash-panel">
                <div class="dash-panel-header">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-chart-bar text-slate"></i>
                        <h3 class="dash-panel-title">Monthly Collection Chart</h3>
                    </div>
                    <div class="d-flex gap-3 small">
                        <span><span class="legend-dot bg-indigo"></span> Expected</span>
                        <span><span class="legend-dot bg-emerald"></span> Collected</span>
                    </div>
                </div>
                <div class="dash-panel-body p-3" style="min-height: 300px;">
                    <canvas id="feeCollectionChart" style="max-height: 320px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Monthly Trend Table --}}
        <div class="col-lg-8 mb-4">
            <div class="dash-panel">
                <div class="dash-panel-header">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-table text-slate"></i>
                        <h3 class="dash-panel-title">Monthly Breakdown</h3>
                    </div>
                </div>
                <div class="dash-panel-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="pl-4">Month</th>
                                    <th class="text-right">Expected</th>
                                    <th class="text-right">Collected</th>
                                    <th class="text-right">Outstanding</th>
                                    <th class="text-right pr-4">Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($monthlyData as $row)
                                    <tr>
                                        <td class="pl-4 font-weight-bold">{{ $row['month'] }}</td>
                                        <td class="text-right text-dark">KES {{ number_format($row['expected'], 2) }}</td>
                                        <td class="text-right text-emerald font-weight-bold">KES {{ number_format($row['collected'], 2) }}</td>
                                        <td class="text-right {{ $row['outstanding'] > 0 ? 'text-rose' : 'text-muted' }}">
                                            KES {{ number_format($row['outstanding'], 2) }}
                                        </td>
                                        <td class="text-right pr-4">
                                            @php
                                                $rateClass = $row['rate'] >= 80 ? 'badge-success' : ($row['rate'] >= 50 ? 'badge-warning' : 'badge-danger');
                                            @endphp
                                            <span class="badge {{ $rateClass }}">{{ $row['rate'] }}%</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Top Paying Classes --}}
        <div class="col-lg-4 mb-4">
            <div class="dash-panel h-100">
                <div class="dash-panel-header">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-trophy text-amber"></i>
                        <h3 class="dash-panel-title">Top Paying Classes</h3>
                    </div>
                </div>
                <div class="dash-panel-body p-0">
                    @if($topClasses->isEmpty())
                        <div class="p-4 text-center text-muted">No payment data available.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th class="pl-4">Class</th>
                                        <th class="text-right pr-4">Collected</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topClasses as $i => $cls)
                                        <tr>
                                            <td class="pl-4">
                                                <span class="rank-badge rank-{{ $i + 1 }}">{{ $i + 1 }}</span>
                                                {{ $cls->class_name }}
                                            </td>
                                            <td class="text-right pr-4 font-weight-bold text-emerald">
                                                KES {{ number_format($cls->total_collected, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
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
.bg-amber { background: var(--amber); }
.bg-indigo { background: var(--indigo); }
.bg-emerald { background: var(--emerald); }
.bg-rose-light { background: var(--rose-light); } .text-rose { color: var(--rose); }
.bg-slate-light { background: var(--slate-light); } .text-slate { color: var(--slate); }
.border-emerald-light { border-color: #34d399 !important; }
.border-rose-light { border-color: #fb7185 !important; }
.border-amber-light { border-color: #fcd34d !important; }

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
.table tbody td { padding: .875rem 1.5rem; vertical-align: middle; border-bottom: 1px solid #f1f5f9; border-top: 0; font-size: 0.875rem; }
.table tbody tr:last-child td { border-bottom: 0; }

.btn-dash { display: inline-flex; align-items: center; justify-content: center; padding: .625rem 1rem; border-radius: 8px; font-size: .875rem; font-weight: 600; transition: all 150ms var(--ease-out); border: 1px solid transparent; text-decoration: none !important; cursor: pointer; }
.btn-ghost { background: transparent; color: var(--muted); border: 1px solid var(--border); }
.btn-ghost:hover { background: #f1f5f9; color: var(--text); border-color: #cbd5e1; }

.legend-dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 4px; }

.rank-badge { display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 50%; font-size: 0.7rem; font-weight: 800; margin-right: 6px; }
.rank-1 { background: #fef08a; color: #713f12; }
.rank-2 { background: #e2e8f0; color: #475569; }
.rank-3 { background: #fed7aa; color: #7c2d12; }
.rank-4, .rank-5 { background: #f1f5f9; color: #64748b; }
</style>

@push('scripts')
<script>
(function () {
    const labels   = {!! $chartLabels !!};
    const expected = {!! $chartExpected !!};
    const collected = {!! $chartCollected !!};

    const ctx = document.getElementById('feeCollectionChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Expected',
                    data: expected,
                    backgroundColor: 'rgba(79, 70, 229, 0.15)',
                    borderColor: 'rgba(79, 70, 229, 0.8)',
                    borderWidth: 2,
                    borderRadius: 4,
                },
                {
                    label: 'Collected',
                    data: collected,
                    backgroundColor: 'rgba(16, 185, 129, 0.7)',
                    borderColor: 'rgba(16, 185, 129, 1)',
                    borderWidth: 1,
                    borderRadius: 4,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': KES ' + context.parsed.y.toLocaleString('en-US', {minimumFractionDigits: 2});
                        }
                    }
                }
            },
            scales: {
                x: { grid: { display: false } },
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'KES ' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
})();
</script>
@endpush
@endsection
