@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark font-weight-bold">
                    <i class="fas fa-birthday-cake text-primary mr-2"></i>Age Distribution
                </h1>
                <p class="text-muted small mb-0">Student age demographics across the institution</p>
            </div>
            <div class="col-sm-6 text-right">
                <div class="btn-group mr-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-toggle="dropdown"><i class="fas fa-download mr-1"></i> Export</button>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="{{ route('student-reports.age-distribution.csv') }}"><i class="fas fa-file-csv text-success mr-2"></i>Download CSV</a>
                        <a class="dropdown-item" href="{{ route('student-reports.age-distribution.pdf') }}"><i class="fas fa-file-pdf text-danger mr-2"></i>Download PDF</a>
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
    {{-- Stats --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stat-card border-left-primary">
                <div class="stat-icon"><i class="fas fa-users text-primary"></i></div>
                <div>
                    <div class="stat-value text-primary">{{ $totalStudents }}</div>
                    <div class="stat-label">Total Active Students</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card border-left-info">
                <div class="stat-icon"><i class="fas fa-calculator text-info"></i></div>
                <div>
                    <div class="stat-value text-info">{{ $avgAge }} yrs</div>
                    <div class="stat-label">Average Age</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            @php $largestGroup = $ageGroups->max(); $largestLabel = $ageGroups->keys()->first(); @endphp
            @foreach($ageGroups as $label => $count)
                @if($count === $largestGroup)
                    @php $largestLabel = $label; @endphp
                @endif
            @endforeach
            <div class="stat-card border-left-success">
                <div class="stat-icon"><i class="fas fa-chart-bar text-success"></i></div>
                <div>
                    <div class="stat-value text-success">{{ $largestLabel }}</div>
                    <div class="stat-label">Largest Age Group ({{ $largestGroup }} students)</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Chart --}}
        <div class="col-md-7">
            <div class="card card-outline card-primary elevation-2 border-0">
                <div class="card-header bg-white">
                    <h3 class="card-title font-weight-bold">Age Group Breakdown</h3>
                </div>
                <div class="card-body">
                    <canvas id="ageChart" style="min-height: 320px;"></canvas>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="col-md-5">
            <div class="card card-outline card-primary elevation-2 border-0">
                <div class="card-header bg-white">
                    <h3 class="card-title font-weight-bold">Detailed Count</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr class="bg-light text-muted small text-uppercase">
                                <th class="pl-4">Age Group</th>
                                <th class="text-center">Count</th>
                                <th class="text-center">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ageGroups as $label => $count)
                                @php $pct = $totalStudents > 0 ? round(($count / $totalStudents) * 100, 1) : 0; @endphp
                                <tr>
                                    <td class="pl-4 font-weight-bold">{{ $label }}</td>
                                    <td class="text-center font-weight-bold">{{ $count }}</td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <div class="progress flex-grow-1 mr-2" style="height: 6px; max-width: 80px;">
                                                <div class="progress-bar bg-primary" style="width: {{ $pct }}%"></div>
                                            </div>
                                            <small class="text-muted">{{ $pct }}%</small>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .stat-card {
        background: #fff; border: 1px solid #e5e7eb; border-radius: 12px;
        padding: 1rem 1.25rem; display: flex; align-items: center; gap: 1rem; border-left: 4px solid;
    }
    .stat-icon { font-size: 1.5rem; }
    .stat-value { font-size: 1.15rem; font-weight: 800; color: #1e293b; }
    .stat-label { font-size: 0.72rem; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.05em; }
    @media print {
        .btn, .main-header, .main-sidebar { display: none !important; }
        .content-wrapper { margin-left: 0 !important; }
    }
</style>

@push('page_scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    $(function () {
        const labels = {!! json_encode($ageGroups->keys()) !!};
        const data = {!! json_encode($ageGroups->values()) !!};

        new Chart(document.getElementById('ageChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Students',
                    data: data,
                    backgroundColor: ['#f43f5e', '#f59e0b', '#3b82f6', '#10b981', '#8b5cf6', '#ec4899', '#6b7280'],
                    borderRadius: 8,
                    barThickness: 35
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [2, 2], drawBorder: false }, ticks: { precision: 0 } },
                    x: { grid: { display: false } }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: { backgroundColor: 'rgba(0,0,0,0.8)', padding: 12, cornerRadius: 8 }
                }
            }
        });
    });
</script>
@endpush
@endsection
