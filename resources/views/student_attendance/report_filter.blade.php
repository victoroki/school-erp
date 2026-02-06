@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">
                    <i class="fas fa-file-invoice text-warning mr-2"></i>Attendance Report Filter
                </h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('student-attendance.index') }}" class="btn btn-default shadow-sm border-warning text-warning">
                    <i class="fas fa-calendar-plus mr-1"></i> Mark Attendance
                </a>
            </div>
        </div>
    </div>
</div>

<div class="content px-3">
    <div class="card card-outline card-warning elevation-2">
        <div class="card-header border-0 bg-white">
            <h3 class="card-title font-weight-bold">Select Report Criteria</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('student-attendance.report') }}" method="GET">
                <div class="row align-items-end">
                    <div class="col-md-4">
                        <div class="form-group mb-0">
                            <label class="small text-uppercase text-muted font-weight-bold">Class Section</label>
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
                    <div class="col-md-3">
                        <div class="form-group mb-0">
                            <label class="small text-uppercase text-muted font-weight-bold">Month</label>
                            <select name="month" class="form-control shadow-sm">
                                @foreach(range(1, 12) as $m)
                                    <option value="{{ sprintf('%02d', $m) }}" {{ $m == date('m') ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $m, 10)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-0">
                            <label class="small text-uppercase text-muted font-weight-bold">Year</label>
                            <select name="year" class="form-control shadow-sm">
                                @foreach(range(date('Y')-2, date('Y')+1) as $y)
                                    <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-warning btn-block shadow-sm">
                            <i class="fas fa-file-alt mr-1"></i> Generate
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
