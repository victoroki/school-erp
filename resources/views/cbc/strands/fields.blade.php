<div class="row">
    <div class="form-group col-sm-6">
        {!! Form::label('learning_area_id', 'Learning Area:', ['class' => 'font-weight-bold']) !!}
        {!! Form::select('learning_area_id', $learningAreas, null, ['class' => 'form-control select2', 'required', 'placeholder' => 'Select Learning Area']) !!}
    </div>

    <div class="form-group col-sm-6">
        {!! Form::label('name', 'Strand Name:', ['class' => 'font-weight-bold']) !!}
        {!! Form::text('name', null, ['class' => 'form-control', 'required', 'placeholder' => 'e.g. Numbers']) !!}
    </div>

    <div class="form-group col-sm-12">
        {!! Form::label('description', 'Description:', ['class' => 'font-weight-bold']) !!}
        {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Brief description of this strand']) !!}
    </div>
</div>
