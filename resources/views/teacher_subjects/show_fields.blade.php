<!-- Staff Field -->
<div class="col-sm-12">
    {!! Form::label('staff', 'Staff:') !!}
    <p>{{ $teacherSubject->staff->first_name }} {{ $teacherSubject->staff->last_name }}</p>
</div>

<!-- Subject Field -->
<div class="col-sm-12">
    {!! Form::label('subject', 'Subject:') !!}
    <p>{{ $teacherSubject->subject->name }}</p>
</div>

<!-- Class Section Field -->
<div class="col-sm-12">
    {!! Form::label('class_section', 'Class Section:') !!}
    <p>{{ $teacherSubject->classSection->schoolClass->name }} - {{ $teacherSubject->classSection->section->name }}</p>
</div>

<!-- Academic Year Field -->
<div class="col-sm-12">
    {!! Form::label('academic_year', 'Academic Year:') !!}
    <p>{{ $teacherSubject->academicYear->name }}</p>
</div>

