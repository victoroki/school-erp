<div class="row">
    <!-- Name Field -->
    <div class="form-group col-sm-8">
        {!! Form::label('name', 'Exam Type Name') !!}
        {!! Form::text('name', null, ['class' => 'form-control', 'required', 'maxlength' => 100, 'placeholder' => 'e.g. Final Examination']) !!}
        <small class="text-muted">The full name of the examination category.</small>
    </div>

    <!-- Short Name Field -->
    <div class="form-group col-sm-4">
        {!! Form::label('short_name', 'Abbreviation') !!}
        {!! Form::text('short_name', null, ['class' => 'form-control', 'maxlength' => 20, 'placeholder' => 'e.g. FINAL']) !!}
        <small class="text-muted">Used in reports.</small>
    </div>

    <!-- Description Field -->
    <div class="form-group col-sm-12">
        {!! Form::label('description', 'Description') !!}
        {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Brief details about this exam type...']) !!}
    </div>
</div>