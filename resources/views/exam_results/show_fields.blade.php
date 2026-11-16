<!-- Exam Field -->
<div class="col-sm-12">
    {!! Form::label('exam_id', 'Exam:') !!}
    <p>{{ $examResult->exam ? $examResult->exam->name : 'N/A' }}</p>
</div>

<!-- Student Field -->
<div class="col-sm-12">
    {!! Form::label('student_id', 'Student:') !!}
    <p>{{ $examResult->student ? $examResult->student->full_name : 'N/A' }}</p>
</div>

<!-- Class Section Field -->
<div class="col-sm-12">
    {!! Form::label('class_section_id', 'Class Section:') !!}
    <p>{{ $examResult->classSection ? (optional($examResult->classSection->schoolClass)->name.' '.($examResult->classSection->section->name ?? '')) : 'N/A' }}</p>
</div>

<!-- Subject Field -->
<div class="col-sm-12">
    {!! Form::label('subject_id', 'Subject:') !!}
    <p>{{ $examResult->subject ? $examResult->subject->name : 'N/A' }}</p>
</div>

<!-- Marks Obtained Field -->
<div class="col-sm-12">
    {!! Form::label('marks_obtained', 'Marks Obtained:') !!}
    <p>{{ $examResult->marks_obtained }}</p>
</div>

<!-- Grade Field -->
<div class="col-sm-12">
    {!! Form::label('grade_id', 'Grade:') !!}
    <p>{{ $examResult->grade ? $examResult->grade->name : 'N/A' }}</p>
</div>

<!-- Remarks Field -->
<div class="col-sm-12">
    {!! Form::label('remarks', 'Remarks:') !!}
    <p>{{ $examResult->remarks }}</p>
</div>

<!-- Created By Field -->
<div class="col-sm-12">
    {!! Form::label('created_by', 'Created By:') !!}
    <p>{{ $examResult->createdBy ? $examResult->createdBy->full_name : 'System' }}</p>
</div>

