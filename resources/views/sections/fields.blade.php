<!-- Class Field -->
<div class="form-group col-sm-12 mb-3">
    {!! Form::label('class_id', 'Assigned Class Level', ['class' => 'dash-label']) !!}
    {!! Form::select('class_id', $classes, null, ['class' => 'form-control dash-control', 'placeholder' => 'Select Class', 'required']) !!}
</div>

<!-- Name Field -->
<div class="form-group col-sm-6 mb-3">
    {!! Form::label('name', 'Section Name', ['class' => 'dash-label']) !!}
    {!! Form::text('name', null, ['class' => 'form-control dash-control', 'required', 'placeholder' => 'e.g. A, B, North, South']) !!}
</div>

<!-- Capacity Field -->
<div class="form-group col-sm-6 mb-3">
    {!! Form::label('capacity', 'Student Capacity', ['class' => 'dash-label']) !!}
    {!! Form::number('capacity', null, ['class' => 'form-control dash-control', 'placeholder' => 'e.g. 40', 'min' => 1]) !!}
</div>