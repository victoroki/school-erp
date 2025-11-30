<!-- Student Id Field -->
<div class="col-sm-12">
    {!! Form::label('student_id', 'Student:') !!}
    <p>{{ $transportRegistration->student->name ?? 'N/A' }}</p>
</div>

<!-- Route Id Field -->
<div class="col-sm-12">
    {!! Form::label('route_id', 'Route:') !!}
    <p>{{ $transportRegistration->route->name ?? 'N/A' }}</p>
</div>

<!-- Stop Id Field -->
<div class="col-sm-12">
    {!! Form::label('stop_id', 'Stop:') !!}
    <p>{{ $transportRegistration->stop->stop_name ?? 'N/A' }}</p>
</div>

<!-- Fee Amount Field -->
<div class="col-sm-12">
    {!! Form::label('fee_amount', 'Fee Amount:') !!}
    <p>{{ $transportRegistration->fee_amount }}</p>
</div>

<!-- Payment Status Field -->
<div class="col-sm-12">
    {!! Form::label('payment_status', 'Payment Status:') !!}
    <p>{{ $transportRegistration->payment_status }}</p>
</div>

<!-- Academic Year Id Field -->
<div class="col-sm-12">
    {!! Form::label('academic_year_id', 'Academic Year:') !!}
    <p>{{ $transportRegistration->academicYear->name ?? 'N/A' }}</p>
</div>