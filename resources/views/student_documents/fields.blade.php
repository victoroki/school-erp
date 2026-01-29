<div class="row w-100">
    <!-- Student Id Field -->
    <div class="form-group col-sm-6">
        {!! Form::label('student_id', 'Student:') !!}
        {!! Form::select('student_id', $students, null, ['class' => 'form-control select2', 'placeholder' => 'Select Student', 'required']) !!}
    </div>

    <!-- Document Category Field -->
    <div class="form-group col-sm-6">
        {!! Form::label('document_category', 'Category:') !!}
        {!! Form::select('document_category', $categories, null, ['class' => 'form-control', 'placeholder' => 'Select Category', 'required']) !!}
    </div>

    <!-- Document Type Field -->
    <div class="form-group col-sm-6">
        {!! Form::label('document_type', 'Document Type:') !!}
        {!! Form::select('document_type', $documentTypes, null, ['class' => 'form-control', 'placeholder' => 'Select Document Type', 'required']) !!}
    </div>

    <!-- Document Name Field -->
    <div class="form-group col-sm-6">
        {!! Form::label('document_name', 'Document Name:') !!}
        {!! Form::text('document_name', null, ['class' => 'form-control', 'required', 'maxlength' => 100]) !!}
    </div>

    <!-- File Path Field -->
    <div class="form-group col-sm-6">
        {!! Form::label('document_file', 'Upload Document:') !!}
        <div class="custom-file">
            {!! Form::file('document_file', ['class' => 'custom-file-input', 'accept' => '.pdf,.doc,.docx,.jpg,.jpeg,.png', (isset($studentDocument) ? '' : 'required')]) !!}
            {!! Form::label('document_file', 'Choose file', ['class' => 'custom-file-label']) !!}
        </div>
        <small class="form-text text-muted">Accepted: PDF, Word, Images (Max: 5MB)</small>
        @if(isset($studentDocument) && $studentDocument->file_path)
            <div class="mt-2">
                <span class="badge badge-info"><i class="fas fa-file"></i> Current File: {{ basename($studentDocument->file_path) }}</span>
            </div>
        @endif
    </div>

    <!-- Additional Fields -->
    <div class="form-group col-sm-3">
        {!! Form::label('expiry_date', 'Expiry Date:') !!}
        {!! Form::date('expiry_date', null, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group col-sm-3">
        <label class="d-block">Options</label>
        <div class="custom-control custom-checkbox mt-2">
            {!! Form::hidden('is_mandatory', 0) !!}
            {!! Form::checkbox('is_mandatory', 1, null, ['class' => 'custom-control-input', 'id' => 'is_mandatory']) !!}
            <label class="custom-control-label" for="is_mandatory">Is Mandatory</label>
        </div>
    </div>

    <div class="form-group col-sm-12">
        {!! Form::label('notes', 'Notes:') !!}
        {!! Form::textarea('notes', null, ['class' => 'form-control', 'rows' => 2]) !!}
    </div>
</div>

@push('page_scripts')
<script type="text/javascript">
    $(document).ready(function() {
        // Handle file input label
        $('.custom-file-input').on('change', function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').addClass("selected").html(fileName);
        });

        // Initialize Select2 if available
        if (typeof $.fn.select2 !== 'undefined') {
            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%'
            });
        }
    });
</script>
@endpush