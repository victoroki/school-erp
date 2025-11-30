<!-- Student Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('student_id', 'Student:') !!}
    {!! Form::select('student_id', $students ?? [], null, ['class' => 'form-control', 'placeholder' => 'Select Student']) !!}
</div>

<!-- Route Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('route_id', 'Route:') !!}
    {!! Form::select('route_id', $routes ?? [], null, ['class' => 'form-control', 'placeholder' => 'Select Route']) !!}
</div>

<!-- Stop Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('stop_id', 'Stop:') !!}
    {!! Form::select('stop_id', $stops ?? [], null, ['class' => 'form-control', 'placeholder' => 'Select Stop']) !!}
</div>

<!-- Fee Amount Field -->
<div class="form-group col-sm-6">
    {!! Form::label('fee_amount', 'Fee Amount:') !!}
    {!! Form::number('fee_amount', null, ['class' => 'form-control', 'step' => '0.01']) !!}
</div>

<!-- Payment Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('payment_status', 'Payment Status:') !!}
    {!! Form::select('payment_status', ['paid' => 'Paid', 'unpaid' => 'Unpaid', 'partially_paid' => 'Partially Paid'], null, ['class' => 'form-control']) !!}
</div>

<!-- Academic Year Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('academic_year_id', 'Academic Year:') !!}
    {!! Form::select('academic_year_id', $academicYears ?? [], null, ['class' => 'form-control', 'placeholder' => 'Select Academic Year']) !!}
</div>