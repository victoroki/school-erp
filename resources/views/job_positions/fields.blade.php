<!-- Title Field -->
<div class="form-group col-sm-6">
    {!! Form::label('title', 'Role Title') !!}
    {!! Form::text('title', null, ['class' => 'form-control', 'required', 'placeholder' => 'e.g. Senior Software Engineer', 'maxlength' => 100]) !!}
</div>

<!-- Department Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('department_id', 'Department') !!}
    {!! Form::select('department_id', $department, null, ['class' => 'form-control', 'placeholder' => '— Select Department —']) !!}
</div>

<!-- Description Field -->
<div class="form-group col-sm-12">
    {!! Form::label('description', 'Role Description') !!}
    {!! Form::textarea('description', null, ['class' => 'form-control', 'placeholder' => 'Brief overview of the role...', 'rows' => 3]) !!}
</div>

<!-- Responsibilities Field -->
<div class="form-group col-sm-12">
    {!! Form::label('responsibilities', 'Key Responsibilities') !!}
    {!! Form::textarea('responsibilities', null, ['class' => 'form-control', 'placeholder' => '• Main duty 1\n• Main duty 2...', 'rows' => 4]) !!}
</div>

<!-- Qualifications Field -->
<div class="form-group col-sm-12">
    {!! Form::label('qualifications', 'Required Qualifications') !!}
    {!! Form::textarea('qualifications', null, ['class' => 'form-control', 'placeholder' => 'Minimum requirements for this position...', 'rows' => 3]) !!}
</div>

<!-- Is Active Field -->
<div class="form-group col-sm-12 mt-2">
    <div class="dash-checkbox p-3 border rounded-3 bg-light">
        <div class="form-check d-flex align-items-center gap-2">
            {!! Form::hidden('is_active', 0) !!}
            {!! Form::checkbox('is_active', '1', null, ['class' => 'form-check-input ms-0', 'id' => 'is_active_check', 'style' => 'width: 1.25rem; height: 1.25rem;']) !!}
            <div>
                {!! Form::label('is_active', 'Position is Active', ['class' => 'form-check-label mb-0 fw-bold ms-2']) !!}
                <small class="text-muted d-block ms-2 mt-1">Inactive roles will not be selectable during staff onboarding.</small>
            </div>
        </div>
    </div>
</div>

<style>
.dash-checkbox { transition: all 200ms ease; cursor: pointer; border: 1px dashed var(--border) !important; }
.dash-checkbox:has(:checked) { background: var(--indigo-light) !important; border: 1px solid var(--indigo) !important; border-style: solid !important; }
.form-check-input:checked { background-color: var(--indigo); border-color: var(--indigo); }
</style>