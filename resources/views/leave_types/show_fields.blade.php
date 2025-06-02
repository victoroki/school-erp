<!-- Name Field -->
<div class="col-sm-12">
    {!! Form::label('name', 'Name:') !!}
    <p>{{ $leaveType->name }}</p>
</div>

<!-- Days Allowed Field -->
<div class="col-sm-12">
    {!! Form::label('days_allowed', 'Days Allowed:') !!}
    <p>{{ $leaveType->days_allowed }}</p>
</div>

<!-- Description Field -->
<div class="col-sm-12">
    {!! Form::label('description', 'Description:') !!}
    <p>{{ $leaveType->description }}</p>
</div>

<!-- Is Paid Field -->
<div class="col-sm-12">
    {!! Form::label('is_paid', 'Is Paid:') !!}
    <p>{{ $leaveType->is_paid }}</p>
</div>

