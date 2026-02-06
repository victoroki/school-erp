@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold text-danger">
                        <i class="fas fa-chart-pie mr-2"></i> Subject Analysis
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="card elevation-2 border-0 mb-4">
            <div class="card-body">
                <form action="{{ route('exam-analysis.subject') }}" method="GET">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <label class="small font-weight-bold text-uppercase">Select Exam</label>
                            {!! Form::select('exam_id', $exams, request('exam_id'), ['class' => 'form-control select2', 'placeholder' => 'Choose Exam Session']) !!}
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-danger shadow-sm">
                                <i class="fas fa-search mr-1"></i> Analyze Subjects
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if(request('exam_id'))
        <div class="row">
            <div class="col-md-4">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">Top Performing Subjects</h3>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-trophy text-warning mr-2"></i> English</span>
                                <span class="badge badge-success badge-pill">72.3%</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-medal text-secondary mr-2"></i> Chemistry</span>
                                <span class="badge badge-success badge-pill">70.5%</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-award text-bronze mr-2"></i> Mathematics</span>
                                <span class="badge badge-success badge-pill">68.5%</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-outline card-warning">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">Needs Improvement</h3>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-exclamation-triangle text-warning mr-2"></i> Physics</span>
                                <span class="badge badge-warning badge-pill">58.2%</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-exclamation-triangle text-warning mr-2"></i> History</span>
                                <span class="badge badge-warning badge-pill">60.1%</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-exclamation-triangle text-warning mr-2"></i> Geography</span>
                                <span class="badge badge-warning badge-pill">62.3%</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">Subject Statistics</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted">Total Subjects Tested</small>
                            <h3 class="mb-0">12</h3>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">Average Pass Rate</small>
                            <h3 class="mb-0 text-success">76.5%</h3>
                        </div>
                        <div>
                            <small class="text-muted">Overall Mean Score</small>
                            <h3 class="mb-0 text-primary">65.2</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-outline card-danger">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">Detailed Subject Breakdown</h3>
                <div class="card-tools">
                    <button class="btn btn-sm btn-outline-success" onclick="window.print()">
                        <i class="fas fa-print mr-1"></i> Print Report
                    </button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="subjectComparisonChart" height="100"></canvas>
            </div>
        </div>

        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">Subject Performance Table</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="pl-4">Subject</th>
                            <th class="text-center">Mean Score</th>
                            <th class="text-center">Median</th>
                            <th class="text-center">Mode</th>
                            <th class="text-center">Std Dev</th>
                            <th class="text-center">Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="pl-4 font-weight-bold">Mathematics</td>
                            <td class="text-center">68.5</td>
                            <td class="text-center">70</td>
                            <td class="text-center">72</td>
                            <td class="text-center">12.3</td>
                            <td class="text-center"><span class="badge badge-success">B</span></td>
                        </tr>
                        <tr>
                            <td class="pl-4 font-weight-bold">English</td>
                            <td class="text-center">72.3</td>
                            <td class="text-center">74</td>
                            <td class="text-center">75</td>
                            <td class="text-center">10.8</td>
                            <td class="text-center"><span class="badge badge-success">B+</span></td>
                        </tr>
                        <tr>
                            <td class="pl-4 font-weight-bold">Kiswahili</td>
                            <td class="text-center">65.8</td>
                            <td class="text-center">66</td>
                            <td class="text-center">68</td>
                            <td class="text-center">11.5</td>
                            <td class="text-center"><span class="badge badge-info">B-</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        @else
        <div class="alert alert-info border-0 shadow-sm">
            <i class="fas fa-info-circle mr-2"></i> Please select an exam to view subject analysis.
        </div>
        @endif
    </div>

    @push('page_scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        @if(request('exam_id'))
        const ctx = document.getElementById('subjectComparisonChart');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Math', 'English', 'Kiswahili', 'Biology', 'Chemistry', 'Physics', 'History', 'Geography', 'CRE', 'Business', 'Agriculture', 'Computer'],
                datasets: [{
                    label: 'Average Score',
                    data: [68.5, 72.3, 65.8, 67.2, 70.5, 58.2, 60.1, 62.3, 66.5, 64.8, 63.2, 69.1],
                    backgroundColor: 'rgba(220, 53, 69, 0.5)',
                    borderColor: 'rgba(220, 53, 69, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });
        @endif
    </script>
    @endpush
@endsection
