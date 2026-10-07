<!-- Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('name', 'Name:', ['class' => 'form-label fw-bold small text-uppercase text-muted mb-1']) !!}
    {!! Form::text('name', null, ['class' => 'form-control rounded-3', 'required', 'maxlength' => 255, 'placeholder' => 'e.g., Sibling Discount']) !!}
</div>

<!-- Code Field -->
<div class="form-group col-sm-6">
    {!! Form::label('code', 'Code:', ['class' => 'form-label fw-bold small text-uppercase text-muted mb-1']) !!}
    <div class="input-group">
        {!! Form::text('code', null, ['class' => 'form-control rounded-start-3', 'maxlength' => 50, 'id' => 'code-field', 'placeholder' => 'Auto-generated']) !!}
        <button type="button" class="btn btn-outline-secondary rounded-end-3" id="generate-code-btn" title="Generate Code">
            <i class="fas fa-bolt"></i>
        </button>
    </div>
</div>

<!-- Type Field -->
<div class="form-group col-sm-6">
    {!! Form::label('type', 'Type:', ['class' => 'form-label fw-bold small text-uppercase text-muted mb-1']) !!}
    {!! Form::select('type', ['percentage' => 'Percentage', 'fixed' => 'Fixed Amount', 'full_waiver' => 'Full Waiver'], null, ['class' => 'form-control select2 rounded-3', 'required']) !!}
</div>

<!-- Value Field -->
<div class="form-group col-sm-6">
    {!! Form::label('value', 'Value:', ['class' => 'form-label fw-bold small text-uppercase text-muted mb-1']) !!}
    {!! Form::number('value', null, ['class' => 'form-control rounded-3', 'step' => '0.01', 'placeholder' => 'Enter value']) !!}
</div>

<!-- Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('status', 'Status:', ['class' => 'form-label fw-bold small text-uppercase text-muted mb-1']) !!}
    {!! Form::select('status', ['active' => 'Active', 'inactive' => 'Inactive'], null, ['class' => 'form-control select2 rounded-3', 'required']) !!}
</div>

<!-- Applies To Field -->
<div class="form-group col-sm-6">
    {!! Form::label('applies_to', 'Applies To:', ['class' => 'form-label fw-bold small text-uppercase text-muted mb-1']) !!}
    {!! Form::select('applies_to', ['all_fees' => 'All Fees', 'specific_categories' => 'Specific Categories', 'exclude_categories' => 'Exclude Categories'], 'all_fees', ['class' => 'form-control select2 rounded-3', 'required']) !!}
</div>

<!-- Applicable Fee Categories Field -->
{{--
    Ticked as a grid of checkboxes rather than a multi-select.

    The multi-select was rendered with a null selected value, so the edit form
    always showed it empty, and because an unticked multi-select drops the key
    from the payload entirely a stored selection could never be cleared. Boxes
    show their own state, and the marker input below is what lets the
    controller tell "ticked none" apart from "this request never had the field".

    The marker is also why the default is read conditionally: after a failed
    validation the session does carry it, so falling back to the model's stored
    categories there would tick boxes the user had deliberately unticked.
--}}
@php
    $categoriesWereSubmitted = ! is_null(old('applicable_fee_categories_submitted'));
    $selectedCategoryIds = collect(
        old(
            'applicable_fee_categories',
            $categoriesWereSubmitted ? [] : ($discountScheme->applicable_fee_categories ?? [])
        )
    )->map(fn ($id) => (int) $id)->all();
@endphp

<div class="form-group col-sm-12" id="applicable-fee-categories">
    <input type="hidden" name="applicable_fee_categories_submitted" value="1">

    <div class="ds-cat-head">
        <span class="form-label mb-0 fw-bold small text-uppercase text-muted" id="applicable-fee-categories-label">Applicable Fee Categories:</span>
        <span class="ds-cat-count" data-cat-count aria-live="polite"></span>
        <button type="button" class="ds-cat-clear" data-cat-clear hidden>Clear</button>
    </div>

    <div class="ds-cat-grid" role="group" aria-labelledby="applicable-fee-categories-label">
        @forelse($feeCategories as $categoryId => $categoryName)
            <label class="ds-cat-card">
                {!! Form::checkbox('applicable_fee_categories[]', $categoryId, in_array((int) $categoryId, $selectedCategoryIds, true), ['class' => 'form-check-input', 'id' => 'ds-cat-' . $categoryId]) !!}
                <span class="ds-cat-name">{{ $categoryName }}</span>
            </label>
        @empty
            <p class="text-muted small mb-0">No fee categories exist yet, so this scheme will apply to every fee.</p>
        @endforelse
    </div>

    <small class="form-text text-muted">Leave all unticked to apply to all fee categories. Tick categories to limit where this scheme can be used.</small>
</div>

{{--
    Styles are literal values rather than the create page's --indigo/--border
    custom properties: this partial is also rendered by edit.blade.php, which
    never defines them, so variables here would fall back to nothing there.
--}}
<style>
    .ds-cat-head {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .ds-cat-count {
        font-size: 0.7rem;
        font-weight: 700;
        color: #64748b;
    }

    .ds-cat-clear {
        margin-left: auto;
        padding: 0;
        border: 0;
        background: none;
        font-size: 0.7rem;
        font-weight: 700;
        color: #4f46e5;
        text-decoration: underline;
        cursor: pointer;
    }

    .ds-cat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
        gap: 0.5rem;
    }

    .ds-cat-card {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        margin: 0;
        padding: 0.625rem 0.75rem;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        cursor: pointer;
        transition: border-color 150ms cubic-bezier(0.23, 1, 0.32, 1),
                    background-color 150ms cubic-bezier(0.23, 1, 0.32, 1);
    }

    .ds-cat-card:hover {
        border-color: #c7d2fe;
        background: #f8fafc;
    }

    /* Set by the script below rather than :has(), so a browser without it
       still shows which categories are ticked. */
    .ds-cat-card.is-checked {
        border-color: #4f46e5;
        background: #eef2ff;
    }

    .ds-cat-card .form-check-input {
        flex: none;
        margin: 0;
    }

    .ds-cat-name {
        font-size: 0.8125rem;
        font-weight: 600;
        line-height: 1.3;
        color: #1e293b;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var group = document.getElementById('applicable-fee-categories');

        if (!group) {
            return;
        }

        var count = group.querySelector('[data-cat-count]');
        var clear = group.querySelector('[data-cat-clear]');

        function boxes() {
            return group.querySelectorAll('input[type="checkbox"]');
        }

        function sync() {
            var ticked = group.querySelectorAll('input[type="checkbox"]:checked');

            boxes().forEach(function (box) {
                box.closest('.ds-cat-card').classList.toggle('is-checked', box.checked);
            });

            if (count) {
                count.textContent = ticked.length === 0 ? 'None ticked' : ticked.length + ' ticked';
            }

            if (clear) {
                clear.hidden = ticked.length === 0;
            }
        }

        group.addEventListener('change', sync);

        if (clear) {
            clear.addEventListener('click', function () {
                boxes().forEach(function (box) {
                    box.checked = false;
                });

                sync();
            });
        }

        sync();
    });
</script>

 <!-- Eligibility Criteria Field -->
<div class="form-group col-sm-6">
    {!! Form::label('eligibility_criteria', 'Eligibility Criteria:', ['class' => 'form-label fw-bold small text-uppercase text-muted mb-1']) !!}
    {!! Form::select('eligibility_criteria', ['staff_child' => 'Staff Child', 'sibling' => 'Sibling', 'merit' => 'Merit Based', 'financial_aid' => 'Financial Aid', 'custom' => 'Custom'], 'custom', ['class' => 'form-control select2 rounded-3', 'required']) !!}
</div>

{{--
    These five fields used to live only in create.blade.php, so a scheme could be
    created with a validity window, approval requirement and auto-apply setting
    that the edit screen never showed and could therefore never change. Keeping
    them in this shared partial means the two forms cannot drift apart again.
--}}

<!-- Academic Year Field -->
<div class="form-group col-sm-6">
    {!! Form::label('academic_year_id', 'Academic Year:', ['class' => 'form-label fw-bold small text-uppercase text-muted mb-1']) !!}
    {!! Form::select('academic_year_id', $academicYears ?? [], null, ['class' => 'form-control select2 rounded-3', 'placeholder' => 'Select Year']) !!}
</div>

<!-- Valid From Field -->
<div class="form-group col-sm-6">
    {!! Form::label('valid_from', 'Valid From:', ['class' => 'form-label fw-bold small text-uppercase text-muted mb-1']) !!}
    {!! Form::date('valid_from', null, ['class' => 'form-control rounded-3']) !!}
</div>

<!-- Valid To Field -->
<div class="form-group col-sm-6">
    {!! Form::label('valid_to', 'Valid To:', ['class' => 'form-label fw-bold small text-uppercase text-muted mb-1']) !!}
    {!! Form::date('valid_to', null, ['class' => 'form-control rounded-3']) !!}
</div>

<!-- Requires Approval Field -->
<div class="form-group col-sm-6">
    <div class="form-check">
        {!! Form::checkbox('requires_approval', 1, null, ['class' => 'form-check-input']) !!}
        {!! Form::label('requires_approval', 'Requires Approval', ['class' => 'form-check-label fw-600']) !!}
    </div>
</div>

<!-- Auto Apply Field -->
<div class="form-group col-sm-6">
    <div class="form-check">
        {!! Form::checkbox('auto_apply', 1, null, ['class' => 'form-check-input']) !!}
        {!! Form::label('auto_apply', 'Auto Apply', ['class' => 'form-check-label fw-600']) !!}
    </div>
</div>
