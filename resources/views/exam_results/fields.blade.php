<div class="row">
    <!-- Exam Field -->
    <div class="form-group col-sm-6">
        {!! Form::label('exam_id', 'Exam Session') !!}
        {!! Form::select('exam_id', $exams, null, ['class' => 'form-control select2', 'required', 'placeholder' => 'Select Session']) !!}
    </div>

    <!-- Class Section Field -->
    <div class="form-group col-sm-6">
        {!! Form::label('class_section_id', 'Class & Section') !!}
        {!! Form::select('class_section_id', $classSections, null, ['class' => 'form-control select2', 'required', 'id' => 'class_section_id', 'placeholder' => 'Select Class']) !!}
    </div>

    <!-- Student Field -->
    <div class="form-group col-sm-6">
        {!! Form::label('student_id', 'Student') !!}
        {!! Form::select('student_id', $students, null, ['class' => 'form-control select2', 'required', 'id' => 'student_id', 'placeholder' => 'First Select Class']) !!}
    </div>

    <!-- Subject Field -->
    <div class="form-group col-sm-6">
        {!! Form::label('subject_id', 'Subject') !!}
        {!! Form::select('subject_id', $subjects, null, ['class' => 'form-control select2', 'required', 'id' => 'subject_id', 'placeholder' => 'First Select Class']) !!}
    </div>

    <div class="col-12"><hr></div>

    <!-- Marks Obtained Field -->
    <div class="form-group col-sm-6">
        {!! Form::label('marks_obtained', 'Score Obtained') !!}
        <div class="input-group">
            {!! Form::number('marks_obtained', null, ['class' => 'form-control', 'required', 'min' => '0', 'step' => '0.01', 'id' => 'marks_obtained']) !!}
            <div class="input-group-append">
                <span class="input-group-text badge-info" id="auto-grade">--</span>
            </div>
        </div>
        <small class="text-muted">Grade will be calculated automatically on save.</small>
    </div>

    <!-- Remarks Field -->
    <div class="form-group col-sm-6">
        {!! Form::label('remarks', 'Teacher Remarks') !!}
        {!! Form::text('remarks', null, ['class' => 'form-control', 'placeholder' => 'e.g. Excellent work']) !!}
    </div>
</div>

{!! Form::hidden('created_by', Auth::id()) !!}

@push('page_scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });

        // Basic AJAX to load students/subjects if needed. 
        // Note: Real implementation would need routes for these.
        $('#class_section_id').on('change', function() {
            // Simplified for demonstration - usually you'd hit an API endpoint here
            // Class section changed
        });
    });
</script>
@endpush