<!-- Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('name', 'Name:') !!}
    {!! Form::text('name', null, ['class' => 'form-control', 'required', 'maxlength' => 50, 'maxlength' => 50]) !!}
</div>

<!-- Start Date Field -->
<div class="form-group col-sm-6">
    {!! Form::label('start_date', 'Start Date:') !!}
    {!! Form::date('start_date', isset($academicYear) && $academicYear->start_date ? $academicYear->start_date->format('Y-m-d') : null, ['class' => 'form-control','id'=>'start_date']) !!}
</div>

<!-- End Date Field -->
<div class="form-group col-sm-6">
    {!! Form::label('end_date', 'End Date:') !!}
    {!! Form::date('end_date', isset($academicYear) && $academicYear->end_date ? $academicYear->end_date->format('Y-m-d') : null, ['class' => 'form-control','id'=>'end_date']) !!}
</div>



<!-- Is Current Field -->
<div class="form-group col-sm-6">
    <div class="form-check">
        {!! Form::hidden('is_current', 0, ['class' => 'form-check-input']) !!}
        {!! Form::checkbox('is_current', '1', null, ['class' => 'form-check-input']) !!}
        {!! Form::label('is_current', 'Is Current', ['class' => 'form-check-label']) !!}
    </div>
</div>