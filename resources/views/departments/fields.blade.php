<!-- Name Field -->
<div class="form-group col-sm-6 mb-3">
    {!! Form::label('name', 'Department Name', ['class' => 'dash-label']) !!}
    {!! Form::text('name', null, ['class' => 'form-control dash-control', 'required', 'placeholder' => 'e.g. Science, Administration, Finance', 'maxlength' => 100]) !!}
</div>

<!-- HOD Id Field -->
<div class="form-group col-sm-6 mb-3">
    {!! Form::label('hod_id', 'Head of Department (HOD)', ['class' => 'dash-label']) !!}
    {!! Form::select('hod_id', $staff, null, ['class' => 'form-control dash-control', 'placeholder' => 'Select an HOD Staff Member']) !!}
</div>

<!-- Description Field -->
<div class="form-group col-sm-12 mb-3">
    {!! Form::label('description', 'Department Description', ['class' => 'dash-label']) !!}
    {!! Form::textarea('description', null, ['class' => 'form-control dash-control', 'placeholder' => 'Define the scope and responsibilities of this unit...', 'rows' => 3]) !!}
</div>