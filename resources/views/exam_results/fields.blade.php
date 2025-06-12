<!-- Exam Field -->
<div class="form-group col-sm-6">
    {!! Form::label('exam_id', 'Exam:') !!}
    {!! Form::select('exam_id', ['' => 'Select Exam'] + $exams, null, ['class' => 'form-control select2', 'required']) !!}
</div>

<!-- Class Section Field -->
<div class="form-group col-sm-6">
    {!! Form::label('class_section_id', 'Class Section:') !!}
    {!! Form::select('class_section_id', ['' => 'Select Class Section'] + $classSections, null, ['class' => 'form-control select2', 'required', 'id' => 'class_section_id']) !!}
</div>

<!-- Student Field -->
<div class="form-group col-sm-6">
    {!! Form::label('student_id', 'Student:') !!}
    {!! Form::select('student_id', ['' => 'Select Student'] + $students, null, ['class' => 'form-control select2', 'required', 'id' => 'student_id']) !!}
</div>

<!-- Subject Field -->
<div class="form-group col-sm-6">
    {!! Form::label('subject_id', 'Subject:') !!}
    {!! Form::select('subject_id', ['' => 'Select Subject'] + $subjects, null, ['class' => 'form-control select2', 'required', 'id' => 'subject_id']) !!}
</div>

<!-- Marks Obtained Field -->
<div class="form-group col-sm-6">
    {!! Form::label('marks_obtained', 'Marks Obtained:') !!}
    {!! Form::number('marks_obtained', null, ['class' => 'form-control', 'required', 'min' => '0', 'max' => '100', 'step' => '0.01', 'id' => 'marks_obtained']) !!}
</div>

<!-- Grade Field -->
<div class="form-group col-sm-6">
    {!! Form::label('grade_id', 'Grade:') !!}
    {!! Form::select('grade_id', ['' => 'Auto Calculate or Select Grade'] + $grades, null, ['class' => 'form-control select2', 'id' => 'grade_id']) !!}
    <small class="text-muted">Leave empty to auto-calculate based on marks</small>
</div>

<!-- Remarks Field -->
<div class="form-group col-sm-12">
    {!! Form::label('remarks', 'Remarks:') !!}
    {!! Form::textarea('remarks', null, ['class' => 'form-control', 'rows' => 3, 'maxlength' => 500, 'placeholder' => 'Enter any additional remarks or comments']) !!}
</div>

<!-- Created By Field (Hidden - Auto-filled) -->
{!! Form::hidden('created_by', Auth::id()) !!}

@push('page_scripts')
    <script type="text/javascript">
        $(document).ready(function() {
            // Initialize Select2 for better dropdown experience
            $('.select2').select2({
                placeholder: "Select an option",
                allowClear: true
            });

            // Filter students and subjects based on selected class section
            $('#class_section_id').change(function() {
                var classSectionId = $(this).val();
                
                if (classSectionId) {
                    // Load students for selected class section
                    $.ajax({
                        url: '/exam-results/students-by-class-section/' + classSectionId,
                        type: 'GET',
                        success: function(data) {
                            $('#student_id').empty().append('<option value="">Select Student</option>');
                            $.each(data, function(key, student) {
                                $('#student_id').append('<option value="' + student.id + '">' + 
                                    student.first_name + ' ' + student.last_name + ' (' + student.student_id + ')' + '</option>');
                            });
                            $('#student_id').trigger('change');
                        }
                    });

                    // Load subjects for selected class section
                    $.ajax({
                        url: '/exam-results/subjects-by-class-section/' + classSectionId,
                        type: 'GET',
                        success: function(data) {
                            $('#subject_id').empty().append('<option value="">Select Subject</option>');
                            $.each(data, function(key, subject) {
                                $('#subject_id').append('<option value="' + subject.id + '">' + subject.subject_name + '</option>');
                            });
                            $('#subject_id').trigger('change');
                        }
                    });
                } else {
                    $('#student_id').empty().append('<option value="">Select Student</option>');
                    $('#subject_id').empty().append('<option value="">Select Subject</option>');
                }
            });

            // Auto-calculate grade based on marks
            $('#marks_obtained').on('input', function() {
                var marks = parseFloat($(this).val());
                
                if (marks >= 0) {
                    // You can implement auto-grade calculation here
                    // This is a simple example - adjust based on your grading system
                    var gradeOptions = $('#grade_id option');
                    gradeOptions.each(function() {
                        var option = $(this);
                        var text = option.text();
                        
                        // Extract min and max marks from grade text like "A (90-100)"
                        var match = text.match(/\((\d+)-(\d+)\)/);
                        if (match) {
                            var minMarks = parseInt(match[1]);
                            var maxMarks = parseInt(match[2]);
                            
                            if (marks >= minMarks && marks <= maxMarks) {
                                $('#grade_id').val(option.val()).trigger('change');
                                return false; // Break the loop
                            }
                        }
                    });
                }
            });

            // Validation for marks
            $('#marks_obtained').on('input', function() {
                var marks = parseFloat($(this).val());
                var max = parseFloat($(this).attr('max'));
                
                if (marks > max) {
                    $(this).val(max);
                    alert('Marks cannot exceed ' + max);
                }
            });
        });
    </script>
@endpush