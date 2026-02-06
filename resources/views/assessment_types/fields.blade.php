<div class="row">
    <!-- Name Field -->
    <div class="form-group col-sm-6">
        {!! Form::label('name', 'Name:', ['class' => 'font-weight-bold']) !!}
        {!! Form::text('name', null, ['class' => 'form-control', 'required', 'placeholder' => 'e.g. CAT 1, Final Exam']) !!}
    </div>

    <!-- Code Field -->
    <div class="form-group col-sm-6">
        {!! Form::label('code', 'Short Code:', ['class' => 'font-weight-bold']) !!}
        {!! Form::text('code', null, ['class' => 'form-control', 'placeholder' => 'e.g. CAT1']) !!}
    </div>

    <!-- Max Marks Field -->
    <div class="form-group col-sm-4">
        {!! Form::label('max_marks', 'Max Marks:', ['class' => 'font-weight-bold']) !!}
        {!! Form::number('max_marks', null, ['class' => 'form-control', 'required', 'step' => '0.01']) !!}
    </div>

    <!-- Weightage Field -->
    <div class="form-group col-sm-4">
        {!! Form::label('weightage', 'Weightage (%):', ['class' => 'font-weight-bold']) !!}
        {!! Form::number('weightage', null, ['class' => 'form-control', 'required', 'step' => '0.01', 'max' => 100]) !!}
    </div>

    <!-- Is Cbc Field -->
    <div class="form-group col-sm-4">
        {!! Form::label('is_cbc', 'Curriculum:', ['class' => 'font-weight-bold']) !!}
        <div class="pt-2">
            <div class="custom-control custom-radio custom-control-inline">
                {!! Form::radio('is_cbc', 0, true, ['id' => 'cur_844', 'class' => 'custom-control-input']) !!}
                {!! Form::label('cur_844', '8-4-4', ['class' => 'custom-control-label']) !!}
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                {!! Form::radio('is_cbc', 1, false, ['id' => 'cur_cbc', 'class' => 'custom-control-input']) !!}
                {!! Form::label('cur_cbc', 'CBC', ['class' => 'custom-control-label']) !!}
            </div>
        </div>
    </div>

    <!-- Description Field -->
    <div class="form-group col-sm-12">
        {!! Form::label('description', 'Description:', ['class' => 'font-weight-bold']) !!}
        {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => 3]) !!}
    </div>

    <!-- Status Field -->
    <div class="form-group col-sm-12">
        <div class="custom-control custom-switch">
            {!! Form::hidden('status', 0) !!}
            {!! Form::checkbox('status', 1, null, ['class' => 'custom-control-input', 'id' => 'statusSwitch']) !!}
            {!! Form::label('statusSwitch', 'Active Status', ['class' => 'custom-control-input-label']) !!}
        </div>
    </div>
</div>
