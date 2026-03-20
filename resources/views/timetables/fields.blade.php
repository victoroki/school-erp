<!-- Class Section Field -->
<div class="form-group col-sm-6">
    {!! Form::label('class_section_id', 'Class Section:') !!}
    {!! Form::select('class_section_id',
        ['' => 'Select Class Section'] + $classSections,
        null,
        ['class' => 'form-control', 'required']) !!}
</div>

<!-- Day Of Week Field -->
<div class="form-group col-sm-6">
    {!! Form::label('day_of_week', 'Day Of Week:') !!}
    {!! Form::select('day_of_week',
        ['' => 'Select Day'] + $daysOfWeek,
        null,
        ['class' => 'form-control', 'required']) !!}
</div>

<!-- Period Field -->
<div class="form-group col-sm-6">
    {!! Form::label('period_id', 'Period:') !!}
    {!! Form::select('period_id',
        ['' => 'Select Period'] + $periods,
        null,
        ['class' => 'form-control', 'required']) !!}
</div>

<!-- Subject Field -->
<div class="form-group col-sm-6">
    {!! Form::label('subject_id', 'Subject:') !!}
    {!! Form::select('subject_id',
        ['' => 'Select Subject'] + $subjects,
        null,
        ['class' => 'form-control', 'required']) !!}
</div>

<!-- Teacher Field -->
<div class="form-group col-sm-6">
    {!! Form::label('teacher_id', 'Teacher:') !!}
    {!! Form::select('teacher_id',
        ['' => 'Select Teacher'] + $teachers,
        null,
        ['class' => 'form-control', 'required']) !!}
</div>

<!-- Classroom Field -->
<div class="form-group col-sm-6">
    {!! Form::label('classroom_id', 'Classroom:') !!}
    {!! Form::select('classroom_id',
        ['' => 'Select Classroom'] + $classrooms,
        null,
        ['class' => 'form-control', 'required']) !!}
</div>

<!-- Academic Year Field -->
<div class="form-group col-sm-6">
    {!! Form::label('academic_year_id', 'Academic Year:') !!}
    {!! Form::select('academic_year_id',
        ['' => 'Select Academic Year'] + $academicYears,
        null,
        ['class' => 'form-control', 'required']) !!}
</div>

@push('page_scripts')
    <script>
        $(document).ready(function() {
            function filterTeachers() {
                var subjectId = $('#subject_id').val();
                var classSectionId = $('#class_section_id').val();
                var teacherSelect = $('#teacher_id');
                var currentTeacherId = teacherSelect.val();

                if (subjectId) {
                    // Show a loading state if possible
                    teacherSelect.prop('disabled', true);
                    
                    $.ajax({
                        url: "{{ url('api/subjects') }}/" + subjectId + "/teachers",
                        type: 'GET',
                        data: { class_section_id: classSectionId },
                        dataType: 'json',
                        success: function(data) {
                            teacherSelect.empty();
                            teacherSelect.append('<option value="">Select Teacher</option>');
                            
                            var teacherFound = false;
                            $.each(data, function(key, value) {
                                var selected = (key == currentTeacherId) ? 'selected' : '';
                                if(key == currentTeacherId) teacherFound = true;
                                teacherSelect.append('<option value="' + key + '" ' + selected + '>' + value + '</option>');
                            });
                            
                            // If the previously selected teacher is NOT in the new list, reset selection
                            if(!teacherFound && currentTeacherId != "") {
                                teacherSelect.val("");
                            }

                            teacherSelect.prop('disabled', false);
                        },
                        error: function() {
                            teacherSelect.prop('disabled', false);
                        }
                    });
                }
            }

            $('#subject_id, #class_section_id').on('change', function() {
                filterTeachers();
            });

            // Initial filter if subject is already selected (e.g. on Edit)
            if($('#subject_id').val()) {
                // filterTeachers(); 
                // We might not want to filter immediately if it removes the current valid teacher, 
                // but our logic preserves it if found.
            }
        });
    </script>
@endpush
