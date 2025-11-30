<!-- Exam Field -->
<div class="col-sm-12">
    {!! Form::label('exam', 'Exam:') !!}
    <p>{{ $examSchedule->exam->name ?? 'N/A' }}</p>
</div>

<!-- Class Field -->
<div class="col-sm-12">
    {!! Form::label('class', 'Class:') !!}
    <p>{{ $examSchedule->class->class_name ?? 'N/A' }}</p>
</div>

<!-- Subject Field -->
<div class="col-sm-12">
    {!! Form::label('subject', 'Subject:') !!}
    <p>{{ $examSchedule->subject->name ?? 'N/A' }}</p>
</div>

<!-- Exam Date Field -->
<div class="col-sm-12">
    {!! Form::label('exam_date', 'Exam Date:') !!}
    <p>{{ $examSchedule->exam_date }}</p>
</div>

<!-- Start Time Field -->
<div class="col-sm-12">
    {!! Form::label('start_time', 'Start Time:') !!}
    <p>{{ $examSchedule->start_time }}</p>
</div>

<!-- End Time Field -->
<div class="col-sm-12">
    {!! Form::label('end_time', 'End Time:') !!}
    <p>{{ $examSchedule->end_time }}</p>
</div>

<!-- Room Id Field -->
<div class="col-sm-12">
    {!! Form::label('room_id', 'Room Id:') !!}
    <p>{{ $examSchedule->room_id }}</p>
</div>

<!-- Max Marks Field -->
<div class="col-sm-12">
    {!! Form::label('max_marks', 'Max Marks:') !!}
    <p>{{ $examSchedule->max_marks }}</p>
</div>

<!-- Passing Marks Field -->
<div class="col-sm-12">
    {!! Form::label('passing_marks', 'Passing Marks:') !!}
    <p>{{ $examSchedule->passing_marks }}</p>
</div>

