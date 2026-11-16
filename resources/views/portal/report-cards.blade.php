@extends('layouts.portal')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Report Cards — {{ $student->full_name }}</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    @if($exams->isEmpty())
                        <p class="text-muted p-3">No exam results available yet.</p>
                    @else
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>Exam</th>
                                <th>Type</th>
                                <th>Academic Year</th>
                                <th>Term</th>
                                <th>Date Range</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($exams as $exam)
                            <tr>
                                <td>{{ $exam->name }}</td>
                                <td>{{ $exam->examType->name ?? 'N/A' }}</td>
                                <td>{{ $exam->academicYear->name ?? 'N/A' }}</td>
                                <td>{{ $exam->termModel?->name ?? 'N/A' }}</td>
                                <td>{{ $exam->start_date?->format('d/m/Y') ?? '—' }} — {{ $exam->end_date?->format('d/m/Y') ?? '—' }}</td>
                                <td>
                                    <a href="{{ route('portal.report-cards.show', $exam->exam_id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-file-alt"></i> View Report
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
