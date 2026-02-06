<div class="row">
    <div class="form-group col-sm-6">
        {!! Form::label('strand_id', 'Strand:', ['class' => 'font-weight-bold']) !!}
        {!! Form::select('strand_id', $strands, null, ['class' => 'form-control select2', 'required', 'placeholder' => 'Select Strand']) !!}
    </div>

    <div class="form-group col-sm-6">
        {!! Form::label('name', 'Sub-Strand Name:', ['class' => 'font-weight-bold']) !!}
        {!! Form::text('name', null, ['class' => 'form-control', 'required', 'placeholder' => 'e.g. Addition of whole numbers']) !!}
    </div>

    <div class="form-group col-sm-12">
        {!! Form::label('description', 'Description:', ['class' => 'font-weight-bold']) !!}
        {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Brief description of this sub-strand']) !!}
    </div>
</div>
