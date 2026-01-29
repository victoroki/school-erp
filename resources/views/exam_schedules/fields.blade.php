<div class="row">
    <!-- Exam Id Field -->
    <div class="form-group col-sm-6">
        {!! Form::label('exam_id', 'Exam Session') !!}
        {!! Form::select('exam_id', $exams, null, ['class' => 'form-control', 'required', 'placeholder' => 'Select Session']) !!}
    </div>

    <!-- Class Id Field -->
    <div class="form-group col-sm-6">
        {!! Form::label('class_id', 'School Class') !!}
        {!! Form::select('class_id', $classes, null, ['class' => 'form-control', 'required', 'placeholder' => 'Select Class']) !!}
    </div>

    <!-- Subject Id Field -->
    <div class="form-group col-sm-6">
        {!! Form::label('subject_id', 'Subject') !!}
        {!! Form::select('subject_id', $subjects, null, ['class' => 'form-control', 'required', 'placeholder' => 'Select Subject']) !!}
    </div>

    <!-- Room Id Field -->
    <div class="form-group col-sm-6">
        {!! Form::label('room_id', 'Classroom/Venue') !!}
        {!! Form::select('room_id', $rooms, null, ['class' => 'form-control', 'placeholder' => 'Select Venue']) !!}
    </div>

    <div class="col-12"><hr></div>

    <!-- Exam Date Field -->
    <div class="form-group col-sm-4">
        {!! Form::label('exam_date', 'Examination Date') !!}
        {!! Form::date('exam_date', $examSchedule->exam_date ?? null, ['class' => 'form-control', 'required']) !!}
    </div>

    <!-- Start Time Field -->
    <div class="form-group col-sm-4">
        {!! Form::label('start_time', 'Starts At') !!}
        {!! Form::time('start_time', null, ['class' => 'form-control', 'required']) !!}
    </div>

    <!-- End Time Field -->
    <div class="form-group col-sm-4">
        {!! Form::label('end_time', 'Ends At') !!}
        {!! Form::time('end_time', null, ['class' => 'form-control', 'required']) !!}
    </div>

    <div class="col-12"><hr></div>

    <!-- Max Marks Field -->
    <div class="form-group col-sm-6">
        {!! Form::label('max_marks', 'Maximum Possible Marks') !!}
        {!! Form::number('max_marks', null, ['class' => 'form-control', 'required', 'step' => '0.01', 'placeholder' => 'e.g. 100']) !!}
    </div>

    <!-- Passing Marks Field -->
    <div class="form-group col-sm-6">
        {!! Form::label('passing_marks', 'Passing Threshold (Marks)') !!}
        {!! Form::number('passing_marks', null, ['class' => 'form-control', 'required', 'step' => '0.01', 'placeholder' => 'e.g. 40']) !!}
    </div>
</div>