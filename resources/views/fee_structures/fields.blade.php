<div class="row">
    <!-- Academic Year Id Field -->
    <div class="form-group col-sm-6">
        {!! Form::label('academic_year_id', 'Academic Year') !!}
        {!! Form::select('academic_year_id', $academicYear, null, ['class' => 'form-control select2', 'placeholder' => 'Select Academic Year', 'required']) !!}
        <small class="text-muted">The academic year this fee applies to.</small>
    </div>

    <!-- Class Id Field -->
    <div class="form-group col-sm-6">
        {!! Form::label('class_id', 'Applicable Class') !!}
        {!! Form::select('class_id', $classes, null, ['class' => 'form-control select2', 'placeholder' => 'Select Class', 'required']) !!}
        <small class="text-muted">Students in this class will be eligible for this fee.</small>
    </div>

    <!-- Term Field -->
    <div class="form-group col-sm-6">
        {!! Form::label('term', 'Applicable Term') !!}
        {{-- Options are scoped to one academic year by the controller; changing the
             Academic Year above re-populates this list via the cascade script at the
             bottom of this file. --}}
        <select name="term" id="term" class="form-control select2" data-placeholder="Select Term" required>
            <option value="">Select Term</option>
            @foreach($terms as $termOpt)
                <option value="{{ $termOpt->code }}" {{ old('term', isset($feeStructure) ? $feeStructure->term : null) == $termOpt->code ? 'selected' : '' }}>
                    {{ $termOpt->name }}
                </option>
            @endforeach
        </select>
        <small class="text-muted">Select which term this fee applies to. You can create a different fee structure for each term of the same year.</small>
    </div>

    <!-- Category Id Field -->
    <div class="form-group col-sm-6">
        {!! Form::label('category_id', 'Fee Category') !!}
        {!! Form::select('category_id', $category, null, ['class' => 'form-control select2', 'placeholder' => 'Select Fee Category', 'required']) !!}
        <small class="text-muted">E.g. Tuition, Transport, Lab Fee.</small>
    </div>

    <!-- Amount Field -->
    <div class="form-group col-sm-6">
        {!! Form::label('amount', 'Base Amount') !!}
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text">KES</span>
            </div>
            {!! Form::number('amount', null, ['class' => 'form-control', 'required', 'step' => '0.01', 'placeholder' => '0.00']) !!}
        </div>
    </div>

    <!-- Payment Frequency Field -->
    <div class="form-group col-sm-6">
        {!! Form::label('payment_frequency', 'Payment Frequency') !!}
        {!! Form::select('payment_frequency', ['one-time' => 'One-time', 'termly' => 'Termly', 'monthly' => 'Monthly', 'custom' => 'Custom'], null, ['class' => 'form-control', 'required']) !!}
    </div>

    <!-- Due Date Field -->
    <div class="form-group col-sm-6">
        {!! Form::label('due_date', 'Payment Due Date') !!}
        {!! Form::date('due_date', null, ['class' => 'form-control', 'id' => 'due_date', 'required']) !!}
    </div>

    <!-- Status Field -->
    <div class="form-group col-sm-6">
        {!! Form::label('status', 'Status') !!}
        {!! Form::select('status', ['active' => 'Active', 'draft' => 'Draft', 'inactive' => 'Inactive', 'archived' => 'Archived'], 'active', ['class' => 'form-control', 'required']) !!}
    </div>

    @if(!isset($feeStructure))
    <!-- Auto Assign Field (Only on Create) -->
    <div class="form-group col-sm-12">
        <div class="custom-control custom-checkbox mt-2">
            <input type="checkbox" name="auto_assign" value="1" class="custom-control-input" id="auto_assign" {{ old('auto_assign', '1') == '1' ? 'checked' : '' }}>
            <label class="custom-control-label font-weight-bold text-primary" for="auto_assign">
                Automatically assign this fee to ALL currently enrolled students in the selected class
            </label>
        </div>
        <p class="text-muted small ml-4">If checked, the system will immediately create student fee records for all students currently enrolled in the selected class.</p>
    </div>
    @endif
</div>

@push('page_scripts')
<script>
    (function () {
        const yearSelect = document.getElementById('academic_year_id');
        const termSelect = document.getElementById('term');
        if (!yearSelect || !termSelect) {
            return;
        }

        const termsUrl = @json(route('fee-structures.terms-by-year', ['academicYearId' => 'ACADEMIC_YEAR_ID']));

        // Rebuild the Term options for the given academic year. Term codes are only
        // unique within a year, so the list must be refetched whenever the year
        // changes rather than rendering every year at once.
        function loadTerms(academicYearId) {
            if (!academicYearId) {
                return;
            }

            fetch(termsUrl.replace('ACADEMIC_YEAR_ID', encodeURIComponent(academicYearId)), {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            })
                .then(function (response) {
                    return response.ok ? response.json() : [];
                })
                .then(function (terms) {
                    // Built via DOM nodes so term names are never parsed as HTML.
                    termSelect.textContent = '';

                    var placeholder = document.createElement('option');
                    placeholder.value = '';
                    placeholder.textContent = 'Select Term';
                    termSelect.appendChild(placeholder);

                    if (!terms.length) {
                        placeholder.textContent = 'No terms defined for this academic year';
                    }

                    terms.forEach(function (term) {
                        var option = document.createElement('option');
                        option.value = term.code;
                        option.textContent = term.name;
                        termSelect.appendChild(option);
                    });

                    refreshSelect2();
                })
                .catch(function () {
                    // Leave the previously rendered options in place on a failed fetch.
                });
        }

        // Rebuilds the select2 widget so it reflects the options we just swapped in.
        function refreshSelect2() {
            if (typeof window.jQuery === 'undefined' || typeof window.jQuery.fn.select2 === 'undefined') {
                return;
            }

            var $ = window.jQuery;
            if ($.data(termSelect, 'select2')) {
                $(termSelect).select2('destroy');
            }
            $(termSelect).select2({ theme: 'bootstrap-5' });
        }

        // Initialise every select2 on the form, then wire up the cascade.
        function initFeeForm() {
            if (typeof window.jQuery === 'undefined' || typeof window.jQuery.fn.select2 === 'undefined') {
                setTimeout(initFeeForm, 50);
                return;
            }

            window.jQuery('.select2').select2({ theme: 'bootstrap-5' });
            yearSelect.addEventListener('change', function () {
                loadTerms(yearSelect.value);
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(initFeeForm, 100);
        });
    })();
</script>
@endpush
