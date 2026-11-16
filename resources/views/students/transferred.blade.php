@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark font-weight-bold">
                    <i class="fas fa-exchange-alt text-warning mr-2"></i>Transferred Students
                </h1>
                <p class="text-muted small mb-0">Historical record of learners who have left through the transfer workflow.</p>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('student-transfer.index') }}" class="btn btn-primary shadow-sm">
                    <i class="fas fa-user-minus mr-1"></i> Transfer a Student
                </a>
                <a href="{{ route('students.index') }}" class="btn btn-default shadow-sm border ml-2">
                    <i class="fas fa-arrow-left mr-1"></i> Active Students
                </a>
            </div>
        </div>
    </div>
</div>

<div class="content px-3">
    @include('flash::message')

    {{-- Filters --}}
    <div class="card card-outline card-warning elevation-2 mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('students.transferred') }}">
                <div class="form-row align-items-end">
                    <div class="form-group col-md-4 mb-md-0">
                        <label for="q" class="small font-weight-bold text-muted mb-1">Search</label>
                        <input type="text" id="q" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
                               placeholder="Name or admission number...">
                    </div>
                    <div class="form-group col-md-3 mb-md-0">
                        <label for="class_id" class="small font-weight-bold text-muted mb-1">Last class</label>
                        <select id="class_id" name="class_id" class="form-control form-control-sm">
                            <option value="">All classes</option>
                            @foreach($classes as $id => $name)
                                <option value="{{ $id }}" {{ request('class_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-2 mb-md-0">
                        <label for="from" class="small font-weight-bold text-muted mb-1">From</label>
                        <input type="date" id="from" name="from" value="{{ request('from') }}" class="form-control form-control-sm">
                    </div>
                    <div class="form-group col-md-2 mb-md-0">
                        <label for="to" class="small font-weight-bold text-muted mb-1">To</label>
                        <input type="date" id="to" name="to" value="{{ request('to') }}" class="form-control form-control-sm">
                    </div>
                    <div class="form-group col-md-1 mb-md-0 text-right">
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter"></i></button>
                        <a href="{{ route('students.transferred') }}" class="btn btn-sm btn-outline-secondary" title="Clear filters"><i class="fas fa-times"></i></a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-outline card-warning elevation-2">
        <div class="card-header bg-white">
            <h3 class="card-title font-weight-bold"><i class="fas fa-list mr-2"></i>{{ $transferred->total() }} transferred learner(s)</h3>
        </div>
        <div class="card-body p-0">
            @if($transferred->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                    No transferred students match these filters.
                    <div class="mt-2">
                        <a href="{{ route('student-transfer.index') }}" class="btn btn-sm btn-outline-primary">Transfer a student</a>
                    </div>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr class="bg-light text-muted small text-uppercase">
                                <th class="pl-4">Admission No</th>
                                <th>Student</th>
                                <th>Last Class</th>
                                <th>Transfer Date</th>
                                <th>Reason</th>
                                <th>Certificate No</th>
                                <th class="text-center pr-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transferred as $student)
                                @php
                                    $lastEnrollment = $student->studentClassEnrollments
                                        ->sortByDesc('enrollment_date')
                                        ->first();
                                    $lastClass = $lastEnrollment?->classSection?->schoolClass?->name;
                                    $lastSection = $lastEnrollment?->classSection?->section?->name;
                                @endphp
                                <tr>
                                    <td class="pl-4 font-weight-bold">{{ $student->admission_no ?? ('ID ' . $student->student_id) }}</td>
                                    <td>{{ $student->full_name }}</td>
                                    <td>{{ trim(($lastClass ?? 'Unknown') . ($lastSection ? ' - ' . $lastSection : '')) }}</td>
                                    <td>{{ $student->transfer_date ? \Illuminate\Support\Carbon::parse($student->transfer_date)->format('d M Y') : '—' }}</td>
                                    <td class="text-wrap" style="max-width: 260px;">{{ \Illuminate\Support\Str::limit($student->transfer_reason ?? '—', 80) }}</td>
                                    <td>{{ $student->transfer_certificate_no ?? '—' }}</td>
                                    <td class="text-center pr-4">
                                        <a href="{{ route('students.show', $student->student_id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye mr-1"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @if($transferred->hasPages())
            <div class="card-footer bg-white">
                {{ $transferred->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
