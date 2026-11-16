@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">
                    <i class="fas fa-calendar-check text-warning mr-2"></i>Mark Attendance
                </h1>
                <p class="text-muted mb-0 small">
                    Class: <span class="font-weight-bold text-dark">{{ $classSections->find($classSectionId)->schoolClass->name }} - {{ $classSections->find($classSectionId)->section->name }}</span> | 
                    Date: <span class="font-weight-bold text-dark">{{ \Carbon\Carbon::parse($date)->format('D, M d, Y') }}</span>
                </p>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('student-attendance.index') }}" class="btn btn-default shadow-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Change Selection
                </a>
            </div>
        </div>
    </div>
</div>

<div class="content px-3">
    @include('flash::message')

    <div class="card card-outline card-warning elevation-2">
        <form action="{{ route('student-attendance.store') }}" method="POST">
            @csrf
            <input type="hidden" name="class_section_id" value="{{ $classSectionId }}">
            <input type="hidden" name="date" value="{{ $date }}">

            <div class="card-body p-0 table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 50px;" class="text-center">#</th>
                            <th style="width: 120px;">Admission No.</th>
                            <th>Student Name</th>
                            <th class="text-center">Status</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $index => $student)
                            @php 
                                $att = $attendanceData[$student->student_id] ?? null;
                                $status = $att ? $att->status : 'present';
                            @endphp
                            <tr>
                                <td class="text-center align-middle">{{ $index + 1 }}</td>
                                <td class="align-middle font-weight-bold">{{ $student->admission_no }}</td>
                                <td class="align-middle">
                                    <div class="d-flex align-items-center">
                                        @include('students._avatar', ['student' => $student, 'size' => 32])
                                        <span>{{ $student->full_name }}</span>
                                    </div>
                                </td>
                                <td class="align-middle text-center">
                                    <div class="btn-group btn-group-toggle shadow-sm" data-toggle="buttons">
                                        <label class="btn btn-xs btn-outline-success {{ $status == 'present' ? 'active' : '' }}" title="Present">
                                            <input type="radio" name="attendance[{{ $student->student_id }}]" value="present" {{ $status == 'present' ? 'checked' : '' }}> P
                                        </label>
                                        <label class="btn btn-xs btn-outline-danger {{ $status == 'absent' ? 'active' : '' }}" title="Absent">
                                            <input type="radio" name="attendance[{{ $student->student_id }}]" value="absent" {{ $status == 'absent' ? 'checked' : '' }}> A
                                        </label>
                                        <label class="btn btn-xs btn-outline-warning {{ $status == 'late' ? 'active' : '' }}" title="Late">
                                            <input type="radio" name="attendance[{{ $student->student_id }}]" value="late" {{ $status == 'late' ? 'checked' : '' }}> L
                                        </label>
                                        <label class="btn btn-xs btn-outline-info {{ $status == 'excused' ? 'active' : '' }}" title="Excused">
                                            <input type="radio" name="attendance[{{ $student->student_id }}]" value="excused" {{ $status == 'excused' ? 'checked' : '' }}> E
                                        </label>
                                    </div>
                                </td>
                                <td class="align-middle">
                                    <input type="text" name="remarks[{{ $student->student_id }}]" class="form-control form-control-sm border-0 bg-light-soft" placeholder="Add note..." value="{{ $att->remarks ?? '' }}">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white text-right">
                <button type="submit" class="btn btn-warning shadow-sm px-4">
                    <i class="fas fa-save mr-1"></i> Save Attendance Record
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .bg-light-soft { background-color: rgba(0,0,0,0.02); border-radius: 4px; }
    .btn-group-toggle .btn { width: 35px; }
    .btn-group-toggle .btn.active { color: #fff !important; }
    .btn-outline-success.active { background-color: #28a745 !important; }
    .btn-outline-danger.active { background-color: #dc3545 !important; }
    .btn-outline-warning.active { background-color: #ffc107 !important; }
    .btn-outline-info.active { background-color: #17a2b8 !important; }
</style>
@endsection
