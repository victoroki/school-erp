@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold text-danger">
                        <i class="fas fa-book-open mr-2"></i> Subject Analysis
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
                                <i class="fas fa-chart-bar mr-1"></i> Generate Analysis
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if(! request('exam_id'))
            <div class="alert alert-info border-0 shadow-sm">
                <i class="fas fa-info-circle mr-2"></i> Please select an exam to view subject analysis.
            </div>
        @elseif(! $analysis || $analysis['subjects']->isEmpty())
            <div class="alert alert-warning border-0 shadow-sm">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                No subject marks have been recorded for this exam yet, so there is nothing to analyse.
            </div>
        @else
            @php
                $ranked = $analysis['subjects'];
                $top = $ranked->take(3);
                // Weakest three, excluding anything already listed as a top performer
                // when there are fewer than six subjects.
                $weak = $ranked->sortBy('average')->take(3)->reject(fn ($s) => $top->contains('name', $s['name']));
                $overall = $analysis['overall'];
            @endphp

            <div class="row">
                <div class="col-md-4">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title font-weight-bold">Top Performing Subjects</h3>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                @foreach($top as $subject)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span><i class="fas fa-trophy text-warning mr-2"></i> {{ $subject['name'] }}</span>
                                        <span class="badge badge-success badge-pill">{{ number_format($subject['average'], 1) }}%</span>
                                    </li>
                                @endforeach
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
                                @forelse($weak as $subject)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span><i class="fas fa-exclamation-triangle text-warning mr-2"></i> {{ $subject['name'] }}</span>
                                        <span class="badge badge-warning badge-pill">{{ number_format($subject['average'], 1) }}%</span>
                                    </li>
                                @empty
                                    <li class="list-group-item text-muted">Too few subjects to compare.</li>
                                @endforelse
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
                                <h3 class="mb-0">{{ number_format($overall['subjects_tested']) }}</h3>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted">Average Pass Rate</small>
                                <h3 class="mb-0 text-success">{{ number_format($overall['pass_rate'], 1) }}%</h3>
                            </div>
                            <div>
                                <small class="text-muted">Overall Mean Score (%)</small>
                                <h3 class="mb-0 text-primary">{{ number_format($overall['average'], 1) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-danger">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">Subject Comparison</h3>
                </div>
                <div class="card-body">
                    <canvas id="subjectComparisonChart" height="100"></canvas>
                </div>
            </div>

            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">Subject Performance Table</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-outline-success" onclick="window.print()">
                            <i class="fas fa-print mr-1"></i> Print Report
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="pl-4">Subject</th>
                                <th class="text-center">Learners</th>
                                <th class="text-center">Mean %</th>
                                <th class="text-center">Median</th>
                                <th class="text-center">Mode</th>
                                <th class="text-center">Std Dev</th>
                                <th class="text-center">Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ranked as $subject)
                                <tr>
                                    <td class="pl-4 font-weight-bold">{{ $subject['name'] }}</td>
                                    <td class="text-center">{{ number_format($subject['students']) }}</td>
                                    <td class="text-center">{{ number_format($subject['average'], 1) }}</td>
                                    <td class="text-center">{{ number_format($subject['median'], 1) }}</td>
                                    <td class="text-center">{{ number_format($subject['mode'], 1) }}</td>
                                    <td class="text-center">{{ number_format($subject['std_dev'], 1) }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ \App\Support\GradeBadge::for($subject['grade']) }}">
                                            {{ $subject['grade'] ?? '—' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    @push('page_scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        @if($analysis && $analysis['subjects']->isNotEmpty())
        new Chart(document.getElementById('subjectComparisonChart'), {
            type: 'bar',
            data: {
                labels: @json($analysis['subjects']->pluck('name')),
                datasets: [{
                    label: 'Average score (%)',
                    data: @json($analysis['subjects']->pluck('average')),
                    backgroundColor: 'rgba(220, 53, 69, 0.5)',
                    borderColor: 'rgba(220, 53, 69, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, max: 100 } }
            }
        });
        @endif
    </script>
    @endpush
@endsection
