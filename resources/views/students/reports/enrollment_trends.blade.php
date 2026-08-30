@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark font-weight-bold">
                    <i class="fas fa-chart-line text-indigo mr-2"></i>Enrollment Trends
                </h1>
                <p class="text-muted small mb-0">Year-over-year student enrollment across academic years</p>
            </div>
            <div class="col-sm-6 text-right">
                <div class="btn-group mr-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-toggle="dropdown"><i class="fas fa-download mr-1"></i> Export</button>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="{{ route('student-reports.enrollment-trends.csv') }}"><i class="fas fa-file-csv text-success mr-2"></i>Download CSV</a>
                        <a class="dropdown-item" href="{{ route('student-reports.enrollment-trends.pdf') }}"><i class="fas fa-file-pdf text-danger mr-2"></i>Download PDF</a>
                    </div>
                </div>
                <button onclick="window.print()" class="btn btn-outline-info btn-sm shadow-sm">
                    <i class="fas fa-print mr-1"></i> Print
                </button>
                <a href="{{ route('student-reports.index') }}" class="btn btn-default btn-sm shadow-sm border ml-2">
                    <i class="fas fa-arrow-left mr-1"></i> Back
                </a>
            </div>
        </div>
    </div>
</div>

<div class="content px-3">
    {{-- Chart --}}
    <div class="card card-outline card-indigo elevation-2 border-0 mb-4">
        <div class="card-header bg-white">
            <h3 class="card-title font-weight-bold">Enrollment Over Time</h3>
        </div>
        <div class="card-body">
            <canvas id="enrollmentChart" style="min-height: 350px;"></canvas>
        </div>
    </div>

    {{-- Table --}}
    <div class="card card-outline card-indigo elevation-2 border-0">
        <div class="card-header bg-white">
            <h3 class="card-title font-weight-bold">Year-by-Year Breakdown</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr class="bg-light text-muted small text-uppercase">
                            <th class="pl-4">Academic Year</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Male</th>
                            <th class="text-center">Female</th>
                            <th class="text-center">Change</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($trends as $idx => $trend)
                            @php
                                $prevTotal = $idx > 0 ? $trends[$idx - 1]->total : 0;
                                $change = $prevTotal > 0 ? $trend->total - $prevTotal : 0;
                                $changePct = $prevTotal > 0 ? round(($change / $prevTotal) * 100, 1) : 0;
                            @endphp
                            <tr>
                                <td class="pl-4 font-weight-bold">
                                    {{ $trend->year_name }}
                                    @if($trend->is_current)
                                        <span class="badge badge-primary ml-1" style="font-size: 0.65rem;">CURRENT</span>
                                    @endif
                                </td>
                                <td class="text-center font-weight-bold">{{ $trend->total }}</td>
                                <td class="text-center"><i class="fas fa-male text-primary mr-1"></i>{{ $trend->male }}</td>
                                <td class="text-center"><i class="fas fa-female text-pink mr-1"></i>{{ $trend->female }}</td>
                                <td class="text-center">
                                    @if($change > 0)
                                        <span class="text-success font-weight-bold"><i class="fas fa-arrow-up"></i> +{{ $change }} ({{ $changePct }}%)</span>
                                    @elseif($change < 0)
                                        <span class="text-danger font-weight-bold"><i class="fas fa-arrow-down"></i> {{ $change }} ({{ $changePct }}%)</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($trend->is_current)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-secondary">Completed</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .text-indigo { color: #4f46e5; }
    .text-pink { color: #ec4899; }
    .card-indigo { border-top: 3px solid #4f46e5; }
    @media print {
        .btn, .main-header, .main-sidebar { display: none !important; }
        .content-wrapper { margin-left: 0 !important; }
    }
</style>

@push('page_scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    $(function () {
        const labels = {!! json_encode($trends->pluck('year_name')) !!};
        const totals = {!! json_encode($trends->pluck('total')) !!};
        const males = {!! json_encode($trends->pluck('male')) !!};
        const females = {!! json_encode($trends->pluck('female')) !!};

        new Chart(document.getElementById('enrollmentChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    { label: 'Total', data: totals, borderColor: '#4f46e5', backgroundColor: 'rgba(79,70,229,0.1)', fill: true, tension: 0.4, pointRadius: 5, pointBackgroundColor: '#4f46e5' },
                    { label: 'Male', data: males, borderColor: '#3b82f6', borderDash: [5, 5], tension: 0.4, pointRadius: 4, pointBackgroundColor: '#3b82f6' },
                    { label: 'Female', data: females, borderColor: '#ec4899', borderDash: [5, 5], tension: 0.4, pointRadius: 4, pointBackgroundColor: '#ec4899' }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [2, 2], drawBorder: false }, ticks: { precision: 0 } },
                    x: { grid: { display: false } }
                },
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 20, font: { size: 12, weight: 'bold' }, usePointStyle: true } },
                    tooltip: { backgroundColor: 'rgba(0,0,0,0.8)', padding: 12, cornerRadius: 8 }
                }
            }
        });
    });
</script>
@endpush
@endsection
