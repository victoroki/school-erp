@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">
                    <i class="fas fa-user-slash text-warning mr-2"></i>Unassigned Students
                </h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('students.create') }}" class="btn btn-warning shadow-sm">
                    <i class="fas fa-plus mr-1"></i> New Student
                </a>
            </div>
        </div>
    </div>
</div>

<div class="content px-3">
    @include('flash::message')
    @include('adminlte-templates::common.errors')

    @if($students->isEmpty())
        <div class="alert alert-success elevation-1">
            <i class="fas fa-check-circle mr-2"></i> All active students have a current class enrollment. Nothing to assign.
        </div>
    @else
        <div class="alert alert-info elevation-1">
            <i class="fas fa-info-circle mr-2"></i>
            {{ $students->count() }} active student{{ $students->count() === 1 ? '' : 's' }} {{ $students->count() === 1 ? 'has' : 'have' }} no current class. Pick a class for each student (or use "Assign All To") and click <strong>Save All Assignments</strong>. Assigned students are enrolled immediately and drop off this list.
        </div>

        <form action="{{ route('student-unassigned.store') }}" method="POST">
            @csrf

            <div class="card card-outline card-warning elevation-2">
                <div class="card-header border-0 bg-white d-flex justify-content-between align-items-center flex-wrap">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-clipboard-list mr-2"></i> Assign Classes
                    </h3>
                    <div class="d-flex align-items-center flex-wrap">
                        <div class="form-inline mr-3">
                            <label class="small text-muted font-weight-bold mr-2" for="assign-all-class">Assign All To</label>
                            <select id="assign-all-class" class="form-control form-control-sm select2" style="min-width: 240px;">
                                <option value="">— Choose class for all rows —</option>
                                @foreach($classSections as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="button" class="btn btn-xs btn-link text-warning mr-3" id="selectAll">
                            <i class="fas fa-check-square mr-1"></i> Select All
                        </button>
                        <button type="submit" class="btn btn-warning shadow-sm px-4 font-weight-bold" onclick="return confirm('Assign the selected students to their chosen classes?')">
                            <i class="fas fa-save mr-1"></i> Save All Assignments
                        </button>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 40px;" class="text-center">
                                        <input type="checkbox" id="checkAll" title="Select all students">
                                    </th>
                                    <th style="width: 120px;">Admission No</th>
                                    <th>Student</th>
                                    <th style="min-width: 260px;">Assign Class</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($students as $student)
                                    <tr>
                                        <td class="text-center align-middle">
                                            <input type="checkbox" name="student_ids[]" value="{{ $student->student_id }}"
                                                   class="student-checkbox" data-student-id="{{ $student->student_id }}">
                                        </td>
                                        <td class="align-middle">
                                            <span class="badge badge-light border font-weight-bold px-2 py-1">{{ $student->admission_no }}</span>
                                        </td>
                                        <td class="align-middle">
                                            <div class="d-flex align-items-center">
                                                @include('students._avatar', ['student' => $student, 'size' => 30])
                                                <div class="ml-2">
                                                    <div class="font-weight-bold">{{ $student->full_name }}</div>
                                                    <div class="x-small text-muted">{{ $student->nemis_number ? 'NEMIS: '.$student->nemis_number : '' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <select name="assignments[{{ $student->student_id }}]"
                                                    class="form-control form-control-sm select2 class-picker"
                                                    data-student-id="{{ $student->student_id }}">
                                                <option value="">— Select Class —</option>
                                                @foreach($classSections as $id => $name)
                                                    <option value="{{ $id }}">{{ $name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-white text-right">
                    <a href="{{ route('students.index') }}" class="btn btn-light border mr-2">Cancel</a>
                    <button type="submit" class="btn btn-warning px-4 font-weight-bold" onclick="return confirm('Assign the selected students to their chosen classes?')">
                        <i class="fas fa-save mr-1"></i> Save All Assignments
                    </button>
                </div>
            </div>
        </form>
    @endif
</div>

<style>
    .x-small { font-size: 0.75rem; }
    .table thead th { border-top: 0; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .table td { vertical-align: middle !important; border-top: 1px solid #f8f9fa; }
</style>

@push('page_css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container .select2-selection--single {
            height: 34px !important;
            border: 1px solid #ced4da !important;
            border-radius: 4px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 32px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 32px !important;
        }
    </style>
@endpush

@push('page_scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(function() {
            $('.select2').select2({
                placeholder: "Select...",
                allowClear: true,
                width: '100%'
            });

            $('#checkAll, #selectAll').click(function() {
                $('.student-checkbox').prop('checked', true);
                $('#checkAll').prop('checked', true);
            });

            $('#checkAll').change(function() {
                $('.student-checkbox').prop('checked', $(this).is(':checked'));
            });

            // "Assign All To": apply one class to every row.
            $('#assign-all-class').on('change', function() {
                var value = $(this).val();
                $('.class-picker').each(function() {
                    $(this).val(value).trigger('change');
                    // Auto-check the row so the class actually applies.
                    var studentId = $(this).data('student-id');
                    $('.student-checkbox[data-student-id="' + studentId + '"]').prop('checked', value !== '');
                });
            });

            // When a per-row class is picked without a checkbox, check the row.
            $('.class-picker').on('change', function() {
                var studentId = $(this).data('student-id');
                $('.student-checkbox[data-student-id="' + studentId + '"]').prop('checked', $(this).val() !== '');
            });
        });
    </script>
@endpush
@endsection
