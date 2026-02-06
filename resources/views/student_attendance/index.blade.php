@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">
                    <i class="fas fa-calendar-check text-warning mr-2"></i>Student Attendance
                </h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('student-attendance.report') }}" class="btn btn-outline-info shadow-sm">
                    <i class="fas fa-file-invoice mr-1"></i> Attendance Report
                </a>
            </div>
        </div>
    </div>
</div>

<div class="content px-3">
    @include('flash::message')
    
    <div class="card card-outline card-warning elevation-2">
        <div class="card-header border-0 bg-white">
            <h3 class="card-title font-weight-bold">Select Class Section to Mark Attendance</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('student-attendance.index') }}" method="GET">
                <div class="row align-items-end">
                    <div class="col-md-5">
                        <div class="form-group mb-0">
                            <label class="small text-uppercase text-muted font-weight-bold">Academic Class & Section</label>
                            <select name="class_section_id" class="form-control select2 shadow-sm" required>
                                <option value="">Select Class Section</option>
                                @foreach($classSections as $cs)
                                    <option value="{{ $cs->class_section_id }}">
                                        {{ $cs->schoolClass->name }} - {{ $cs->section->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-0">
                            <label class="small text-uppercase text-muted font-weight-bold">Attendance Date</label>
                            <input type="date" name="date" class="form-control shadow-sm" value="{{ $date }}" max="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-warning btn-block shadow-sm">
                            <i class="fas fa-search mr-1"></i> Load Students
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Instructions/Summary Card -->
    <div class="row">
        <div class="col-md-12">
            <div class="callout callout-warning shadow-sm bg-white">
                <h5><i class="fas fa-info-circle text-warning mr-2"></i>Attendance Marking Tips</h5>
                <ul class="mb-0 text-muted small">
                    <li>By default, students are considered <strong>Present</strong>.</li>
                    <li>Update the status only for those who are <strong>Absent, Late, or Excused</strong>.</li>
                    <li>Attendance can only be marked for the current day or past dates.</li>
                    <li>You can revisit and update attendance for any date if needed.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
