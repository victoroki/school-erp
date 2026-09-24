<!-- Class Section Id Field -->
<div class="col-sm-12">
    {!! Form::label('class_section_id', 'Class / Stream:') !!}
    <p>{{ optional($timetable->classSection->schoolClass)->name ?? 'N/A' }}{{ optional($timetable->classSection->section)->name ? ' — '.$timetable->classSection->section->name : '' }}</p>
</div>

<!-- Day Of Week Field -->
<div class="col-sm-12">
    {!! Form::label('day_of_week', 'Day Of Week:') !!}
    <p>{{ $timetable->day_of_week ? ucfirst($timetable->day_of_week) : '—' }}</p>
</div>

<!-- Period Id Field -->
<div class="col-sm-12">
    {!! Form::label('period_id', 'Period:') !!}
    <p>{{ $timetable->period->name ?? 'N/A' }}</p>
</div>

<!-- Subject Id Field -->
<div class="col-sm-12">
    {!! Form::label('subject_id', 'Subject:') !!}
    <p>{{ $timetable->subject->name ?? 'N/A' }}</p>
</div>

<!-- Teacher Id Field -->
<div class="col-sm-12">
    {!! Form::label('teacher_id', 'Teacher:') !!}
    <p>{{ $timetable->teacher->full_name ?? 'N/A' }}</p>
</div>

<!-- Classroom Id Field -->
<div class="col-sm-12">
    {!! Form::label('classroom_id', 'Classroom:') !!}
    <p>{{ $timetable->classroom->name ?? 'N/A' }}</p>
</div>

<!-- Academic Year Id Field -->
<div class="col-sm-12">
    {!! Form::label('academic_year_id', 'Academic Year:') !!}
    <p>{{ $timetable->academicYear->name ?? 'N/A' }}</p>
</div>

