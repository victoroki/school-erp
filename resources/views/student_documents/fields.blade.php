<!-- Student Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('student_id', 'Student:') !!}
    {!! Form::select('student_id', $students, null, ['class' => 'form-control', 'placeholder' => 'Select Student', 'required']) !!}
</div>

<!-- Document Type Field -->
<div class="form-group col-sm-6">
    {!! Form::label('document_type', 'Document Type:') !!}
    {!! Form::select('document_type', $documentTypes, null, ['class' => 'form-control', 'placeholder' => 'Select Document Type', 'required']) !!}
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