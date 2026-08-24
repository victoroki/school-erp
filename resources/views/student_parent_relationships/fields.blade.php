<!-- Student Field -->
<div class="form-group col-sm-6">
    <label>Student <span class="text-danger">*</span></label>
    <select name="student_id" id="student-select" class="form-control" required>
        @if(!empty($selectedStudent))
            <option value="{{ $selectedStudent->student_id }}" selected>{{ trim($selectedStudent->first_name . ' ' . $selectedStudent->last_name) }} ({{ $selectedStudent->admission_no ?? 'ID ' . $selectedStudent->student_id }})</option>
        @endif
    </select>
    <small class="form-text text-muted"><i class="fas fa-search mr-1"></i>Type a name or admission number to search.</small>
</div>

<!-- Parent Field -->
<div class="form-group col-sm-6">
    <label>Parent / Guardian <span class="text-danger">*</span></label>
    <select name="parent_id" id="parent-select" class="form-control" required>
        @if(!empty($selectedParent))
            <option value="{{ $selectedParent->parent_id }}" selected>{{ trim($selectedParent->first_name . ' ' . $selectedParent->last_name) }}{{ !empty($selectedParent->relationship) ? ' &middot; ' . ucfirst($selectedParent->relationship) : '' }}</option>
        @endif
    </select>
    <small class="form-text text-muted"><i class="fas fa-search mr-1"></i>Type a name, phone number, or email to search.</small>
</div>

<!-- Is Primary Contact Field -->
<div class="form-group col-sm-6 d-flex align-items-center">
    <div class="custom-control custom-switch custom-switch-off-secondary custom-switch-on-success">
        {!! Form::hidden('is_primary_contact', 0) !!}
        <input type="checkbox" name="is_primary_contact" value="1" class="custom-control-input" id="isPrimarySwitch" {{ (!empty($isPrimary) && $isPrimary) ? 'checked' : '' }}>
        <label class="custom-control-label font-weight-bold" for="isPrimarySwitch">Primary Contact</label>
    </div>
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
        // yet: the Vite bundle loads as a deferred module, so this classic
        // script polls until jQuery + Select2 are available.
        (function () {
            function initSelect2() {
                if (!(window.jQuery && window.jQuery.fn && window.jQuery.fn.select2)) {
                    return false;
                }

                var $ = window.jQuery;

                function ajaxSearch(url) {
                    return {
                        url: url,
                        dataType: 'json',
                        delay: 300,
                        data: function (params) {
                            return { q: params.term || '', page: params.page || 1 };
                        },
                        processResults: function (data) {
                            return { results: data.results, pagination: data.pagination };
                        }
                    };
                }

                $('#student-select').select2({
                    theme: 'bootstrap4',
                    width: '100%',
                    placeholder: 'Type to search by name or admission no...',
                    allowClear: true,
                    ajax: ajaxSearch('{{ route('student-parent-relationships.search-students') }}'),
                    minimumInputLength: 0
                });

                $('#parent-select').select2({
                    theme: 'bootstrap4',
                    width: '100%',
                    placeholder: 'Type to search by name, phone, or email...',
                    allowClear: true,
                    ajax: ajaxSearch('{{ route('student-parent-relationships.search-parents') }}'),
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
@endpush
