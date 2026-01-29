<div class="row">
    <!-- Name Field -->
    <div class="form-group col-sm-4">
        {!! Form::label('name', 'Grade Letter') !!}
        {!! Form::text('name', null, ['class' => 'form-control', 'required', 'maxlength' => 20, 'placeholder' => 'e.g. A+']) !!}
        <small class="text-muted">The grade symbol.</small>
    </div>

    <!-- Grade Point Field -->
    <div class="form-group col-sm-4">
        {!! Form::label('grade_point', 'GPA Value') !!}
        {!! Form::number('grade_point', null, ['class' => 'form-control', 'step' => '0.01', 'placeholder' => 'e.g. 4.00']) !!}
        <small class="text-muted">Numeric value for GPA.</small>
    </div>

    <div class="col-sm-4"></div>

    <!-- Min Percentage Field -->
    <div class="form-group col-sm-4">
        {!! Form::label('min_percentage', 'Minimum %') !!}
        <div class="input-group">
            {!! Form::number('min_percentage', null, ['class' => 'form-control', 'required', 'step' => '0.01', 'min' => 0, 'max' => 100]) !!}
            <div class="input-group-append">
                <span class="input-group-text">%</span>
            </div>
        </div>
    </div>

    <!-- Max Percentage Field -->
    <div class="form-group col-sm-4">
        {!! Form::label('max_percentage', 'Maximum %') !!}
        <div class="input-group">
            {!! Form::number('max_percentage', null, ['class' => 'form-control', 'required', 'step' => '0.01', 'min' => 0, 'max' => 100]) !!}
            <div class="input-group-append">
                <span class="input-group-text">%</span>
            </div>
        </div>
    </div>

    <!-- Description Field -->
    <div class="form-group col-sm-12">
        {!! Form::label('description', 'Performance Remarks') !!}
        {!! Form::text('description', null, ['class' => 'form-control', 'placeholder' => 'e.g. Excellent Performance']) !!}
    </div>
</div>