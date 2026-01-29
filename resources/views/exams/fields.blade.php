<div class="row">
    <!-- Name Field -->
    <div class="form-group col-sm-6">
        {!! Form::label('name', 'Examination Name') !!}
        {!! Form::text('name', null, ['class' => 'form-control', 'required', 'maxlength' => 100, 'placeholder' => 'e.g. Second Term Final 2025']) !!}
    </div>

    <!-- Exam Type Id Field -->
    <div class="form-group col-sm-3">
        {!! Form::label('exam_type_id', 'Category') !!}
        {!! Form::select('exam_type_id', $examtypes, null, ['class' => 'form-control', 'required', 'placeholder' => 'Select Type']) !!}
    </div>

    <!-- Academic Year Id Field -->
    <div class="form-group col-sm-3">
        {!! Form::label('academic_year_id', 'Academic Session') !!}
        {!! Form::select('academic_year_id', $academicYears, null, ['class' => 'form-control', 'required', 'placeholder' => 'Select Year']) !!}
    </div>

    <!-- Start Date Field -->
    <div class="form-group col-sm-6">
        {!! Form::label('start_date', 'Commencement Date') !!}
        {!! Form::date('start_date', $exam->start_date ?? null, ['class' => 'form-control', 'required', 'id' => 'start_date']) !!}
    </div>

    <!-- End Date Field -->
    <div class="form-group col-sm-6">
        {!! Form::label('end_date', 'Completion Date') !!}
        {!! Form::date('end_date', $exam->end_date ?? null, ['class' => 'form-control', 'required', 'id' => 'end_date']) !!}
    </div>

    <!-- Description Field -->
    <div class="form-group col-sm-12">
        {!! Form::label('description', 'Exam Session Notes') !!}
        {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => 2, 'placeholder' => 'Any special instructions or details...']) !!}
    </div>

    <!-- Publish Result Field -->
    <div class="form-group col-sm-12">
        <hr>
        <div class="custom-control custom-switch">
            {!! Form::hidden('publish_result', 0) !!}
            {!! Form::checkbox('publish_result', '1', null, ['class' => 'custom-control-input', 'id' => 'publish_result']) !!}
            {!! Form::label('publish_result', 'Make Results Visible to Parents/Students immediately after entry', ['class' => 'custom-control-label']) !!}
        </div>
    </div>
</div>