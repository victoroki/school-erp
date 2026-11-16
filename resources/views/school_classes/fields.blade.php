<!-- Name Field -->
<div class="form-group col-sm-12 mb-3">
    {!! Form::label('name', 'Class Name', ['class' => 'dash-label']) !!}
    {!! Form::text('name', null, ['class' => 'form-control dash-control', 'required', 'placeholder' => 'e.g. Grade 1 or Form 4']) !!}
</div>

<!-- Level / Order Field -->
<div class="form-group col-sm-6 mb-3">
    {!! Form::label('numeric_value', 'Level / Order', ['class' => 'dash-label']) !!}
    {!! Form::number('numeric_value', null, ['class' => 'form-control dash-control', 'min' => 0, 'max' => 99, 'placeholder' => 'e.g. 1']) !!}
    <small class="text-muted">
        Sorts this class in lists and matches it to subject grade levels.
        Leave blank for classes that have no grade level.
    </small>
</div>

<!-- Description Field -->
<div class="form-group col-sm-12">
    {!! Form::label('description', 'Description', ['class' => 'dash-label']) !!}
    {!! Form::textarea('description', null, ['class' => 'form-control dash-control', 'rows' => 3, 'placeholder' => 'Optional class description...']) !!}
</div>
