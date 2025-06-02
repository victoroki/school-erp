<!-- Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('name', 'Name:') !!}
    {!! Form::text('name', null, ['class' => 'form-control', 'required', 'maxlength' => 50, 'maxlength' => 50]) !!}
</div>

<!-- Days Allowed Field -->
<div class="form-group col-sm-6">
    {!! Form::label('days_allowed', 'Days Allowed:') !!}
    {!! Form::number('days_allowed', null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Description Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('description', 'Description:') !!}
    {!! Form::textarea('description', null, ['class' => 'form-control', 'maxlength' => 65535, 'maxlength' => 65535]) !!}
</div>

<!-- Is Paid Field -->
<div class="form-group col-sm-6">
    <div class="form-check">
        {!! Form::hidden('is_paid', 0, ['class' => 'form-check-input']) !!}
        {!! Form::checkbox('is_paid', '1', null, ['class' => 'form-check-input']) !!}
        {!! Form::label('is_paid', 'Is Paid', ['class' => 'form-check-label']) !!}
    </div>
</div>