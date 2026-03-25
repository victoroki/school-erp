@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">
                    <i class="fas fa-calendar-check text-success mr-2"></i>Attendance Summary
                </h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('student-reports.index') }}" class="btn btn-default shadow-sm border">
                    <i class="fas fa-arrow-left mr-1"></i> Back
                </a>
            </div>
        </div>
    </div>
</div>

<div class="content px-3">
    <div class="row">
        <div class="col-md-5">
            <div class="card card-outline card-success elevation-2">
                <div class="card-header bg-white">
                    <h3 class="card-title font-weight-bold">Status Breakdown</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Status</th>
                                <th class="text-center">Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $total = $data->sum('count'); @endphp
                            @forelse($data as $row)
                                <tr>
                                    <td class="text-capitalize">
                                        @php
                                            $icon = 'check-circle text-success';
                                            if($row->status == 'absent') $icon = 'times-circle text-danger';
                                            if($row->status == 'late') $icon = 'clock text-warning';
                                            if($row->status == 'half_day') $icon = 'adjust text-info';
                                        @endphp
                                        <i class="fas fa-{{ $icon }} mr-2"></i>
                                        {{ str_replace('_', ' ', $row->status) }}
                                    </td>
                                    <td class="text-center font-weight-bold">{{ $row->count }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center py-4 text-muted">No attendance data recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($total > 0)
                        <tfoot class="bg-light font-weight-bold">
                            <tr>
                                <td>OVERALL TOTAL</td>
                                <td class="text-center text-primary">{{ $total }}</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card card-outline card-success elevation-2">
                <div class="card-header bg-white">
                    <h3 class="card-title font-weight-bold">Visual Summary</h3>
                </div>
                <div class="card-body">
                    <canvas id="attendanceChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@push('page_scripts')
<!-- Using fixed Chart.js version for stability -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    $(function () {
        const renderAttendanceChart = () => {
            const chartData = {!! json_encode($data->pluck('count')) !!};
            if (!chartData || chartData.length === 0) {
                console.warn("No attendance data available for charting.");
                return;
            }

            const ctx = document.getElementById('attendanceChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($data->pluck('status')->map(fn($s) => ucfirst(str_replace('_', ' ', (string)$s)))) !!},
                    datasets: [{
                        label: 'Total Instances',
                        data: chartData,
                        backgroundColor: [
                            '#10b981', // Present (Emerald)
                            '#f43f5e', // Absent (Rose)
                            '#f59e0b', // Late (Amber)
                            '#3b82f6', // Half Day (Blue)
                            '#6b7280'  // Excused (Gray)
                        ],
                        borderRadius: 8,
                        barThickness: 35
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { 
                            beginAtZero: true,
                            grid: { borderDash: [2, 2], drawBorder: false },
                            ticks: { precision: 0 }
                        },
                        x: {
                            grid: { display: false }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(0,0,0,0.8)',
                            padding: 12,
                            cornerRadius: 8
                        }
                    }
                }
            });
        };

        if (typeof Chart !== 'undefined') {
            renderAttendanceChart();
        } else {
            console.error("Chart.js library failed to load.");
        }
    });
</script>
@endpush
@endsection
