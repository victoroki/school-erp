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
    {!! Form::date('enrollment_date', (isset($studentClassEnrollment) && $studentClassEnrollment->enrollment_date) ? $studentClassEnrollment->enrollment_date->format('Y-m-d') : null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('status', 'Status:') !!}
    {!! Form::select('status', ['' => 'Select Status'] + $statusOptions, (isset($studentClassEnrollment) && $studentClassEnrollment->status) ? $studentClassEnrollment->status : 'active', ['class' => 'form-control', 'required']) !!}
</div>

@push('page_css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container .select2-selection--single {
            height: 38px !important;
            border: 1px solid #ced4da !important;
            border-radius: 4px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px !important;
            color: #495057 !important;
        }
        .select2-search__field {
            display: block !important;
        }
    </style>
@endpush

@push('page_scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function() {
            var select2InitInterval = setInterval(function() {
                if (window.jQuery && window.jQuery.fn.select2) {
                    clearInterval(select2InitInterval);
                    window.jQuery(function($) {
                        $('.select2').select2({
                            placeholder: "Search and select...",
                            allowClear: true,
                            width: '100%'
                        });
                        
                        // Force search box to show focus if needed
                        $(document).on('select2:open', () => {
                            document.querySelector('.select2-search__field').focus();
                        });
                    });
                }
            }, 100);
            
            // Fallback timeout to clear interval
            setTimeout(function() { clearInterval(select2InitInterval); }, 5000);
        });
    </script>
@endpush