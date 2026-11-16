@extends('layouts.app')

@section('content')
<style>
:root {
    --blue: #2563eb; --green: #16a34a; --yellow: #d97706; --red: #dc2626;
    --surface: #ffffff; --bg: #f8fafc; --text: #0f172a; --muted: #64748b; --border: #e2e8f0;
    --radius: 12px; --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    --shadow-md: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
    --ease-out: cubic-bezier(0.23, 1, 0.32, 1);
    --ease-in-out: cubic-bezier(0.77, 0, 0.175, 1);
}
.dash-wrap { padding: 1.25rem 0.5rem; }
.dash-heading { font-size: 1.35rem; font-weight: 800; color: var(--text); letter-spacing: -.025em; margin: 0; }
.dash-sub { color: var(--muted); font-size: .813rem; margin-top: .125rem; }
.btn-dash { display: inline-flex; align-items: center; gap: .375rem; border: none; border-radius: 8px; padding: .4rem .875rem; font-size: .813rem; font-weight: 600; cursor: pointer; text-decoration: none !important; white-space: nowrap; transition: transform 150ms var(--ease-out), filter 150ms var(--ease-out), box-shadow 150ms var(--ease-out); outline: none; }
.btn-dash:focus-visible { box-shadow: 0 0 0 3px rgba(37,99,235,.3); }
@media (hover: hover) and (pointer: fine) { .btn-dash:hover { filter: brightness(.95); transform: translateY(-1px); } }
.btn-dash:active { transform: scale(0.97); }
.btn-ghost { background: #f1f5f9; color: #475569; }
.btn-primary-dash { background: var(--blue); color: #fff; box-shadow: 0 4px 12px rgba(37,99,235,.2); }
.dash-alert { display: flex; align-items: center; gap: .75rem; border-radius: var(--radius); padding: .625rem .875rem; border: 1px solid transparent; margin-bottom: .875rem; transition: transform 200ms var(--ease-out); }
.da-icon { width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: .875rem; }
.da-body { flex: 1; }
.da-title { font-weight: 700; font-size: .813rem; margin: 0 0 .125rem; }
.da-desc { font-size: .75rem; margin: 0; opacity: .8; }
.da-action { font-size: .688rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; white-space: nowrap; border-radius: 4px; padding: .2rem .625rem; text-decoration: none !important; border: 1px solid transparent; transition: background 150ms var(--ease-out); }
.alert-danger { background: #fff1f2; border-color: #fee2e2; }
.alert-danger .da-icon { background: #fee2e2; color: var(--red); }
.alert-danger .da-title { color: #991b1b; }
.alert-danger .da-desc { color: #b91c1c; }
.alert-danger .da-action { color: var(--red); border-color: #fecdd3; background: #fff; }
.alert-danger .da-action:hover { background: #fee2e2; }
.alert-warning { background: #fffbeb; border-color: #fef3c7; }
.alert-warning .da-icon { background: #fef3c7; color: var(--yellow); }
.alert-warning .da-title { color: #92400e; }
.alert-warning .da-desc { color: #a16207; }
.alert-warning .da-action { color: var(--yellow); border-color: #fde68a; background: #fff; }
.alert-warning .da-action:hover { background: #fef3c7; }
.alert-info { background: #eff6ff; border-color: #dbeafe; }
.alert-info .da-icon { background: #dbeafe; color: var(--blue); }
.alert-info .da-title { color: #1e40af; }
.alert-info .da-desc { color: #1d4ed8; }
.alert-info .da-action { color: var(--blue); border-color: #bfdbfe; background: #fff; }
.alert-info .da-action:hover { background: #dbeafe; }
.stat-card { background: var(--surface); border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow-sm); padding: 1rem; cursor: pointer; transition: transform 200ms var(--ease-out), box-shadow 200ms var(--ease-out), border-color 200ms var(--ease-out); opacity: 0; transform: translateY(8px); animation: dashCardIn 0.4s var(--ease-out) forwards; position: relative; overflow: hidden; outline: none; }
.stat-card:focus-visible { box-shadow: 0 0 0 3px rgba(37,99,235,.3), var(--shadow); }
@keyframes dashCardIn { to { opacity: 1; transform: translateY(0); } }
@media (hover: hover) and (pointer: fine) { .stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow); border-color: var(--blue); } }
.stat-card:active { transform: scale(0.98); }
.stat-card-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: .5rem; }
.stat-icon { width: 34px; height: 34px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1rem; }
.stat-badge { font-size: .65rem; font-weight: 700; padding: .2rem .4rem; border-radius: 20px; display: inline-flex; align-items: center; gap: .25rem; }
.stat-title { font-size: .813rem; color: var(--muted); font-weight: 500; margin-bottom: .125rem; }
.stat-val { font-size: 1.5rem; font-weight: 800; color: var(--text); line-height: 1.1; }
.stat-foot { font-size: .7rem; color: var(--muted); margin-top: .375rem; }
.ic-blue { background: #eff6ff; color: var(--blue); }
.ic-green { background: #f0fdf4; color: var(--green); }
.ic-yellow { background: #fffbeb; color: var(--yellow); }
.ic-red { background: #fef2f2; color: var(--red); }
.ic-purple { background: #faf5ff; color: #7c3aed; }
.ic-slate { background: #f8fafc; color: #475569; }
.sb-up { background: #f0fdf4; color: var(--green); }
.sb-down { background: #fef2f2; color: var(--red); }
.sb-none { background: #f8fafc; color: var(--muted); }
.dash-panel { background: var(--surface); border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow-sm); overflow: hidden; height: 100%; }
.dash-panel-head { display: flex; justify-content: space-between; align-items: center; padding: .75rem 1rem; border-bottom: 1px solid var(--border); }
.dash-panel-title { font-size: .938rem; font-weight: 700; color: var(--text); margin: 0; }
.dash-panel-sub { font-size: .7rem; color: var(--muted); margin: .125rem 0 0; }
.dash-panel-body { padding: 1rem; }
.module-tile { background: var(--surface); border-radius: var(--radius); border: 1px solid var(--border); padding: .75rem .5rem; display: flex; flex-direction: column; align-items: center; gap: .375rem; text-decoration: none !important; transition: transform 200ms var(--ease-out), box-shadow 200ms var(--ease-out), border-color 200ms var(--ease-out); position: relative; overflow: hidden; opacity: 0; animation: moduleTileIn 0.3s var(--ease-out) forwards; }
@keyframes moduleTileIn { to { opacity: 1; } }
.module-tile i { font-size: 1.125rem; color: var(--blue); transition: transform 200ms var(--ease-out); }
.module-tile span { font-size: .75rem; font-weight: 700; color: var(--text); }
.module-badge { position: absolute; top: 6px; right: 6px; font-size: .6rem; font-weight: 800; padding: .1rem .35rem; border-radius: 20px; }
@media (hover: hover) and (pointer: fine) { .module-tile:hover { transform: translateY(-2px); box-shadow: var(--shadow); border-color: var(--blue); } .module-tile:hover i { transform: scale(1.1); } }
.module-tile:active { transform: scale(0.97); }
.activity-list { list-style: none; margin: 0; padding: 0; }
.activity-item { display: flex; gap: .75rem; padding: .625rem 0; border-bottom: 1px solid #f1f5f9; transition: background 150ms var(--ease-out); }
.activity-item:last-child { border-bottom: none; }
.ai-dot { width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: .75rem; flex-shrink: 0; }
.ai-body { flex: 1; min-width: 0; }
.ai-title { font-weight: 600; font-size: .813rem; color: var(--text); margin: 0 0 .125rem; }
.ai-meta { display: flex; gap: .375rem; align-items: center; }
.ai-badge { font-size: .625rem; font-weight: 600; padding: .1rem .375rem; border-radius: 4px; background: #f1f5f9; color: var(--muted); }
.ai-time { font-size: .625rem; color: var(--muted); }
.health-item { display: flex; align-items: center; justify-content: space-between; padding: .625rem .75rem; border-radius: 8px; margin-bottom: .25rem; transition: all 150ms var(--ease-out); background: transparent; }
@media (hover: hover) and (pointer: fine) { .health-item:hover { background: #f8fafc; } }
.hi-label { font-size: .813rem; font-weight: 600; color: #1e293b; display: flex; align-items: center; gap: .75rem; }
.hi-icon { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 6px; background: #f1f5f9; color: var(--muted); font-size: .875rem; }
.hi-status-wrap { display: flex; align-items: center; gap: .375rem; }
.hi-status { width: 8px; height: 8px; border-radius: 50%; }
.hs-good { background: var(--green); box-shadow: 0 0 0 2px rgba(22,163,74,.1), 0 0 8px rgba(22,163,74,.4); }
.hs-warning { background: var(--yellow); box-shadow: 0 0 0 2px rgba(217,119,6,.1), 0 0 8px rgba(217,119,6,.4); }
.hs-danger { background: var(--red); box-shadow: 0 0 0 2px rgba(220,38,38,.1), 0 0 8px rgba(220,38,38,.4); }
.hs-info { background: var(--blue); box-shadow: 0 0 0 2px rgba(37,99,235,.1), 0 0 8px rgba(37,99,235,.4); }
.hi-meta { text-align: right; }
.hi-count { font-size: .813rem; font-weight: 700; color: #0f172a; line-height: 1; margin-bottom: .125rem; display: block; }
.hi-desc { font-size: .625rem; font-weight: 600; text-transform: uppercase; letter-spacing: .02em; color: var(--muted); display: block; }
.health-list { list-style: none; padding: 0; margin: 0; }
.mb-good { background: #dcfce7; color: var(--green); }
.mb-warning { background: #fef9c3; color: var(--yellow); }
.mb-danger { background: #fee2e2; color: var(--red); }
.mb-info { background: #eff6ff; color: var(--blue); }
.chart-wrap { position: relative; height: 240px; }
.section-label { font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--muted); margin: 1.25rem 0 .5rem; }
.stat-card:nth-child(1) { animation-delay: 0ms; }
.stat-card:nth-child(2) { animation-delay: 50ms; }
.stat-card:nth-child(3) { animation-delay: 100ms; }
.stat-card:nth-child(4) { animation-delay: 150ms; }
.module-tile:nth-child(1) { animation-delay: 0ms; }
.module-tile:nth-child(2) { animation-delay: 30ms; }
.module-tile:nth-child(3) { animation-delay: 60ms; }
.module-tile:nth-child(4) { animation-delay: 90ms; }
.module-tile:nth-child(5) { animation-delay: 120ms; }
.module-tile:nth-child(6) { animation-delay: 150ms; }
.module-tile:nth-child(7) { animation-delay: 180ms; }
.module-tile:nth-child(8) { animation-delay: 210ms; }
.module-tile:nth-child(9) { animation-delay: 240ms; }
.module-tile:nth-child(10) { animation-delay: 270ms; }
@media (max-width: 576px) { .dash-wrap { padding: 1rem; } .dash-heading { font-size: 1.25rem; } .dash-alert { flex-wrap: wrap; } .da-action { width: 100%; text-align: center; margin-top: .25rem; } }
@media (prefers-reduced-motion: reduce) { .stat-card { animation: none; opacity: 1; transform: none; transition: none; } .module-tile { animation: none; opacity: 1; transition: none; } .btn-dash { transition: none; } .stat-card:hover, .module-tile:hover { transform: none; } }
</style>

<div class="dash-wrap">
    {{-- HEADER --}}
    <div class="row align-items-center mb-4">
        <div class="col-md-7">
            @if(in_array($roleName, ['finance','accountant','bursar']))
                <h1 class="dash-heading">Finance Dashboard</h1>
                <p class="dash-sub">Fee & revenue overview — {{ now()->format('F Y') }}</p>
            @elseif(in_array($roleName, ['hr','human resources','hr manager']))
                <h1 class="dash-heading">HR Dashboard</h1>
                <p class="dash-sub">Staff management &amp; leave overview</p>
            @else
                <h1 class="dash-heading">Good {{ now()->hour < 12 ? 'Morning' : (now()->hour < 17 ? 'Afternoon' : 'Evening') }}, {{ explode(' ', $user->name)[0] }}</h1>
                <p class="dash-sub text-muted">{{ now()->format('l, d F Y') }} • System Overview</p>
            @endif
        </div>
        <div class="col-md-5 text-md-end mt-2 mt-md-0">
            <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                @if(in_array($roleName, ['finance','accountant','bursar']))
                    @if($moduleManager->isActive('finance'))
                        <a href="{{ route('financial-reports.index') }}" class="btn-dash btn-ghost"><i class="fas fa-file-alt"></i> Reports</a>
                    @endif
                    @if($moduleManager->isActive('fees'))
                        <a href="{{ route('fees.collect') }}" class="btn-dash btn-primary-dash"><i class="fas fa-money-check-alt"></i> Collect Fees</a>
                    @endif
                @elseif(in_array($roleName, ['hr','human resources','hr manager']))
                    @if($moduleManager->isActive('hr'))
                        <a href="{{ route('leave-applications.index') }}" class="btn-dash btn-ghost"><i class="fas fa-calendar-alt"></i> Leave Requests</a>
                        <a href="{{ route('hr.onboarding') }}" class="btn-dash btn-primary-dash"><i class="fas fa-user-plus"></i> Onboard Staff</a>
                    @endif
                @else
                    @if($moduleManager->isActive('students') && \App\Services\MenuService::canSee($user, ['students.manage']))
                        <a href="{{ route('students.create') }}" class="btn-dash btn-ghost"><i class="fas fa-user-graduate"></i> Admit</a>
                    @endif
                    @if($moduleManager->isActive('academics') && \App\Services\MenuService::canSee($user, ['academics.view', 'academics.manage']))
                        <a href="{{ route('school-classes.index') }}" class="btn-dash btn-ghost"><i class="fas fa-chalkboard"></i> Classes</a>
                    @endif
                    @if($moduleManager->isActive('fees') && \App\Services\MenuService::canSee($user, ['fees.view', 'fees.collect']))
                        <a href="{{ route('fee-management.index') }}" class="btn-dash btn-primary-dash"><i class="fas fa-coins"></i> Fees</a>
                    @endif
                @endif
            </div>
        </div>
    </div>

    {{-- ALERTS --}}
    @if(!empty($visibleAlerts))
    <div class="row mb-2">
        <div class="col-12">
            @foreach($visibleAlerts as $alert)
            <div class="dash-alert alert-{{ $alert['type'] }}">
                <div class="da-icon"><i class="fas {{ $alert['icon'] }}"></i></div>
                <div class="da-body">
                    <p class="da-title">{{ $alert['title'] }}</p>
                    <p class="da-desc">{{ $alert['desc'] }}</p>
                </div>
                <a href="{{ route($alert['actionRoute']) }}" class="da-action">{{ $alert['action'] }}</a>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- KEY METRICS (server-side filtered by permission in DashboardController) --}}
    @if(!empty($keyMetrics))
    <p class="section-label">Key Metrics</p>
    <div class="row mb-4">
        @foreach($keyMetrics as $metric)
            <div class="{{ $metric['col'] }} mb-3">
                <div class="stat-card" onclick="window.location='{{ route($metric['route']) }}'">
                    <div class="stat-card-top">
                        <div class="stat-icon {{ $metric['color'] }}"><i class="fas {{ $metric['icon'] }}"></i></div>
                        <span class="stat-badge {{ $metric['badgeClass'] }}">
                            @if(!empty($metric['badgeIcon']))<i class="fas {{ $metric['badgeIcon'] }}"></i> @endif{{ $metric['badge'] }}
                        </span>
                    </div>
                    <p class="stat-title">{{ $metric['title'] }}</p>
                    <div class="stat-val">{{ $metric['value'] }}</div>
                    <p class="stat-foot">{{ $metric['foot'] }}</p>
                </div>
            </div>
        @endforeach
    </div>
    @endif

    {{-- MODULE TILES --}}
    @if(!empty($visibleModules))
    <p class="section-label">Module Dashboards</p>
    <div class="row mb-4">
        @foreach($visibleModules as $mod)
            <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3">
                <a href="{{ route($mod['route']) }}" class="module-tile">
                    <span class="module-badge mb-{{ $mod['data']['status'] }}">{{ $mod['data']['count'] }}</span>
                    <i class="fas {{ $mod['icon'] }}"></i>
                    <span>{{ $mod['label'] }}</span>
                </a>
            </div>
        @endforeach
    </div>
    @endif

    {{-- MAIN CONTENT --}}
    <div class="row">
        {{-- Chart (only when at least one dataset survived permission filtering) --}}
        @if(!empty($chartSeries))
        <div class="col-lg-8">
            <div class="dash-panel mb-4">
                <div class="dash-panel-head">
                    <div>
                        <h3 class="dash-panel-title">Growth & Revenue</h3>
                        <p class="dash-panel-sub">
                            @if(isset($chartSeries['admissions']) && isset($chartSeries['revenue']))
                                Admissions vs. Collections Performance
                            @elseif(isset($chartSeries['admissions']))
                                Admissions Performance
                            @else
                                Collections Performance
                            @endif
                        </p>
                    </div>
                </div>
                <div class="dash-panel-body">
                    <div class="chart-wrap">
                        <canvas id="chartMain"></canvas>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Right column: Health + Activity --}}
        <div class="{{ !empty($chartSeries) ? 'col-lg-4' : 'col-lg-12' }}">
            {{-- Module Health --}}
            @if(!empty($visibleModules))
            <div class="dash-panel mb-4">
                <div class="dash-panel-head">
                    <h3 class="dash-panel-title">Module Status</h3>
                </div>
                <div class="dash-panel-body">
                    <div class="health-list">
                        @foreach($visibleModules as $mod)
                        <div class="health-item">
                            <div class="hi-label">
                                <div class="hi-icon">
                                    <i class="fas {{ $mod['icon'] }}"></i>
                                </div>
                                <div>
                                    <span class="d-block">{{ $mod['label'] }}</span>
                                    <div class="hi-status-wrap">
                                        <div class="hi-status hs-{{ $mod['data']['status'] }}"></div>
                                        <span style="font-size: .625rem; font-weight: 700; color: var(--muted);">{{ strtoupper($mod['data']['status']) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="hi-meta">
                                <span class="hi-count">{{ $mod['data']['count'] }}</span>
                                <span class="hi-desc">{{ $mod['data']['label'] }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- Recent Activity hidden --}}
        </div>
    </div>
</div>

@if(!empty($chartSeries))
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
(function () {
    const ctx = document.getElementById('chartMain');
    if (!ctx) return;
    const indigo = '#4f46e5';
    const emerald = '#10b981';
    const slate = '#64748b';
    const chartCtx = ctx.getContext('2d');
    const indigoGradient = chartCtx.createLinearGradient(0, 0, 0, 300);
    indigoGradient.addColorStop(0, 'rgba(79, 70, 229, 0.12)');
    indigoGradient.addColorStop(0.5, 'rgba(79, 70, 229, 0.04)');
    indigoGradient.addColorStop(1, 'rgba(79, 70, 229, 0)');
    const emeraldGradient = chartCtx.createLinearGradient(0, 0, 0, 300);
    emeraldGradient.addColorStop(0, 'rgba(16, 185, 129, 0.08)');
    emeraldGradient.addColorStop(1, 'rgba(16, 185, 129, 0)');

    // Only the datasets the user is permitted to see are present here.
    const series = @json($chartSeries);
    const labels = (series.admissions || series.revenue).labels;
    const datasets = [];

    if (series.admissions) {
        datasets.push({
            label: 'Admissions', data: series.admissions.data, borderColor: indigo, backgroundColor: indigoGradient,
            fill: true, tension: 0.45, borderWidth: 3, pointRadius: 0, pointHoverRadius: 5,
            pointHoverBackgroundColor: '#fff', pointHoverBorderColor: indigo, pointHoverBorderWidth: 3, yAxisID: 'y',
            _kind: 'students'
        });
    }

    if (series.revenue) {
        const feeLabels = series.revenue.labels;
        const feeData = series.revenue.data;
        datasets.push({
            label: 'Fee Revenue',
            data: labels.map(label => { const month = label.split(' ')[0]; const index = feeLabels.indexOf(month); return index !== -1 ? feeData[index] : null; }),
            borderColor: emerald, backgroundColor: 'transparent', borderWidth: 2, borderDash: [6, 4],
            tension: 0.45, pointRadius: 0, pointHoverRadius: 4, pointHoverBackgroundColor: '#fff',
            pointHoverBorderColor: emerald, pointHoverBorderWidth: 2, yAxisID: 'y1',
            _kind: 'revenue'
        });
    }

    const crosshairLine = {
        id: 'crosshairLine',
        afterDraw: (chart) => {
            if (chart.tooltip?._active?.length) {
                const activePoint = chart.tooltip._active[0];
                const { ctx } = chart;
                const { x } = activePoint.element;
                const topY = chart.scales.y.top;
                const bottomY = chart.scales.y.bottom;
                ctx.save();
                ctx.beginPath();
                ctx.moveTo(x, topY);
                ctx.lineTo(x, bottomY);
                ctx.lineWidth = 1;
                ctx.strokeStyle = 'rgba(203, 213, 225, 0.8)';
                ctx.setLineDash([4, 4]);
                ctx.stroke();
                ctx.restore();
            }
        }
    };
    new Chart(ctx, {
        type: 'line',
        plugins: [crosshairLine],
        data: { labels: labels, datasets: datasets },
        options: {
            responsive: true, maintainAspectRatio: false, layout: { padding: { top: 10 } },
            animation: { duration: 1000, easing: 'easeOutQuart' },
            interaction: { intersect: false, mode: 'index' },
            plugins: {
                legend: { display: true, position: 'top', align: 'end', labels: { boxWidth: 6, boxHeight: 6, usePointStyle: true, pointStyle: 'circle', font: { size: 11, weight: '700' }, padding: 15, color: slate } },
                tooltip: {
                    enabled: true, backgroundColor: 'rgba(255, 255, 255, 0.98)', titleColor: '#1e293b',
                    bodyColor: '#64748b', borderColor: '#e2e8f0', borderWidth: 1, cornerRadius: 12, padding: 14,
                    boxPadding: 8, usePointStyle: true, titleFont: { size: 14, weight: '800' }, bodyFont: { size: 12, weight: '500' },
                    shadowBlur: 10, shadowColor: 'rgba(0,0,0,0.1)',
                    callbacks: { label: function(context) { let label = context.dataset.label || ''; if (label) label += ': '; if (context.dataset._kind === 'revenue') { label += 'KES ' + new Intl.NumberFormat().format(context.parsed.y); } else { label += context.parsed.y + ' students'; } return label; } }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 11, weight: '500' }, padding: 10 } },
                y: { position: 'left', grid: { color: '#f1f5f9', drawBorder: false }, ticks: { color: slate, font: { size: 11, weight: '500' }, padding: 10, stepSize: 5 }, beginAtZero: true },
                y1: { position: 'right', display: false, grid: { display: false } }
            }
        }
    });
})();
</script>
@endif
@endsection
