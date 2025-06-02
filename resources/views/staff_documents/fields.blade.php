<!-- Staff Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('staff_id', 'Staff Id:') !!}
    {!! Form::number('staff_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Document Type Field -->
<div class="form-group col-sm-6">
    {!! Form::label('document_type', 'Document Type:') !!}
    {!! Form::text('document_type', null, ['class' => 'form-control', 'required', 'maxlength' => 50, 'maxlength' => 50]) !!}
</div>

<!-- Document Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('document_name', 'Document Name:') !!}
    {!! Form::text('document_name', null, ['class' => 'form-control', 'required', 'maxlength' => 100, 'maxlength' => 100]) !!}
</div>

<!-- File Path Field -->
<div class="form-group col-sm-6">
    {!! Form::label('file_path', 'File Path:') !!}
    {!! Form::text('file_path', null, ['class' => 'form-control', 'required', 'maxlength' => 255, 'maxlength' => 255]) !!}
</div>

<!-- Uploaded At Field -->
<div class="form-group col-sm-6">
    {!! Form::label('uploaded_at', 'Uploaded At:') !!}
    {!! Form::text('uploaded_at', null, ['class' => 'form-control','id'=>'uploaded_at']) !!}
</div>

@push('page_scripts')
    <script type="text/javascript">
        $('#uploaded_at').datepicker()
    </script>
@endpush