@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold text-danger">
                        <i class="fas fa-chart-bar mr-2"></i> Performance Analysis
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="card elevation-2 border-0 mb-4">
            <div class="card-body">
                <form action="{{ route('exam-analysis.performance') }}" method="GET">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <label class="small font-weight-bold text-uppercase">Select Exam</label>
                            {!! Form::select('exam_id', $exams, request('exam_id'), ['class' => 'form-control select2', 'placeholder' => 'Choose Exam Session']) !!}
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-danger shadow-sm">
                                <i class="fas fa-chart-line mr-1"></i> Generate Analysis
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if(request('exam_id'))
        <div class="row">
            <div class="col-md-3">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>450</h3>
                        <p>Total Students</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>78.5%</h3>
                        <p>Pass Rate</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>65.2</h3>
                        <p>Average Score</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>12</h3>
                        <p>Subjects Tested</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-book"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">Performance Trends</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="performanceChart" height="200"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">Grade Distribution</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="gradeChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-outline card-danger">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">Subject-wise Performance</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="pl-4">Subject</th>
                            <th class="text-center">Students</th>
                            <th class="text-center">Average</th>
                            <th class="text-center">Highest</th>
                            <th class="text-center">Lowest</th>
                            <th class="text-center">Pass Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="pl-4 font-weight-bold">Mathematics</td>
                            <td class="text-center">450</td>
                            <td class="text-center"><b class="text-primary">68.5</b></td>
                            <td class="text-center">98</td>
                            <td class="text-center">32</td>
                            <td class="text-center"><span class="badge badge-success">75%</span></td>
                        </tr>
                        <tr>
                            <td class="pl-4 font-weight-bold">English</td>
                            <td class="text-center">450</td>
                            <td class="text-center"><b class="text-primary">72.3</b></td>
                            <td class="text-center">95</td>
                            <td class="text-center">45</td>
                            <td class="text-center"><span class="badge badge-success">82%</span></td>
                        </tr>
                        <tr>
                            <td class="pl-4 font-weight-bold">Kiswahili</td>
                            <td class="text-center">450</td>
                            <td class="text-center"><b class="text-primary">65.8</b></td>
                            <td class="text-center">92</td>
                            <td class="text-center">38</td>
                            <td class="text-center"><span class="badge badge-warning">70%</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        @else
        <div class="alert alert-info border-0 shadow-sm">
            <i class="fas fa-info-circle mr-2"></i> Please select an exam to view performance analysis.
        </div>
        @endif
    </div>

    @push('page_scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        @if(request('exam_id'))
        // Performance Trends Chart
        const perfCtx = document.getElementById('performanceChart');
        new Chart(perfCtx, {
            type: 'line',
            data: {
                labels: ['Term 1', 'Term 2', 'Term 3', 'Current'],
                datasets: [{
                    label: 'Average Score',
                    data: [62, 65, 68, 65.2],
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Grade Distribution Chart
        const gradeCtx = document.getElementById('gradeChart');
        new Chart(gradeCtx, {
            type: 'doughnut',
            data: {
                labels: ['A', 'B', 'C', 'D', 'E'],
                datasets: [{
                    data: [45, 120, 180, 85, 20],
                    backgroundColor: [
                        'rgb(40, 167, 69)',
                        'rgb(23, 162, 184)',
                        'rgb(255, 193, 7)',
                        'rgb(253, 126, 20)',
                        'rgb(220, 53, 69)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
        @endif
    </script>
    @endpush
@endsection
