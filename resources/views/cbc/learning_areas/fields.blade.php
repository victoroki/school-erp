<div class="row">
    <div class="form-group col-sm-6">
        {!! Form::label('name', 'Learning Area Name:', ['class' => 'font-weight-bold']) !!}
        {!! Form::text('name', null, ['class' => 'form-control', 'required', 'placeholder' => 'e.g. Mathematics, English Literacy']) !!}
    </div>

    <div class="form-group col-sm-3">
        {!! Form::label('code', 'Code:', ['class' => 'font-weight-bold']) !!}
        {!! Form::text('code', null, ['class' => 'form-control', 'placeholder' => 'e.g. MATH']) !!}
    </div>

    <div class="form-group col-sm-3">
        {!! Form::label('level', 'Grade Level:', ['class' => 'font-weight-bold']) !!}
        {!! Form::select('level', [
            'PP1' => 'Pre-Primary 1',
            'PP2' => 'Pre-Primary 2',
            'Grade 1' => 'Grade 1',
            'Grade 2' => 'Grade 2',
            'Grade 3' => 'Grade 3',
            'Grade 4' => 'Grade 4',
            'Grade 5' => 'Grade 5',
            'Grade 6' => 'Grade 6',
            'Grade 7' => 'Grade 7 (JSS)',
            'Grade 8' => 'Grade 8 (JSS)',
            'Grade 9' => 'Grade 9 (JSS)',
        ], null, ['class' => 'form-control select2', 'placeholder' => 'Select Grade']) !!}
    </div>

    <div class="form-group col-sm-12">
        {!! Form::label('description', 'Description:', ['class' => 'font-weight-bold']) !!}
        {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => 3]) !!}
    </div>

    <div class="form-group col-sm-12">
        <div class="custom-control custom-switch">
            {!! Form::hidden('status', 0) !!}
            {!! Form::checkbox('status', 1, null, ['class' => 'custom-control-input', 'id' => 'areaStatus']) !!}
            {!! Form::label('areaStatus', 'Active Status', ['class' => 'custom-control-input-label']) !!}
        </div>
    </div>
</div>
