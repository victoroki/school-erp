<!-- Name Field -->
<div class="col-sm-12">
    {!! Form::label('name', 'Name:') !!}
    <p>{{ $gradingScale->name }}</p>
</div>

<!-- Min Percentage Field -->
<div class="col-sm-12">
    {!! Form::label('min_percentage', 'Min Percentage:') !!}
    <p>{{ $gradingScale->min_percentage }}</p>
</div>

<!-- Max Percentage Field -->
<div class="col-sm-12">
    {!! Form::label('max_percentage', 'Max Percentage:') !!}
    <p>{{ $gradingScale->max_percentage }}</p>
</div>

<!-- Grade Point Field -->
<div class="col-sm-12">
    {!! Form::label('grade_point', 'Grade Point:') !!}
    <p>{{ $gradingScale->grade_point }}</p>
</div>

<!-- Description Field -->
<div class="col-sm-12">
    {!! Form::label('description', 'Description:') !!}
    <p>{{ $gradingScale->description }}</p>
</div>

