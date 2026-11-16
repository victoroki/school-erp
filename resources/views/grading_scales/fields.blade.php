<div class="row">
    <!-- Name Field -->
    <div class="form-group col-sm-4">
        {!! Form::label('name', 'Grade Letter') !!} <span class="text-danger">*</span>
        {!! Form::text('name', null, ['class' => 'form-control', 'required', 'maxlength' => 20, 'placeholder' => 'e.g. A, A-, B+']) !!}
        <small class="text-muted">8-4-4: A, A-, B+ ... E &nbsp;|&nbsp; CBC/CBE: EE, ME, AE, BE</small>
    </div>

    <!-- Grade Point Field -->
    <div class="form-group col-sm-4">
        {!! Form::label('grade_point', 'Points') !!}
        {!! Form::number('grade_point', null, ['class' => 'form-control', 'step' => '1', 'min' => 1, 'max' => 12, 'placeholder' => 'e.g. 12']) !!}
        <small class="text-muted">8-4-4: E=1 up to A=12 | CBC/CBE: BE=1 up to EE=4</small>
    </div>

    <div class="col-sm-4"></div>

    <!-- Min Percentage Field -->
    <div class="form-group col-sm-4">
        {!! Form::label('min_percentage', 'Minimum %') !!} <span class="text-danger">*</span>
        <div class="input-group">
            {!! Form::number('min_percentage', null, ['class' => 'form-control', 'required', 'step' => '0.01', 'min' => 0, 'max' => 100]) !!}
            <div class="input-group-append">
                <span class="input-group-text">%</span>
            </div>
        </div>
        <small class="text-muted">e.g. A starts at 80%</small>
    </div>

    <!-- Max Percentage Field -->
    <div class="form-group col-sm-4">
        {!! Form::label('max_percentage', 'Maximum %') !!} <span class="text-danger">*</span>
        <div class="input-group">
            {!! Form::number('max_percentage', null, ['class' => 'form-control', 'required', 'step' => '0.01', 'min' => 0, 'max' => 100]) !!}
            <div class="input-group-append">
                <span class="input-group-text">%</span>
            </div>
        </div>
        <small class="text-muted">e.g. A ends at 100%</small>
    </div>

    <div class="col-sm-4"></div>

    <!-- Description Field -->
    <div class="form-group col-sm-12">
        {!! Form::label('description', 'Performance Remarks') !!}
        {!! Form::text('description', null, ['class' => 'form-control', 'placeholder' => 'e.g. Excellent, Average, Fail']) !!}
    </div>
</div>
