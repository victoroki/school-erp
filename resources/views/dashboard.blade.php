@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $firstName = explode(' ', trim($user->name))[0];
    $isFinance = in_array($roleName, ['finance', 'accountant', 'bursar']);
    $isHr = in_array($roleName, ['hr', 'human resources', 'hr manager']);
    $alertTone = ['danger' => 'is-danger', 'warning' => 'is-warning', 'info' => 'is-info', 'success' => 'is-success'];
    $statusText = ['good' => 'On track', 'warning' => 'Check', 'danger' => 'Action needed', 'info' => 'Info'];
@endphp

<style>
    .db { padding: 24px 28px 40px; max-width: 1440px; }

    /* Header */
    .db-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 24px; }
    .db-head h1 { margin: 0; font-size: 24px; font-weight: 700; letter-spacing: -0.02em; color: var(--shell-ink); }
    .db-head p { margin: 4px 0 0; font-size: 14px; color: var(--shell-muted); }
    .db-actions { display: flex; gap: 8px; flex-wrap: wrap; }

    /* Section labels */
    .db-label { margin: 0 0 10px; font-size: 12px; font-weight: 600; letter-spacing: 0.06em; text-transform: uppercase; color: var(--shell-faint); }

    /* Panels */
    .db-panel { background: #fff; border: 1px solid var(--shell-border); border-radius: 12px; }
    .db-panel-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 16px 18px 0; }
    .db-panel-head h2 { margin: 0; font-size: 15px; font-weight: 600; color: var(--shell-ink); }
    .db-panel-head p { margin: 2px 0 0; font-size: 13px; color: var(--shell-muted); }
    .db-panel-link { font-size: 13px; font-weight: 600; color: var(--accent); white-space: nowrap; }
    .db-panel-link:hover { color: var(--accent-strong); }

    /* Needs attention */
    .db-attention { margin-bottom: 24px; }
    .db-attention ul { list-style: none; margin: 0; padding: 6px 0; }
    .db-attention li { display: flex; align-items: center; gap: 14px; padding: 12px 18px; }
    .db-attention li + li { border-top: 1px solid #eef1f5; }
    .db-dot { width: 34px; height: 34px; border-radius: 9px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 14px; }
    .is-danger  .db-dot { background: #fdf1f2; color: #b42335; }
    .is-warning .db-dot { background: #fef6e7; color: #a15c07; }
    .is-info    .db-dot { background: #eef3fa; color: #1d3f7a; }
    .is-success .db-dot { background: #eefaf4; color: #0f6b4b; }
    .db-attention-text { flex: 1; min-width: 0; }
    .db-attention-text strong { display: block; font-size: 14px; font-weight: 600; color: var(--shell-ink); }
    .db-attention-text span { font-size: 13px; color: var(--shell-muted); }

    /* Metrics */
    .db-metrics { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 14px; margin-bottom: 24px; }
    .db-metric { display: block; padding: 18px; color: inherit; text-decoration: none; transition: border-color 150ms ease, box-shadow 150ms ease, transform 160ms var(--ease-out); }
    .db-metric:hover { color: inherit; text-decoration: none; border-color: #cfd7e3; box-shadow: 0 4px 14px rgba(20, 32, 51, 0.06); }
    .db-metric:active { transform: scale(0.99); }
    .db-metric:focus-visible { outline: 2px solid var(--accent); outline-offset: 2px; }
    .db-metric-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
    .db-metric-title { font-size: 13px; font-weight: 500; color: var(--shell-muted); }
    .db-metric-icon { color: var(--shell-faint); font-size: 14px; }
    .db-metric-value { font-size: 28px; font-weight: 700; letter-spacing: -0.02em; color: var(--shell-ink); font-variant-numeric: tabular-nums; line-height: 1.1; }
    .db-metric-foot { margin-top: 6px; font-size: 12.5px; color: var(--shell-muted); }
    .db-metric-value { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .db-metric.is-money .db-metric-value { font-size: 23px; letter-spacing: -0.01em; padding-top: 3px; }
    .db-metric.is-owed .db-metric-value { color: #a15c07; }

    /* Main grid */
    .db-grid { display: grid; grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); gap: 14px; margin-bottom: 24px; }
    .db-chart { padding-bottom: 14px; }
    .db-chart-body { position: relative; height: 280px; padding: 14px 14px 0; }

    /* Lists (payments, activity, modules) */
    .db-list { list-style: none; margin: 0; padding: 6px 0 8px; }
    .db-list li { display: flex; align-items: center; gap: 12px; padding: 10px 18px; }
    .db-list li + li { border-top: 1px solid #f0f2f6; }
    .db-avatar { width: 32px; height: 32px; border-radius: 50%; background: #eef3fa; color: #1d3f7a; font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .db-list-main { flex: 1; min-width: 0; }
    .db-list-main strong { display: block; font-size: 13.5px; font-weight: 600; color: var(--shell-ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .db-list-main span { font-size: 12.5px; color: var(--shell-muted); }
    .db-amount { font-size: 13.5px; font-weight: 600; color: #0f6b4b; font-variant-numeric: tabular-nums; white-space: nowrap; }
    .db-empty { padding: 28px 18px; text-align: center; font-size: 13.5px; color: var(--shell-muted); }

    /* Modules */
    .db-modules { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 0; padding: 6px 0; }
    .db-module { display: flex; align-items: center; gap: 12px; padding: 12px 18px; color: inherit; text-decoration: none; border-radius: 10px; margin: 0 6px; transition: background-color 120ms ease; }
    .db-module:hover { background: var(--shell-hover); color: inherit; text-decoration: none; }
    .db-module:focus-visible { outline: 2px solid var(--accent); outline-offset: -2px; }
    .db-module-icon { width: 36px; height: 36px; border-radius: 9px; background: #f2f4f8; color: var(--shell-ink-2); display: inline-flex; align-items: center; justify-content: center; font-size: 15px; flex-shrink: 0; }
    .db-module-text { flex: 1; min-width: 0; }
    .db-module-text strong { display: block; font-size: 14px; font-weight: 600; color: var(--shell-ink); }
    .db-module-text span { font-size: 12.5px; color: var(--shell-muted); }
    .db-module-count { font-size: 15px; font-weight: 700; color: var(--shell-ink); font-variant-numeric: tabular-nums; }
    .db-status { display: inline-block; width: 7px; height: 7px; border-radius: 50%; margin-right: 5px; vertical-align: 1px; }
    .db-status.good { background: #12805c; }
    .db-status.warning { background: #d68a0c; }
    .db-status.danger { background: #c62d3f; }
    .db-status.info { background: #1d3f7a; }

    @media (max-width: 1100px) { .db-grid { grid-template-columns: 1fr; } }
    @media (max-width: 576px) {
        .db { padding: 18px 16px 32px; }
        .db-head h1 { font-size: 21px; }
        .db-actions { width: 100%; }
        .db-actions .btn { flex: 1; }
        .db-metric-value { font-size: 24px; }
    }
    @media (prefers-reduced-motion: reduce) { .db-metric { transition: none; } .db-metric:active { transform: none; } }
</style>

<div class="db">

    {{-- Greeting and the role's main actions --}}
    <div class="db-head">
        <div>
            <h1>{{ $greeting }}, {{ $firstName }}</h1>
            <p>{{ now()->format('l, j F Y') }}@if($isFinance) · Fees and revenue @elseif($isHr) · Staff and leave @endif</p>
        </div>
        <div class="db-actions">
            @if($isFinance)
                @if($moduleManager->isActive('finance'))
                    <a href="{{ route('financial-reports.index') }}" class="btn btn-default"><i class="fas fa-file-alt mr-1" aria-hidden="true"></i> Reports</a>
                @endif
                @if($moduleManager->isActive('fees'))
                    <a href="{{ route('fees.collect') }}" class="btn btn-primary"><i class="fas fa-hand-holding-usd mr-1" aria-hidden="true"></i> Collect fees</a>
                @endif
            @elseif($isHr)
                @if($moduleManager->isActive('hr'))
                    <a href="{{ route('leave-applications.index') }}" class="btn btn-default"><i class="fas fa-calendar-alt mr-1" aria-hidden="true"></i> Leave requests</a>
                    <a href="{{ route('hr.onboarding') }}" class="btn btn-primary"><i class="fas fa-user-plus mr-1" aria-hidden="true"></i> Onboard staff</a>
                @endif
            @else
                @if($moduleManager->isActive('students') && \App\Services\MenuService::canSee($user, ['students.manage']))
                    <a href="{{ route('students.create') }}" class="btn btn-default"><i class="fas fa-user-plus mr-1" aria-hidden="true"></i> Admit student</a>
                @endif
                @if($moduleManager->isActive('fees') && \App\Services\MenuService::canSee($user, ['fees.collect']))
                    <a href="{{ route('fees.collect') }}" class="btn btn-primary"><i class="fas fa-hand-holding-usd mr-1" aria-hidden="true"></i> Collect fees</a>
                @endif
            @endif
        </div>
    </div>

    {{-- Needs attention --}}
    @if(!empty($visibleAlerts))
        <section class="db-panel db-attention" aria-labelledby="db-attention-title">
            <div class="db-panel-head"><h2 id="db-attention-title">Needs attention</h2></div>
            <ul>
                @foreach($visibleAlerts as $alert)
                    <li class="{{ $alertTone[$alert['type']] ?? 'is-info' }}">
                        <span class="db-dot"><i class="fas {{ $alert['icon'] }}" aria-hidden="true"></i></span>
                        <div class="db-attention-text">
                            <strong>{{ $alert['title'] }}</strong>
                            <span>{{ $alert['desc'] }}</span>
                        </div>
                        <a href="{{ route($alert['actionRoute']) }}" class="btn btn-sm btn-default">{{ \Illuminate\Support\Str::ucfirst(\Illuminate\Support\Str::lower($alert['action'])) }}</a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    {{-- Key metrics --}}
    @if(!empty($keyMetrics))
        <h2 class="db-label">Key Metrics</h2>
        <div class="db-metrics">
            @foreach($keyMetrics as $metric)
                @php
                    $owed = in_array($metric['color'] ?? '', ['ic-red']);
                    $money = str_contains((string) $metric['value'], 'KES');
                @endphp
                <a href="{{ route($metric['route']) }}" class="db-panel db-metric {{ $owed ? 'is-owed' : '' }} {{ $money ? 'is-money' : '' }}">
                    <div class="db-metric-top">
                        <span class="db-metric-title">{{ $metric['title'] }}</span>
                        <i class="fas {{ $metric['icon'] }} db-metric-icon" aria-hidden="true"></i>
                    </div>
                    <div class="db-metric-value">{{ $metric['value'] }}</div>
                    @if(!empty($metric['foot']))
                        <div class="db-metric-foot">{{ $metric['foot'] }}</div>
                    @endif
                </a>
            @endforeach
        </div>
    @endif

    {{-- Chart and latest payments --}}
    @if(!empty($chartSeries) || $recentPayments->isNotEmpty() || !empty($recentActivity))
        <div class="db-grid" @if(empty($chartSeries)) style="grid-template-columns: 1fr;" @endif>
            @if(!empty($chartSeries))
                <section class="db-panel db-chart" aria-labelledby="db-chart-title">
                    <div class="db-panel-head">
                        <div>
                            <h2 id="db-chart-title">Growth & Revenue</h2>
                            <p>
                                @if(isset($chartSeries['revenue']) && isset($chartSeries['admissions']))
                                    Fees collected per month, with new admissions
                                @elseif(isset($chartSeries['revenue']))
                                    Fees collected per month
                                @else
                                    New admissions per month
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="db-chart-body"><canvas id="chartMain" aria-label="Monthly chart" role="img"></canvas></div>
                </section>
            @endif

            @if($recentPayments->isNotEmpty())
                <section class="db-panel" aria-labelledby="db-payments-title">
                    <div class="db-panel-head">
                        <h2 id="db-payments-title">Latest payments</h2>
                        @if(Route::has('fees.reports.receipt-register'))
                            <a class="db-panel-link" href="{{ route('fees.reports.receipt-register') }}">All receipts</a>
                        @endif
                    </div>
                    <ul class="db-list">
                        @foreach($recentPayments as $payment)
                            @php
                                $student = $payment->studentFeeAssignment?->student;
                                $name = $student?->full_name ?? 'Unknown student';
                                $initials = collect(explode(' ', $name))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
                            @endphp
                            <li>
                                <span class="db-avatar" aria-hidden="true">{{ $initials }}</span>
                                <div class="db-list-main">
                                    <strong>{{ $name }}</strong>
                                    <span>{{ optional($payment->payment_date)->format('j M') }} · {{ \Illuminate\Support\Str::of($payment->payment_method ?: 'payment')->replace('_', ' ')->title() }}</span>
                                </div>
                                <span class="db-amount">{{ \App\Support\Money::format($payment->amount) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @elseif(!empty($recentActivity) && count($recentActivity))
                <section class="db-panel" aria-labelledby="db-activity-title">
                    <div class="db-panel-head"><h2 id="db-activity-title">Recent activity</h2></div>
                    <ul class="db-list">
                        @foreach($recentActivity as $activity)
                            <li>
                                <span class="db-avatar" aria-hidden="true"><i class="fas fa-history"></i></span>
                                <div class="db-list-main">
                                    <strong>{{ $activity['action'] }} · {{ $activity['module'] }}</strong>
                                    <span>{{ $activity['user'] }} · {{ $activity['time'] }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>
    @endif

    {{-- Modules: one list, not two --}}
    @if(!empty($visibleModules))
        <section class="db-panel" aria-labelledby="db-modules-title">
            <div class="db-panel-head"><h2 id="db-modules-title">Module Status</h2></div>
            <div class="db-modules">
                @foreach($visibleModules as $mod)
                    <a href="{{ route($mod['route']) }}" class="db-module">
                        <span class="db-module-icon"><i class="fas {{ $mod['icon'] }}" aria-hidden="true"></i></span>
                        <span class="db-module-text">
                            <strong>{{ $mod['label'] }}</strong>
                            <span><i class="db-status {{ $mod['data']['status'] }}" aria-hidden="true"></i>{{ $mod['data']['label'] }}</span>
                        </span>
                        <span class="db-module-count">{{ $mod['data']['count'] }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if(empty($keyMetrics) && empty($visibleModules) && empty($visibleAlerts))
        <div class="db-panel db-empty">
            <p class="mb-1"><strong>Welcome to {{ config('app.name') }}.</strong></p>
            <p class="mb-0">Use the menu on the left to open the pages your account can use.</p>
        </div>
    @endif
</div>

@if(!empty($chartSeries))
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    var canvas = document.getElementById('chartMain');
    if (!canvas || !window.Chart) return;

    // Only the series this user may see are present.
    var series = @json($chartSeries);
    var navy = '#1d3f7a';
    var green = '#12805c';
    var grid = '#eef1f5';
    var tick = '#6b7587';
    var font = { family: getComputedStyle(document.body).fontFamily, size: 12 };
    var kes = new Intl.NumberFormat('en-KE', { maximumFractionDigits: 0 });
    var datasets = [];
    var labels;

    if (series.revenue) {
        labels = series.revenue.labels;
        datasets.push({
            type: 'bar', label: 'Fees collected', data: series.revenue.data, yAxisID: 'y',
            backgroundColor: navy, hoverBackgroundColor: '#17325f', borderRadius: 6, maxBarThickness: 44, _money: true
        });
    }

    if (series.admissions) {
        var adm = series.admissions;
        var admLabels = adm.labels.map(function (l) { return l.split(' ')[0]; });
        if (!labels) {
            labels = admLabels;
            datasets.push({
                type: 'bar', label: 'New admissions', data: adm.data, yAxisID: 'y',
                backgroundColor: navy, hoverBackgroundColor: '#17325f', borderRadius: 6, maxBarThickness: 32
            });
        } else {
            datasets.push({
                type: 'line', label: 'New admissions', yAxisID: 'y1',
                data: labels.map(function (l) { var i = admLabels.lastIndexOf(l); return i === -1 ? null : adm.data[i]; }),
                borderColor: green, backgroundColor: green, borderWidth: 2, tension: 0.3,
                pointRadius: 3, pointHoverRadius: 5
            });
        }
    }

    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    new Chart(canvas, {
        data: { labels: labels, datasets: datasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: reduce ? false : { duration: 300 },
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: datasets.length > 1, position: 'top', align: 'end', labels: { boxWidth: 10, boxHeight: 10, color: tick, font: font } },
                tooltip: {
                    backgroundColor: '#142033', titleFont: font, bodyFont: font, padding: 10, cornerRadius: 8, displayColors: datasets.length > 1,
                    callbacks: {
                        label: function (c) {
                            return c.dataset.label + ': ' + (c.dataset._money ? 'KES ' + kes.format(c.parsed.y) : c.parsed.y);
                        }
                    }
                }
            },
            scales: {
                x: { grid: { display: false }, border: { display: false }, ticks: { color: tick, font: font } },
                y: {
                    beginAtZero: true, grid: { color: grid }, border: { display: false },
                    ticks: {
                        color: tick, font: font, maxTicksLimit: 5,
                        callback: function (v) {
                            if (!series.revenue) return v;
                            return v >= 1e6 ? (v / 1e6).toFixed(1) + 'M' : v >= 1e3 ? Math.round(v / 1e3) + 'k' : v;
                        }
                    }
                },
                y1: { display: false, beginAtZero: true, grid: { display: false } }
            }
        }
    });
})();
</script>
@endif
@endsection
