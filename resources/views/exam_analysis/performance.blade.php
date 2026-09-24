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

        @if(! request('exam_id'))
            <div class="alert alert-info border-0 shadow-sm">
                <i class="fas fa-info-circle mr-2"></i> Please select an exam to view performance analysis.
            </div>
        @elseif(! $analysis || $analysis['overall']['students'] === 0)
            {{-- Explicit empty state. These screens used to show invented figures
                 for an exam with no marks at all. --}}
            <div class="alert alert-warning border-0 shadow-sm">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                No marks have been recorded for this exam yet, so there is nothing to analyse.
            </div>
        @else
            @php $overall = $analysis['overall']; @endphp

            <div class="row">
                <div class="col-md-3">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ number_format($overall['students']) }}</h3>
                            <p>Learners Assessed</p>
                        </div>
                        <div class="icon"><i class="fas fa-users"></i></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ number_format($overall['pass_rate'], 1) }}%</h3>
                            <p>Pass Rate</p>
                        </div>
                        <div class="icon"><i class="fas fa-check-circle"></i></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ number_format($overall['average'], 1) }}</h3>
                            <p>Average Score (%)</p>
                        </div>
                        <div class="icon"><i class="fas fa-chart-line"></i></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>{{ number_format($overall['subjects_tested']) }}</h3>
                            <p>Subjects Tested</p>
                        </div>
                        <div class="icon"><i class="fas fa-book"></i></div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            {{-- Real series: the mean score of each of the most recent
                                 exams, oldest first. Exam results carry no term of their
                                 own, so labelling this "Term 1..Current" was fiction. --}}
                            <h3 class="card-title font-weight-bold">Average Score by Exam</h3>
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
                                <th class="text-center">Learners</th>
                                <th class="text-center">Average %</th>
                                <th class="text-center">Highest %</th>
                                <th class="text-center">Lowest %</th>
                                <th class="text-center">Pass Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($analysis['subjects'] as $subject)
                                <tr>
                                    <td class="pl-4 font-weight-bold">{{ $subject['name'] }}</td>
                                    <td class="text-center">{{ number_format($subject['students']) }}</td>
                                    <td class="text-center"><b class="text-primary">{{ number_format($subject['average'], 1) }}</b></td>
                                    <td class="text-center">{{ number_format($subject['highest'], 1) }}</td>
                                    <td class="text-center">{{ number_format($subject['lowest'], 1) }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $subject['pass_rate'] >= 50 ? 'badge-success' : 'badge-warning' }}">
                                            {{ number_format($subject['pass_rate'], 1) }}%
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        No subject marks recorded for this exam.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    @push('page_scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        @if($analysis && $analysis['overall']['students'] > 0)
        new Chart(document.getElementById('performanceChart'), {
            type: 'line',
            data: {
                labels: @json($analysis['trend']['labels']),
                datasets: [{
                    label: 'Average score (%)',
                    data: @json($analysis['trend']['data']),
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        new Chart(document.getElementById('gradeChart'), {
            type: 'doughnut',
            data: {
                labels: @json($analysis['grades']['labels']),
                datasets: [{
                    data: @json($analysis['grades']['counts']),
                    backgroundColor: @json($analysis['grades']['colours'])
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
        @endif
    </script>
    @endpush
@endsection
