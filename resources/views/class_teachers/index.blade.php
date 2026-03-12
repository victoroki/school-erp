@extends('layouts.app')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">
                        <i class="fas fa-user-tie text-info mr-2"></i>Class Teachers (Form Tutors)
                    </h1>
                </div>
            </div>
        </div>
    </div>

    <div class="content px-3">
        @include('flash::message')

        <div class="card card-outline card-info elevation-2 mb-4">
            <div class="card-body">
                {!! Form::open(['route' => 'class-teachers.index', 'method' => 'GET', 'class' => 'form-inline']) !!}
                    <div class="form-group mr-3">
                        <label for="academic_year_id" class="mr-2">Academic Year:</label>
                        {!! Form::select('academic_year_id', $academicYears->pluck('name', 'academic_year_id'), $selectedAcademicYearId, ['class' => 'form-control', 'onchange' => 'this.form.submit()']) !!}
                    </div>
                {!! Form::close() !!}
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card card-outline card-info elevation-2">
                    <div class="card-header border-0">
                        <h3 class="card-title font-weight-bold">Assigned Class Teachers</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover table-valign-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="px-4">Class & Section</th>
                                    <th>Current Class Teacher</th>
                                    <th class="text-right px-4">Change Assignment</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($classSections as $cs)
                                    <tr>
                                        <td class="px-4">
                                            <div class="font-weight-bold text-dark h6 mb-0">{{ $cs->class->name ?? '' }} - {{ $cs->section->name ?? '' }}</div>
                                            <small class="text-muted">Academic Unit: {{ $cs->class_section_id }}</small>
                                        </td>
                                        <td>
                                            @if($cs->classTeacher)
                                                <div class="d-flex align-items-center">
                                                    <img src="{{ asset('garikon-black.png') }}" class="img-circle img-sm mr-2 border shadow-sm" style="width: 32px; height: 32px;">
                                                    <div>
                                                        <div class="font-weight-bold text-info">{{ $cs->classTeacher->full_name }}</div>
                                                        <div class="x-small text-muted">{{ $cs->classTeacher->employee_id }}</div>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="badge badge-warning"><i class="fas fa-exclamation-triangle mr-1"></i> Not Assigned</span>
                                            @endif
                                        </td>
                                        <td class="text-right px-4">
                                            {!! Form::open(['route' => ['class-teachers.update', $cs->class_section_id], 'method' => 'PATCH', 'class' => 'd-flex justify-content-end align-items-center']) !!}
                                                <div class="input-group" style="width: 250px;">
                                                    <select name="teacher_id" class="custom-select" onchange="this.form.submit()">
                                                        <option value="">-- Select Teacher --</option>
                                                        @foreach($teachers as $teacher)
                                                            <option value="{{ $teacher->staff_id }}" {{ $cs->class_teacher_id == $teacher->staff_id ? 'selected' : '' }}>
                                                                {{ $teacher->first_name }} {{ $teacher->last_name }} 
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <div class="input-group-append">
                                                        <span class="input-group-text bg-white"><i class="fas fa-chalkboard-teacher text-muted"></i></span>
                                                    </div>
                                                </div>
                                            {!! Form::close() !!}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-5 text-muted">No class sections found for the selected year.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer bg-white">
                        <small class="text-muted"><i class="fas fa-info-circle mr-1"></i> Class teachers are responsible for attendance, pastoral care, and overall academic monitoring of their assigned sections.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
