@push('page_css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.1.2/dist/select2-bootstrap4.min.css">
    <style>
        .select2-container--bootstrap4 .select2-selection {
            border: 1px solid var(--border) !important;
            border-radius: 8px !important;
            height: 42px !important;
            padding: 0.375rem 0.75rem !important;
            font-size: 0.875rem !important;
            transition: all 150ms var(--ease-out) !important;
        }
        .select2-container--bootstrap4.select2-container--focus .select2-selection {
            border-color: var(--indigo) !important;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1) !important;
        }
        .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
            line-height: 28px !important;
            color: var(--text) !important;
        }
    </style>
@endpush

<!-- Staff Selection -->
<div class="form-group col-sm-6 mb-4">
    {!! Form::label('staff_id', 'Assigned Teacher', ['class' => 'dash-label']) !!}
    {!! Form::select('staff_id', $staffList, null, ['class' => 'form-control select2', 'id' => 'staff_id', 'required']) !!}
</div>

<!-- Subject Selection -->
<div class="form-group col-sm-6 mb-4">
    {!! Form::label('subject_id', 'Subject to Teach', ['class' => 'dash-label']) !!}
    {!! Form::select('subject_id', $subjectList, null, ['class' => 'form-control select2', 'id' => 'subject_id', 'required']) !!}
</div>

<!-- Class Section Selection -->
<div class="form-group col-sm-6 mb-4">
    {!! Form::label('class_section_id', 'Target Class & Section', ['class' => 'dash-label']) !!}
    {!! Form::select('class_section_id', $classSectionList, null, ['class' => 'form-control select2', 'id' => 'class_section_id', 'required']) !!}
</div>

<!-- Academic Year Selection -->
<div class="form-group col-sm-6 mb-4">
    {!! Form::label('academic_year_id', 'Academic Period', ['class' => 'dash-label']) !!}
    {!! Form::select('academic_year_id', $academicYearList, $currentYearId ?? null, ['class' => 'form-control select2', 'id' => 'academic_year_id', 'required']) !!}
</div>

@push('page_scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: 'Select an option'
            });
        });
    </script>
@endpush
