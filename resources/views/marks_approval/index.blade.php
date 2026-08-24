@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold text-danger">
                        <i class="fas fa-check-double mr-2"></i> Marks Approval
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        {{-- Summary strip --}}
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="info-box elevation-1 mb-0">
                    <span class="info-box-icon bg-danger"><i class="fas fa-hourglass-half"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Pending Entries</span>
                        <span class="info-box-number">{{ number_format($totalPending) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box elevation-1 mb-0">
                    <span class="info-box-icon bg-warning"><i class="fas fa-user-graduate"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Learners Awaiting</span>
                        <span class="info-box-number">{{ number_format($totalLearners) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box elevation-1 mb-0">
                    <span class="info-box-icon bg-success"><i class="fas fa-layer-group"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Batches (Exam × Class)</span>
                        <span class="info-box-number">{{ $batches->count() }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="card elevation-2 border-0 mb-4">
            <div class="card-body py-3">
                <form action="{{ route('marks-approval.index') }}" method="GET" class="form-inline justify-content-end">
                    <label class="small font-weight-bold text-uppercase mr-2">Exam</label>
                    {!! Form::select('exam_id', $exams, request('exam_id'), ['class' => 'form-control select2 mr-2', 'placeholder' => 'All Exams', 'style' => 'width: 220px']) !!}
                    <label class="small font-weight-bold text-uppercase mr-2">Class</label>
                    {!! Form::select('class_section_id', $classSections, request('class_section_id'), ['class' => 'form-control select2 mr-2', 'placeholder' => 'All Classes', 'style' => 'width: 220px']) !!}
                    <button type="submit" class="btn btn-primary shadow-sm">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                </form>
            </div>
        </div>

        {{-- Batch table --}}
        <div class="card card-outline card-danger elevation-2 border-0">
            <div class="card-header bg-white">
                <h3 class="card-title font-weight-bold">
                    Pending Batches
                    <small class="text-muted font-weight-normal ml-2">one row per exam &amp; class stream</small>
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                        <tr>
                            <th>Exam Session</th>
                            <th>Class Stream</th>
                            <th class="text-center">Pending Entries</th>
                            <th class="text-center">Incomplete</th>
                            <th class="text-center">Learners</th>
                            <th class="text-center">Girls / Boys</th>
                            <th>Oldest Entry</th>
                            <th class="text-right pr-3" style="width: 260px;">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($batches as $batch)
                            <tr>
                                <td class="font-weight-bold text-dark">{{ $batch->exam_name }}</td>
                                <td>{{ $batch->class_name }}</td>
                                <td class="text-center">
                                    <span class="badge badge-danger px-2 py-1">{{ number_format($batch->pending_count) }}</span>
                                </td>
                                <td class="text-center">
                                    @if($batch->incomplete_count > 0)
                                        <span class="badge badge-warning px-2 py-1"
                                              title="Entries with no mark recorded yet">{{ number_format($batch->incomplete_count) }}</span>
                                    @else
                                        <span class="badge badge-light border px-2 py-1">0</span>
                                    @endif
                                </td>
                                <td class="text-center font-weight-bold">{{ number_format($batch->learners_count) }}</td>
                                <td class="text-center small">
                                    <span class="badge badge-pill badge-light border" title="Girls">
                                        <i class="fas fa-female text-info"></i> {{ $batch->girls }}
                                    </span>
                                    <span class="badge badge-pill badge-light border" title="Boys">
                                        <i class="fas fa-male text-primary"></i> {{ $batch->boys }}
                                    </span>
                                </td>
                                <td class="small text-muted">
                                    {{ $batch->oldest_entry ? \Carbon\Carbon::parse($batch->oldest_entry)->diffForHumans() : '—' }}
                                </td>
                                <td class="text-right pr-3">
                                    <a href="{{ route('marks-approval.show', [$batch->exam_id, $batch->class_section_id]) }}"
                                       class="btn btn-outline-primary btn-sm shadow-sm">
                                        <i class="fas fa-search mr-1"></i> Review &amp; Approve
                                    </a>
                                    <form action="{{ route('marks-approval.approve') }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Approve ALL {{ $batch->pending_count }} pending entries for {{ $batch->class_name }} — {{ $batch->exam_name }}?')">
                                        @csrf
                                        <input type="hidden" name="exam_id" value="{{ $batch->exam_id }}">
                                        <input type="hidden" name="class_section_id" value="{{ $batch->class_section_id }}">
                                        <button type="submit" class="btn btn-success btn-sm shadow-sm">
                                            <i class="fas fa-check-double mr-1"></i> Approve All
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-thumbs-up fa-2x text-success d-block mb-2"></i>
                                    Nothing pending for approval. All recorded marks are approved.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
