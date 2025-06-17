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

@push('page_scripts')
    <script type="text/javascript">
        $(document).ready(function() {
            // Initialize Select2 for better dropdown experience
            $('.select2').select2({
                placeholder: "Select an option",
                allowClear: true
            });
        });
    </script>
@endpush