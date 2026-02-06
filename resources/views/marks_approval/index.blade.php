@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold text-danger">
                        <i class="fas fa-check-double mr-2"></i> Marks Approval Workflow
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="card elevation-2 border-0 mb-4">
            <div class="card-body">
                <form action="{{ route('marks-approval.index') }}" method="GET">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <label class="small font-weight-bold text-uppercase">Exam Session</label>
                            {!! Form::select('exam_id', $exams, request('exam_id'), ['class' => 'form-control select2', 'placeholder' => 'All Sessions']) !!}
                        </div>
                        <div class="col-md-4">
                            <label class="small font-weight-bold text-uppercase">Class & Section</label>
                            {!! Form::select('class_section_id', $classSections, request('class_section_id'), ['class' => 'form-control select2', 'placeholder' => 'All Classes']) !!}
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary shadow-sm">
                                <i class="fas fa-filter mr-1"></i> Filter Pending
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card card-outline card-danger elevation-2 border-0">
            <form action="{{ route('marks-approval.approve') }}" method="POST" id="approval-form">
                @csrf
                <div class="card-header bg-white">
                    <h3 class="card-title font-weight-bold">Pending Marks Entries</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-outline-primary btn-sm mr-2" id="select-all">Select All</button>
                        <button type="submit" class="btn btn-success px-4 elevation-1" onclick="return confirm('Approve selected marks?')">
                            <i class="fas fa-check mr-1"></i> Approve Selected
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                            <tr>
                                <th style="width: 40px" class="text-center">#</th>
                                <th>Student</th>
                                <th>Exam</th>
                                <th>Subject</th>
                                <th>Marks</th>
                                <th>Entered By</th>
                                <th>Date</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($pendingResults as $res)
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" name="result_ids[]" value="{{ $res->result_id }}" class="result-checkbox">
                                    </td>
                                    <td class="font-weight-bold">{{ $res->student->full_name }}</td>
                                    <td>{{ $res->exam->name }}</td>
                                    <td>{{ $res->subject->name }}</td>
                                    <td><span class="badge badge-info px-2 py-1">{{ $res->marks_obtained }}</span></td>
                                    <td><small class="text-muted">{{ $res->createdBy->staff_id ?? 'System' }}</small></td>
                                    <td class="small">{{ $res->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">No pending marks found for approval.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-top">
                    {{ $pendingResults->links() }}
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('select-all').addEventListener('click', function() {
            const checkboxes = document.querySelectorAll('.result-checkbox');
            const allChecked = Array.from(checkboxes).every(c => c.checked);
            checkboxes.forEach(c => c.checked = !allChecked);
            this.textContent = allChecked ? 'Select All' : 'Deselect All';
        });
    </script>
@endsection
