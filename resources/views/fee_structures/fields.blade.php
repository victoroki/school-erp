<div class="row">
    <!-- Academic Year Id Field -->
    <div class="form-group col-sm-6">
        {!! Form::label('academic_year_id', 'Academic Year') !!}
        {!! Form::select('academic_year_id', $academicYear, null, ['class' => 'form-control select2', 'placeholder' => 'Select Academic Year', 'required']) !!}
        <small class="text-muted">The academic year this fee applies to.</small>
    </div>

    <!-- Class Id Field -->
    <div class="form-group col-sm-6">
        {!! Form::label('class_id', 'Applicable Class') !!}
        {!! Form::select('class_id', $classes, null, ['class' => 'form-control select2', 'placeholder' => 'Select Class', 'required']) !!}
        <small class="text-muted">Students in this class will be eligible for this fee.</small>
    </div>

    <!-- Category Id Field -->
    <div class="form-group col-sm-6">
        {!! Form::label('category_id', 'Fee Category') !!}
        {!! Form::select('category_id', $category, null, ['class' => 'form-control select2', 'placeholder' => 'Select Fee Category', 'required']) !!}
        <small class="text-muted">E.g. Tuition, Transport, Lab Fee.</small>
    </div>

    <!-- Amount Field -->
    <div class="form-group col-sm-6">
        {!! Form::label('amount', 'Base Amount') !!}
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text">KSh</span>
            </div>
            {!! Form::number('amount', null, ['class' => 'form-control', 'required', 'step' => '0.01', 'placeholder' => '0.00']) !!}
        </div>
    </div>

    <!-- Due Date Field -->
    <div class="form-group col-sm-6">
        {!! Form::label('due_date', 'Payment Due Date') !!}
        {!! Form::date('due_date', null, ['class' => 'form-control', 'id' => 'due_date', 'required']) !!}
    </div>

    @if(!isset($feeStructure))
    <!-- Auto Assign Field (Only on Create) -->
    <div class="form-group col-sm-12">
        <div class="custom-control custom-checkbox mt-2">
            {!! Form::checkbox('auto_assign', 1, true, ['class' => 'custom-control-input', 'id' => 'auto_assign']) !!}
            {!! Form::label('auto_assign', 'Automatically assign this fee to ALL current students in the selected class', ['class' => 'custom-control-label font-weight-bold text-primary']) !!}
        </div>
        <p class="text-muted small ml-4">If checked, the system will immediately create student fee records for all currently enrolled students in this class.</p>
    </div>
    @endif
</div>