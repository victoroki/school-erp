<!-- Name Field -->
<div class="form-group col-sm-12 mb-3">
    {!! Form::label('name', 'Class Name', ['class' => 'dash-label']) !!}
    {!! Form::text('name', null, ['class' => 'form-control dash-control', 'required', 'placeholder' => 'e.g. Grade 1 or Form 4']) !!}
</div>

<!-- Description Field -->
<div class="form-group col-sm-12">
    {!! Form::label('description', 'Description', ['class' => 'dash-label']) !!}
    {!! Form::textarea('description', null, ['class' => 'form-control dash-control', 'rows' => 3, 'placeholder' => 'Optional class description...']) !!}
</div>

<!-- Hidden Numeric Value (Defaults to 0 for system compatibility) -->
{!! Form::hidden('numeric_value', 0) !!}