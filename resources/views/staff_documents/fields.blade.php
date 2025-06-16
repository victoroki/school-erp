<!-- Staff Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('staff_id', 'Staff Id:') !!}
    {!! Form::select('staff_id', $staffs, null, ['class' => 'form-control', 'placeholder' => 'Select Staff']) !!}
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
    {!! Form::label('document_file', 'Upload Document:') !!}
    {!! Form::file('document_file', ['class' => 'form-control-file', 'accept' => '.pdf,.doc,.docx,.jpg,.jpeg,.png', 'required']) !!}
    <small class="form-text text-muted">Accepted formats: PDF, DOC, DOCX, JPG, JPEG, PNG (Max: 5MB)</small>
</div>

@push('page_scripts')
    <script type="text/javascript">
        $('#uploaded_at').datepicker()
    </script>
@endpush