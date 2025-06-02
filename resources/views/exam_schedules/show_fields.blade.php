<!-- Exam Id Field -->
<div class="col-sm-12">
    {!! Form::label('exam_id', 'Exam Id:') !!}
    <p>{{ $examSchedule->exam_id }}</p>
</div>

<!-- Class Id Field -->
<div class="col-sm-12">
    {!! Form::label('class_id', 'Class Id:') !!}
    <p>{{ $examSchedule->class_id }}</p>
</div>

<!-- Subject Id Field -->
<div class="col-sm-12">
    {!! Form::label('subject_id', 'Subject Id:') !!}
    <p>{{ $examSchedule->subject_id }}</p>
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

