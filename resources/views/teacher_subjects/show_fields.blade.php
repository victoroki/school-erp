<!-- Staff Id Field -->
<div class="col-sm-12">
    {!! Form::label('staff_id', 'Teacher:') !!}
    <p>{{ $teacherSubject->staff->full_name ?? 'N/A' }}</p>
</div>

<!-- Subject Id Field -->
<div class="col-sm-12">
    {!! Form::label('subject_id', 'Subject:') !!}
    <p>{{ $teacherSubject->subject->name ?? 'N/A' }}</p>
</div>

<!-- Class Section Id Field -->
<div class="col-sm-12">
    {!! Form::label('class_section_id', 'Class / Stream:') !!}
    <p>{{ optional($teacherSubject->classSection->schoolClass)->name ?? 'N/A' }}{{ optional($teacherSubject->classSection->section)->name ? ' — '.$teacherSubject->classSection->section->name : '' }}</p>
</div>

<!-- Academic Year Id Field -->
<div class="col-sm-12">
    {!! Form::label('academic_year_id', 'Academic Year:') !!}
    <p>{{ $teacherSubject->academicYear->name ?? 'N/A' }}</p>
</div>

