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
<div class="form-group col-sm-12">
    {!! Form::label('applicable_fee_categories', 'Applicable Fee Categories:', ['class' => 'form-label fw-bold small text-uppercase text-muted mb-1']) !!}
    {!! Form::select('applicable_fee_categories[]', $feeCategories ?? [], null, ['class' => 'form-control select2 rounded-3', 'multiple' => 'multiple', 'placeholder' => 'Select Categories']) !!}
</div>

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
