<!-- Exam Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('exam_id', 'Exam:') !!}
    {!! Form::select('exam_id', $exams, null, ['class' => 'form-control', 'placeholder' => 'Exams']) !!}
</div>

<!-- Class Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('class_id', 'Class:') !!}
    {!! Form::select('class_id', $classes, null, ['class' => 'form-control', 'placeholder' => 'Class']) !!}
</div>

<!-- Subject Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('subject_id', 'Subject:') !!}
    {!! Form::select('subject_id', $subjects,  null, ['class' => 'form-control', 'placeholder' => 'Subjects']) !!}
</div>

<!-- Exam Date Field -->
<div class="form-group col-sm-6">
    {!! Form::label('exam_date', 'Exam Date:') !!}
    {!! Form::text('exam_date', null, ['class' => 'form-control','id'=>'exam_date']) !!}
</div>

@push('page_scripts')
    <script type="text/javascript">
        $('#exam_date').datepicker()
    </script>
@endpush

<!-- Start Time Field -->
<div class="form-group col-sm-6">
    {!! Form::label('start_time', 'Start Time:') !!}
    {!! Form::text('start_time', null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- End Time Field -->
<div class="form-group col-sm-6">
    {!! Form::label('end_time', 'End Time:') !!}
    {!! Form::text('end_time', null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Room Id Field
<div class="form-group col-sm-6">
    {!! Form::label('room_id', 'Room :') !!}
    {!! Form::number('room_id',  null, ['class' => 'form-control']) !!}
</div> -->

<!-- Max Marks Field -->
<div class="form-group col-sm-6">
    {!! Form::label('max_marks', 'Max Marks:') !!}
    {!! Form::number('max_marks', null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Passing Marks Field -->
<div class="form-group col-sm-6">
    {!! Form::label('passing_marks', 'Passing Marks:') !!}
    {!! Form::number('passing_marks', null, ['class' => 'form-control', 'required']) !!}
</div>