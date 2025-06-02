<!-- Student Id Field -->
<div class="col-sm-12">
    {!! Form::label('student_id', 'Student Id:') !!}
    <p>{{ $studentDocument->student_id }}</p>
</div>

<!-- Document Type Field -->
<div class="col-sm-12">
    {!! Form::label('document_type', 'Document Type:') !!}
    <p>{{ $studentDocument->document_type }}</p>
</div>

<!-- Document Name Field -->
<div class="col-sm-12">
    {!! Form::label('document_name', 'Document Name:') !!}
    <p>{{ $studentDocument->document_name }}</p>
</div>

<!-- File Path Field -->
<div class="col-sm-12">
    {!! Form::label('file_path', 'File Path:') !!}
    <p>{{ $studentDocument->file_path }}</p>
</div>

<!-- Uploaded At Field -->
<div class="col-sm-12">
    {!! Form::label('uploaded_at', 'Uploaded At:') !!}
    <p>{{ $studentDocument->uploaded_at }}</p>
</div>

