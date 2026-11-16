@extends('layouts.portal')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        {{ $exam->name }} — {{ $student->full_name }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('portal.report-cards') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Overview -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-chart-line"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Average</span>
                                    <span class="info-box-number">{{ $overview['average'] }}%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-trophy"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Class Position</span>
                                    <span class="info-box-number">{{ $overview['class_position'] }} / {{ $overview['total_students'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-star"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Marks</span>
                                    <span class="info-box-number">{{ $overview['total_marks'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger"><i class="fas fa-crown"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Highest Total</span>
                                    <span class="info-box-number">{{ $overview['highest_total'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Results Table -->
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Class</th>
                                <th>Marks Obtained</th>
                                <th>Grade</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($results as $result)
                            <tr>
                                <td>{{ $result->subject->name ?? 'N/A' }}</td>
                                <td>{{ trim(($result->classSection?->schoolClass?->name ?? '') . ' ' . ($result->classSection?->section?->name ?? '')) ?: 'N/A' }}</td>
                                <td>{{ $result->marks_obtained ?? 'N/A' }}</td>
                                <td>
                                    @if($result->grade)
                                        <span class="badge badge-info">{{ $result->grade->name ?? $result->grade->grade_name ?? 'N/A' }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $result->remarks ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
