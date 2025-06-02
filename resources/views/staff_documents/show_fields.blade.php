<!-- Staff Id Field -->
<div class="col-sm-12">
    {!! Form::label('staff_id', 'Staff Id:') !!}
    <p>{{ $staffDocument->staff_id }}</p>
</div>

<!-- Document Type Field -->
<div class="col-sm-12">
    {!! Form::label('document_type', 'Document Type:') !!}
    <p>{{ $staffDocument->document_type }}</p>
</div>

<!-- Document Name Field -->
<div class="col-sm-12">
    {!! Form::label('document_name', 'Document Name:') !!}
    <p>{{ $staffDocument->document_name }}</p>
</div>

<!-- File Path Field -->
<div class="col-sm-12">
    {!! Form::label('file_path', 'File Path:') !!}
    <p>{{ $staffDocument->file_path }}</p>
</div>

<!-- Uploaded At Field -->
<div class="col-sm-12">
    {!! Form::label('uploaded_at', 'Uploaded At:') !!}
    <p>{{ $staffDocument->uploaded_at }}</p>
</div>

