<!-- Exam Type Id Field -->
<div class="col-sm-12">
    {!! Form::label('exam_type_id', 'Exam Type Id:') !!}
    <p>{{ $exam->exam_type_id }}</p>
</div>

<!-- Name Field -->
<div class="col-sm-12">
    {!! Form::label('name', 'Name:') !!}
    <p>{{ $exam->name }}</p>
</div>

<!-- Description Field -->
<div class="col-sm-12">
    {!! Form::label('description', 'Description:') !!}
    <p>{{ $exam->description }}</p>
</div>

<!-- Academic Year Id Field -->
<div class="col-sm-12">
    {!! Form::label('academic_year_id', 'Academic Year Id:') !!}
    <p>{{ $exam->academic_year_id }}</p>
</div>

<!-- Start Date Field -->
<div class="col-sm-12">
    {!! Form::label('start_date', 'Start Date:') !!}
    <p>{{ $exam->start_date }}</p>
</div>

<!-- End Date Field -->
<div class="col-sm-12">
    {!! Form::label('end_date', 'End Date:') !!}
    <p>{{ $exam->end_date }}</p>
</div>

<!-- Publish Result Field -->
<div class="col-sm-12">
    {!! Form::label('publish_result', 'Publish Result:') !!}
    <p>{{ $exam->publish_result }}</p>
</div>

