@push('page_css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.1.2/dist/select2-bootstrap4.min.css">
    <style>
        .select2-container .select2-selection--single { height: 38px !important; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px !important; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 36px !important; }
    </style>
@endpush

<!-- Staff Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('staff_id', 'Staff:') !!}
    {!! Form::select('staff_id', $staffList, null, ['class' => 'form-control select2', 'id' => 'staff_id', 'required']) !!}
</div>

<!-- Subject Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('subject_id', 'Subject:') !!}
    {!! Form::select('subject_id', $subjectList, null, ['class' => 'form-control select2', 'id' => 'subject_id', 'required']) !!}
</div>

<!-- Class Section Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('class_section_id', 'Class Section:') !!}
    {!! Form::select('class_section_id', $classSectionList, null, ['class' => 'form-control select2', 'id' => 'class_section_id', 'required']) !!}
</div>

<!-- Academic Year Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('academic_year_id', 'Academic Year:') !!}
    {!! Form::select('academic_year_id', $academicYearList, $currentYearId ?? null, ['class' => 'form-control select2', 'id' => 'academic_year_id', 'required']) !!}
</div>

@push('page_scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%'
            });
        });
    </script>
@endpush
