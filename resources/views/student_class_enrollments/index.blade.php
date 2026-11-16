@extends('layouts.app')

@section('title', 'Class enrollments')

@section('content')
<div class="page">
    <div class="page-head">
        <div>
            <h1>Class enrollments</h1>
            <p>Which class and year every student is in.</p>
        </div>
        <div class="page-actions">
            @can('students.manage')
                <a class="btn btn-primary" href="{{ route('student-class-enrollments.create') }}">
                    <i class="fas fa-plus mr-1" aria-hidden="true"></i> Enroll a student
                </a>
            @endcan
        </div>
    </div>

    @include('flash::message')

    <div class="list-card">
        <form method="GET" action="{{ route('student-class-enrollments.index') }}" class="filter-bar" role="search">
            <div class="form-group filter-search">
                <label for="f-q">Student</label>
                <input type="search" id="f-q" name="q" value="{{ request('q') }}" class="form-control" placeholder="Name or admission number">
            </div>
            <div class="form-group">
                <label for="f-class">Class</label>
                <select id="f-class" name="class_section_id" class="form-control">
                    <option value="">All classes</option>
                    @foreach($classSections as $id => $name)
                        <option value="{{ $id }}" @selected((string) request('class_section_id') === (string) $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="f-year">Academic year</label>
                <select id="f-year" name="academic_year_id" class="form-control">
                    <option value="">All years</option>
                    @foreach($academicYears as $id => $name)
                        <option value="{{ $id }}" @selected((string) request('academic_year_id') === (string) $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="f-status">Status</label>
                <select id="f-status" name="status" class="form-control">
                    <option value="">Any status</option>
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-buttons">
                <button type="submit" class="btn btn-primary">Filter</button>
                @if(request()->hasAny(['q', 'class_section_id', 'academic_year_id', 'status']))
                    <a href="{{ route('student-class-enrollments.index') }}" class="btn btn-default">Clear</a>
                @endif
            </div>
        </form>

        @include('student_class_enrollments.table')
    </div>
</div>
@endsection
