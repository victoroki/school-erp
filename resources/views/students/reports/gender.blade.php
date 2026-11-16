@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">
                    <i class="fas fa-venus-mars text-info mr-2"></i>Gender Distribution
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
        <div class="col-md-6">
            <div class="card card-outline card-info elevation-2">
                <div class="card-header bg-white">
                    <h3 class="card-title font-weight-bold">Summary Table</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Gender</th>
                                <th class="text-center">Count</th>
                                <th class="text-center">Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php 
                                $total = $data->sum('count');
                            @endphp
                            @foreach($data as $row)
                                <tr>
                                    <td class="text-capitalize">
                                        <i class="fas fa-{{ $row->gender == 'male' ? 'mars text-primary' : ($row->gender == 'female' ? 'venus text-pink' : 'user text-muted') }} mr-2"></i>
                                        {{ $row->gender }}
                                    </td>
                                    <td class="text-center font-weight-bold">{{ $row->count }}</td>
                                    <td class="text-center">
                                        {{ $total > 0 ? round(($row->count / $total) * 100, 1) : 0 }}%
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light font-weight-bold">
                            <tr>
                                <td>TOTAL</td>
                                <td class="text-center">{{ $total }}</td>
                                <td class="text-center">100%</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-outline card-info elevation-2">
                <div class="card-header bg-white">
                    <h3 class="card-title font-weight-bold">Visual Distribution</h3>
                </div>
                <div class="card-body">
                    <canvas id="genderChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .text-pink { color: #e83e8c; }
</style>

@push('page_scripts')
<!-- Using fixed Chart.js version for stability -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    $(function () {
        const renderGenderChart = () => {
            const chartData = {!! json_encode($data->pluck('count')) !!};
            if (!chartData || chartData.length === 0) {
                console.warn("No gender data available for charting.");
                return;
            }

            const ctx = document.getElementById('genderChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut', // Pie or Doughnut looks more premium
                data: {
                    labels: {!! json_encode($data->pluck('gender')->map(fn($g) => ucfirst((string)$g))) !!},
                    datasets: [{
                        data: chartData,
                        backgroundColor: [
                            '#3b82f6', // Male (Blue)
                            '#f43f5e', // Female (Pink)
                            '#6b7280', // Other/Unknown (Gray)
                            '#fbbf24', // Amber
                            '#10b981'  // Emerald
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff',
                        hoverOffset: 12
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                font: { size: 12, weight: 'bold' },
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0,0,0,0.8)',
                            padding: 12,
                            cornerRadius: 8,
                            titleFont: { size: 14 }
                        }
                    }
                }
            });
        };

        // Delay slightly to ensure reliable rendering
        if (typeof Chart !== 'undefined') {
            renderGenderChart();
        } else {
            console.error("Chart.js library failed to load.");
        }
    });
</script>
@endpush
@endsection
