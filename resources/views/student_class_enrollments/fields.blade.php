<!-- Student Field -->
<div class="form-group col-sm-6">
    {!! Form::label('student_id', 'Student:') !!}
    {!! Form::select('student_id', ['' => 'Select Student'] + $students, null, ['class' => 'form-control select2', 'required']) !!}
</div>

<!-- Class Section Field -->
<div class="form-group col-sm-6">
    {!! Form::label('class_section_id', 'Class Section:') !!}
    {!! Form::select('class_section_id', ['' => 'Select Class Section'] + $classSections, null, ['class' => 'form-control select2', 'required']) !!}
</div>

<!-- Roll Number Field -->
<div class="form-group col-sm-6">
    {!! Form::label('roll_number', 'Roll Number:') !!}
    {!! Form::text('roll_number', null, ['class' => 'form-control', 'maxlength' => 20, 'placeholder' => 'Enter roll number']) !!}
</div>

<!-- Academic Year Field -->
<div class="form-group col-sm-6">
    {!! Form::label('academic_year_id', 'Academic Year:') !!}
    {!! Form::select('academic_year_id', ['' => 'Select Academic Year'] + $academicYears, null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Enrollment Date Field -->
<div class="form-group col-sm-6">
    {!! Form::label('enrollment_date', 'Enrollment Date:') !!}
    {!! Form::date('enrollment_date', null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('status', 'Status:') !!}
    {!! Form::select('status', ['' => 'Select Status'] + $statusOptions, 'active', ['class' => 'form-control', 'required']) !!}
</div>

@push('page_css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css">
    <style>
        .select2-container .select2-selection--single {
            height: 38px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px !important;
        }
    </style>
@endpush

@push('page_scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            // Initialize Select2 for better dropdown experience
            $('.select2').select2({
                theme: 'bootstrap4',
                placeholder: "Select an option",
                allowClear: true,
                width: '100%'
            });
        });
    </script>
@endpush