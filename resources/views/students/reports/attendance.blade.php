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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(function () {
        var ctx = document.getElementById('attendanceChart').getContext('2d');
        var attendanceChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($data->pluck('status')->map(fn($s) => ucfirst(str_replace('_', ' ', $s)))) !!},
                datasets: [{
                    label: 'Number of Days',
                    data: {!! json_encode($data->pluck('count')) !!},
                    backgroundColor: [
                        '#28a745', // present
                        '#dc3545', // absent
                        '#ffc107', // late
                        '#17a2b8', // half_day
                        '#6c757d'  // excused
                    ],
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    });
</script>
@endpush
@endsection
