@extends('layouts.app')

@section('content')
<div class="page pb-0">
    <div class="page-head mb-0">
        <div>
            <h1>Students</h1>
            <p>Search, filter and manage every learner on the roll.</p>
        </div>
        <div class="page-actions">
            <div class="dropdown">
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Bulk actions
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    {{-- Acts on the students ticked in the table below. --}}
                    <a class="dropdown-item" href="#" onclick="submitBulkAction('id-cards'); return false;">
                        <i class="fas fa-id-card mr-2" aria-hidden="true"></i> Print ID cards for selected
                    </a>
                    {{-- Promotion runs per class on its own screen; the old menu
                         item only showed an alert and promoted nobody. --}}
                    <a class="dropdown-item" href="{{ route('student-promotion.index') }}">
                        <i class="fas fa-level-up-alt mr-2" aria-hidden="true"></i> Promote a class
                    </a>
                </div>
            </div>
            @can('students.manage')
                <a class="btn btn-primary" href="{{ route('students.create') }}">
                    <i class="fas fa-plus mr-1" aria-hidden="true"></i> New student
                </a>
            @endcan
        </div>
    </div>
</div>

<div class="content px-3">
    @include('flash::message')

    <!-- Advanced Filter Bar -->
    <div class="card card-outline card-warning elevation-2 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('students.index') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <div class="form-group mb-0">
                            <label class="small text-uppercase text-muted font-weight-bold">Search</label>
                            <div class="input-group shadow-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
                                </div>
                                <input type="text" name="q" value="{{ request('q') }}" class="form-control border-left-0" placeholder="Name, Adm No, NEMIS...">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <label class="small text-uppercase text-muted font-weight-bold">Academic Year</label>
                            <select name="academic_year_id" class="form-control select2 shadow-sm">
                                <option value="">All Years</option>
                                @foreach($academicYears as $ay)
                                    <option value="{{ $ay->academic_year_id }}" {{ request('academic_year_id') == $ay->academic_year_id ? 'selected' : '' }}>
                                        {{ $ay->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <label class="small text-uppercase text-muted font-weight-bold">Class Section</label>
                            <select name="class_section_id" class="form-control select2 shadow-sm">
                                <option value="">All Classes</option>
                                @foreach($classSections as $cs)
                                    <option value="{{ $cs->class_section_id }}" {{ request('class_section_id') == $cs->class_section_id ? 'selected' : '' }}>
                                        {{ $cs->schoolClass->name }} - {{ $cs->section->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <label class="small text-uppercase text-muted font-weight-bold">Status</label>
                            <select name="status" class="form-control shadow-sm">
                                <option value="">All</option>
                                <option value="active" {{ request('status')=='active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status')=='inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="alumni" {{ request('status')=='alumni' ? 'selected' : '' }}>Alumni</option>
                                <option value="transferred" {{ request('status')=='transferred' ? 'selected' : '' }}>Transferred</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <label class="small text-uppercase text-muted font-weight-bold">Enrollment</label>
                            <select name="enrollment_status" class="form-control shadow-sm">
                                <option value="">All</option>
                                <option value="enrolled" {{ request('enrollment_status')=='enrolled' ? 'selected' : '' }}>Enrolled</option>
                                <option value="graduated" {{ request('enrollment_status')=='graduated' ? 'selected' : '' }}>Graduated</option>
                                <option value="on_leave" {{ request('enrollment_status')=='on_leave' ? 'selected' : '' }}>On Leave</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="btn-group w-100">
                            <button type="submit" class="btn btn-primary" title="Apply filter">
                                <i class="fas fa-filter"></i>
                            </button>
                            <a href="{{ route('students.index') }}" class="btn btn-light border shadow-sm" title="Clear All">
                                <i class="fas fa-redo"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Student Table -->
    <div class="card card-outline card-warning elevation-2 overflow-hidden">
        <form id="bulkActionForm" method="POST" target="_blank">
            @csrf
            @include('students.table')
        </form>
    </div>
</div>

@push('page_scripts')
<script>
    function submitBulkAction(action) {
        var selected = $('.student-checkbox:checked').length;
        if (selected === 0) {
            alert('Please select at least one student.');
            return;
        }

        var form = $('#bulkActionForm');
        if (action === 'id-cards') {
            form.attr('action', "{{ route('students.bulk-id-cards') }}");
            form.submit();
        }
    }
</script>
@endpush
@endsection
