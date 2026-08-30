@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">
                    <i class="fas fa-level-up-alt text-warning mr-2"></i>Student Promotion
                </h1>
            </div>
        </div>
    </div>
</div>

<div class="content px-3">
    @include('flash::message')

    <div class="card card-outline card-warning elevation-2 mb-4">
        <div class="card-header border-0 bg-white">
            <h3 class="card-title font-weight-bold">Promotion Filter</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('student-promotion.index') }}" method="GET">
                <div class="row align-items-end">
                    <div class="col-md-8">
                        <div class="form-group mb-0">
                            <label class="small text-uppercase text-muted font-weight-bold">Promote From (Class-Section)</label>
                            <select name="from_class_section_id" class="form-control select2" required>
                                <option value="">Select From Class Section</option>
                                @foreach($classSections as $cs)
                                    <option value="{{ $cs->class_section_id }}" {{ $fromClassSectionId == $cs->class_section_id ? 'selected' : '' }}>
                                        {{ $cs->schoolClass->name }} - {{ $cs->section->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-warning btn-block">
                            <i class="fas fa-users mr-1"></i> List Students
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($fromClassSectionId && count($students) > 0)
        <form action="{{ route('student-promotion.store') }}" method="POST">
            @csrf
            <input type="hidden" name="from_class_section_id" value="{{ $fromClassSectionId }}">
            
            <div class="row">
                <div class="col-md-4">
                    <div class="card card-outline card-info elevation-2">
                        <div class="card-header border-0 bg-white">
                            <h3 class="card-title font-weight-bold">Promotion Settings</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="small text-uppercase text-muted font-weight-bold">Promote To (Class-Section)</label>
                                <select name="to_class_section_id" class="form-control select2" required>
                                    <option value="">Select Target Class Section</option>
                                    @foreach($classSections as $cs)
                                        <option value="{{ $cs->class_section_id }}">
                                            {{ $cs->schoolClass->name }} - {{ $cs->section->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="small text-uppercase text-muted font-weight-bold">For Academic Year</label>
                                <select name="academic_year_id" class="form-control select2" required>
                                    <option value="">Select Academic Year</option>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->academic_year_id }}">
                                            {{ $year->name }} ({{ $year->start_date->format('Y') }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <hr>
                            
                            <button type="submit" class="btn btn-success btn-block shadow-sm py-2 font-weight-bold" onclick="return confirm('Are you sure you want to promote the selected students?')">
                                <i class="fas fa-rocket mr-1"></i> Confirm Promotion
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card card-outline card-warning elevation-2">
                        <div class="card-header border-0 bg-white d-flex justify-content-between align-items-center">
                            <h3 class="card-title font-weight-bold">Select Students</h3>
                            <button type="button" class="btn btn-xs btn-link text-warning" id="selectAll">Select All</button>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 40px;" class="text-center">#</th>
                                        <th style="width: 40px;" class="text-center">
                                            <input type="checkbox" id="checkAll">
                                        </th>
                                        <th>Admission No.</th>
                                        <th>Student Name</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($students as $index => $student)
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td class="text-center">
                                                <input type="checkbox" name="student_ids[]" value="{{ $student->student_id }}" class="student-checkbox">
                                            </td>
                                            <td class="font-weight-bold">{{ $student->admission_no }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @include('students._avatar', ['student' => $student, 'size' => 25])
                                                    <span class="ml-2">{{ $student->full_name }}</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    @elseif($fromClassSectionId)
        <div class="alert alert-info elevation-1">
            <i class="fas fa-info-circle mr-2"></i> No active students found in the selected class section.
        </div>
    @endif
</div>

@push('page_scripts')
<script>
    $(function() {
        $('#checkAll').change(function() {
            $('.student-checkbox').prop('checked', $(this).is(':checked'));
        });

        $('#selectAll').click(function(e) {
            e.preventDefault();
            var allChecked = $('.student-checkbox').length === $('.student-checkbox:checked').length;
            $('.student-checkbox').prop('checked', !allChecked);
            $('#checkAll').prop('checked', !allChecked);
        });
    });
</script>
@endpush
@endsection
