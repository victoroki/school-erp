<!-- Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('name', 'Name:') !!}
    {!! Form::text('name', null, ['class' => 'form-control', 'required', 'maxlength' => 20, 'maxlength' => 20]) !!}
</div>

<!-- Min Percentage Field -->
<div class="form-group col-sm-6">
    {!! Form::label('min_percentage', 'Min Percentage:') !!}
    {!! Form::number('min_percentage', null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Max Percentage Field -->
<div class="form-group col-sm-6">
    {!! Form::label('max_percentage', 'Max Percentage:') !!}
    {!! Form::number('max_percentage', null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Grade Point Field -->
<div class="form-group col-sm-6">
    {!! Form::label('grade_point', 'Grade Point:') !!}
    {!! Form::number('grade_point', null, ['class' => 'form-control']) !!}
</div>

<!-- Description Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('description', 'Description:') !!}
    {!! Form::textarea('description', null, ['class' => 'form-control', 'maxlength' => 65535, 'maxlength' => 65535]) !!}
</div>