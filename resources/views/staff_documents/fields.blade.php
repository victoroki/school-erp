<!-- Staff Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('staff_id', 'Staff:') !!}
    <select name="staff_id" id="staff-select" class="form-control" required>
        @if(!empty($selectedStaff))
            <option value="{{ $selectedStaff->staff_id }}" selected>{{ trim($selectedStaff->first_name . ' ' . $selectedStaff->last_name) }} ({{ $selectedStaff->employee_number ?: 'ID ' . $selectedStaff->staff_id }})</option>
        @endif
    </select>
    <small class="form-text text-muted"><i class="fas fa-search mr-1"></i>Type a name, employee number, or staff id to search.</small>
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
    {!! Form::file('document_file', ['class' => 'form-control-file', 'accept' => '.pdf,.doc,.docx,.jpg,.jpeg,.png', (isset($staffDocument) ? '' : 'required')]) !!}
    <small class="form-text text-muted">Accepted formats: PDF, DOC, DOCX, JPG, JPEG, PNG (Max: 5MB)</small>
    @if(isset($staffDocument) && $staffDocument->file_path)
        <div class="mt-2">
            <span class="badge badge-info"><i class="fas fa-file"></i> Current File: {{ basename($staffDocument->file_path) }}</span>
        </div>
    @endif
</div>

@push('page_css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css">
    <style>
        .select2-container .select2-selection--single {
            height: 38px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px !important;
        }
    </style>
@endpush

@push('page_scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // Initialize Select2 AJAX search — safe even if jQuery is not ready
        // yet: polls until jQuery + Select2 are available.
        (function () {
            function initSelect2() {
                if (!(window.jQuery && window.jQuery.fn && window.jQuery.fn.select2)) {
                    return false;
                }

                var $ = window.jQuery;

                $('#staff-select').select2({
                    theme: 'bootstrap4',
                    width: '100%',
                    placeholder: 'Type to search by name, employee no, or id...',
                    allowClear: true,
                    ajax: {
                        url: '{{ route('staffDocuments.search-staff') }}',
                        dataType: 'json',
                        delay: 300,
                        data: function (params) {
                            return { q: params.term || '', page: params.page || 1 };
                        },
                        processResults: function (data) {
                            return { results: data.results, pagination: data.pagination };
                        }
                    },
                    minimumInputLength: 0
                });

                return true;
            }

            if (!initSelect2()) {
                var tries = 0;
                var timer = setInterval(function () {
                    tries++;
                    if (initSelect2() || tries > 100) {
                        clearInterval(timer);
                    }
                }, 50);
            }
        })();
    </script>
    <script type="text/javascript">
        $('#uploaded_at').datepicker()
    </script>
@endpush