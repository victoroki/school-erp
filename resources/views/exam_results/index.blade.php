@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold text-danger">
                        <i class="fas fa-edit mr-2"></i> Exam Results
                    </h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a class="btn btn-primary shadow-sm" href="{{ route('exam-results.bulk') }}">
                        <i class="fas fa-keyboard mr-1"></i> Enter / Edit Marks
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('flash::message')

        {{-- Filter bar --}}
        <div class="card elevation-2 border-0 mb-4">
            <div class="card-body py-3">
                <form action="{{ route('exam-results.index') }}" method="GET" class="form-inline justify-content-end flex-wrap">
                    <label class="small font-weight-bold text-uppercase mr-2">Exam</label>
                    {!! Form::select('exam_id', $exams, request('exam_id'), ['class' => 'form-control select2 mr-2 mb-1', 'placeholder' => 'All Exams', 'style' => 'width: 210px']) !!}
                    <label class="small font-weight-bold text-uppercase mr-2">Class</label>
                    {!! Form::select('class_section_id', $classSections, request('class_section_id'), ['class' => 'form-control select2 mr-2 mb-1', 'placeholder' => 'All Classes', 'style' => 'width: 210px']) !!}
                    <button type="submit" class="btn btn-danger shadow-sm mb-1">
                        <i class="fas fa-search mr-1"></i> Apply
                    </button>
                </form>
            </div>
        </div>

        @if(!$filtered)
            {{-- ── Grouped overview: one row per exam × class stream ── --}}
            <div class="card card-outline card-danger elevation-2 border-0">
                <div class="card-header bg-white">
                    <h3 class="card-title font-weight-bold">
                        Recorded Marks by Exam &amp; Class
                        <small class="text-muted font-weight-normal ml-2">select a batch to view or manage individual entries</small>
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                            <tr>
                                <th>Exam Session</th>
                                <th>Class Stream</th>
                                <th class="text-center">Entries</th>
                                <th class="text-center">Approved</th>
                                <th class="text-center">Pending</th>
                                <th class="text-center">Class Average</th>
                                <th>Last Update</th>
                                <th class="text-right pr-3" style="width: 170px;"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($groups as $group)
                                @php
                                    $avgPct = $group->records_count > 0 ? ($group->avg_marks ?? 0) : 0;
                                    $avgColor = $avgPct >= 75 ? 'text-success' : ($avgPct >= 41 ? 'text-primary' : 'text-danger');
                                @endphp
                                <tr>
                                    <td class="font-weight-bold text-dark">{{ $group->exam_name }}</td>
                                    <td>{{ $group->class_name }}</td>
                                    <td class="text-center font-weight-bold">{{ number_format($group->records_count) }}</td>
                                    <td class="text-center"><span class="badge badge-success px-2">{{ number_format($group->approved_count) }}</span></td>
                                    <td class="text-center">
                                        @if($group->pending_count > 0)
                                            <span class="badge badge-warning px-2">{{ number_format($group->pending_count) }}</span>
                                        @else
                                            <span class="badge badge-light border px-2">0</span>
                                        @endif
                                    </td>
                                    <td class="text-center font-weight-bold {{ $avgColor }}">{{ number_format($avgPct, 1) }}</td>
                                    <td class="small text-muted">{{ \Carbon\Carbon::parse($group->latest_entry)->diffForHumans() }}</td>
                                    <td class="text-right pr-3">
                                        <a href="{{ route('exam-results.index', array_merge(request()->only(['exam_id', 'class_section_id']))) }}"
                                           class="btn btn-outline-primary btn-sm shadow-sm">
                                            <i class="fas fa-list mr-1"></i> View Entries
                                        </a>
                                        <a href="{{ route('exam-results.bulk', ['exam_id' => $group->exam_id, 'class_section_id' => $group->class_section_id]) }}"
                                           class="btn btn-outline-secondary btn-sm" title="Enter marks for this batch">
                                            <i class="fas fa-keyboard"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                        No marks recorded yet. Use “Enter / Edit Marks” to record results.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            {{-- ── Detailed entries for the selected exam × class ── --}}
            <div class="card card-outline card-danger elevation-2 border-0">
                <div class="card-header bg-white d-flex align-items-center flex-wrap">
                    <h3 class="card-title font-weight-bold mr-2">
                        {{ $exams[request('exam_id')] ?? '' }} — {{ $classSections[request('class_section_id')] ?? '' }}
                    </h3>
                    <a href="{{ route('exam-results.index') }}" class="btn btn-outline-secondary btn-sm ml-auto">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Overview
                    </a>
                </div>
                <div class="card-body p-0 pb-2">
                    <form action="{{ route('exam-results.index') }}" method="GET" class="px-3 py-2 d-flex flex-wrap align-items-center border-bottom">
                        <input type="hidden" name="exam_id" value="{{ request('exam_id') }}">
                        <input type="hidden" name="class_section_id" value="{{ request('class_section_id') }}">
                        <select name="subject_id" class="form-control form-control-sm mr-2" style="width: 200px;">
                            <option value="">All Subjects</option>
                            @foreach($subjectsForFilter as $sid => $sname)
                                <option value="{{ $sid }}" @selected(request('subject_id') == $sid)>{{ $sname }}</option>
                            @endforeach
                        </select>
                        <select name="approval" class="form-control form-control-sm mr-2" style="width: 160px;">
                            <option value="">Any Status</option>
                            <option value="pending" @selected(request('approval') === 'pending')>Pending Only</option>
                            <option value="approved" @selected(request('approval') === 'approved')>Approved Only</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-filter mr-1"></i> Refine</button>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="exam-results-table">
                            <thead>
                            <tr>
                                <th>Learner</th>
                                <th>Subject</th>
                                <th class="text-center">Marks</th>
                                <th class="text-center">%</th>
                                <th class="text-center">Grade</th>
                                <th class="text-center">Status</th>
                                <th>Remarks</th>
                                <th class="text-center">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($examResults as $examResult)
                                @php
                                    $gradeName = $examResult->grade?->name;
                                    $gradeBadge = 'badge-secondary';
                                    if (in_array($gradeName, ['A', 'A+', 'E', 'EE'])) $gradeBadge = 'badge-success';
                                    elseif (in_array($gradeName, ['B', 'B-', 'C+', 'C', 'ME'])) $gradeBadge = 'badge-primary';
                                    elseif (in_array($gradeName, ['D', 'D-', 'AE'])) $gradeBadge = 'badge-warning';
                                    else $gradeBadge = 'badge-danger';
                                @endphp
                                <tr>
                                    <td>
                                        <span class="text-bold">{{ $examResult->student->full_name ?? 'N/A' }}</span><br>
                                        <small class="text-muted">{{ $examResult->student->admission_no ?? '' }}</small>
                                    </td>
                                    <td><span class="text-dark">{{ $examResult->subject?->name ?? 'N/A' }}</span></td>
                                    <td class="text-center">
                                        <span class="h6 text-bold">{{ number_format($examResult->marks_obtained, 1) }}</span>
                                        <small class="text-muted">/ {{ $examResult->max_marks }}</small>
                                    </td>
                                    <td class="text-center font-weight-bold">{{ number_format($examResult->percentage, 1) }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $gradeBadge }} px-3 py-2">{{ $gradeName ?? '—' }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($examResult->is_approved)
                                            <span class="badge badge-success px-2 py-1"><i class="fas fa-check mr-1"></i>Approved</span>
                                        @else
                                            <span class="badge badge-warning px-2 py-1"><i class="fas fa-hourglass-half mr-1"></i>Pending</span>
                                        @endif
                                    </td>
                                    <td><small class="text-muted">{{ $examResult->remarks ?? '—' }}</small></td>
                                    <td style="width: 120px" class="text-center">
                                        {!! Form::open(['route' => ['exam-results.destroy', $examResult->result_id], 'method' => 'delete']) !!}
                                        <div class='btn-group'>
                                            <a href="{{ route('exam-results.edit', [$examResult->result_id]) }}"
                                               class='btn btn-light btn-sm' title="Edit">
                                                <i class="far fa-edit text-primary"></i>
                                            </a>
                                            {!! Form::button('<i class="far fa-trash-alt text-danger"></i>', ['type' => 'submit', 'class' => 'btn btn-light btn-sm', 'title' => 'Delete', 'onclick' => "return confirm('Are you sure?')"]) !!}
                                        </div>
                                        {!! Form::close() !!}
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer clearfix bg-white">
                    <div class="float-right">
                        {{ $examResults->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>

@endsection
