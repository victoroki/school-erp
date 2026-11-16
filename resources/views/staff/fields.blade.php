{{-- Shared by staff.create and staff.edit. Section headings are col-12 so they sit
     correctly inside either page's .row. Styles are self-contained with literal
     values because this partial is rendered by two pages. --}}

@php
    $err = function ($field) use ($errors) {
        return $errors->first($field);
    };
@endphp

{{-- IDENTITY --}}
<div class="col-12">
    <h4 class="form-section-title"><i class="fas fa-id-card"></i> Identity</h4>
</div>

<div class="form-group col-sm-6">
    {!! Form::label('employee_number', 'Employee Number', ['class' => 'form-label-soft']) !!}
    {!! Form::text('employee_number', old('employee_number'), ['class' => 'form-control' . ($err('employee_number') ? ' is-invalid' : ''), 'placeholder' => 'e.g. T-1001', 'maxlength' => 20, 'autocomplete' => 'off', 'spellcheck' => 'false']) !!}
    @if($err('employee_number'))<span class="form-field-error"><i class="fas fa-exclamation-circle"></i>{{ $err('employee_number') }}</span>@endif
</div>

<div class="form-group col-sm-6">
    {!! Form::label('first_name', 'First Name', ['class' => 'form-label-soft is-required']) !!}
    {!! Form::text('first_name', old('first_name'), ['class' => 'form-control' . ($err('first_name') ? ' is-invalid' : ''), 'required', 'maxlength' => 50, 'placeholder' => 'Given name', 'autocomplete' => 'given-name']) !!}
    @if($err('first_name'))<span class="form-field-error"><i class="fas fa-exclamation-circle"></i>{{ $err('first_name') }}</span>@endif
</div>

<div class="form-group col-sm-6">
    {!! Form::label('middle_name', 'Middle Name', ['class' => 'form-label-soft']) !!}
    {!! Form::text('middle_name', old('middle_name'), ['class' => 'form-control' . ($err('middle_name') ? ' is-invalid' : ''), 'maxlength' => 50, 'autocomplete' => 'additional-name']) !!}
    @if($err('middle_name'))<span class="form-field-error"><i class="fas fa-exclamation-circle"></i>{{ $err('middle_name') }}</span>@endif
</div>

<div class="form-group col-sm-6">
    {!! Form::label('last_name', 'Last Name', ['class' => 'form-label-soft is-required']) !!}
    {!! Form::text('last_name', old('last_name'), ['class' => 'form-control' . ($err('last_name') ? ' is-invalid' : ''), 'required', 'maxlength' => 50, 'placeholder' => 'Family name', 'autocomplete' => 'family-name']) !!}
    @if($err('last_name'))<span class="form-field-error"><i class="fas fa-exclamation-circle"></i>{{ $err('last_name') }}</span>@endif
</div>

<div class="form-group col-sm-6">
    {!! Form::label('date_of_birth', 'Date of Birth', ['class' => 'form-label-soft is-required']) !!}
    {!! Form::date('date_of_birth', old('date_of_birth'), ['class' => 'form-control' . ($err('date_of_birth') ? ' is-invalid' : ''), 'required', 'max' => now()->subYears(18)->format('Y-m-d')]) !!}
    @if($err('date_of_birth'))<span class="form-field-error"><i class="fas fa-exclamation-circle"></i>{{ $err('date_of_birth') }}</span>@endif
</div>

<div class="form-group col-sm-6">
    {!! Form::label('gender', 'Gender', ['class' => 'form-label-soft is-required']) !!}
    {!! Form::select('gender', ['' => 'Select gender', 'male' => 'Male', 'female' => 'Female', 'other' => 'Other'], old('gender'), ['class' => 'form-control' . ($err('gender') ? ' is-invalid' : ''), 'required']) !!}
    @if($err('gender'))<span class="form-field-error"><i class="fas fa-exclamation-circle"></i>{{ $err('gender') }}</span>@endif
</div>

<div class="form-group col-sm-6">
    {!! Form::label('staff_type', 'Staff Type', ['class' => 'form-label-soft is-required']) !!}
    {!! Form::select('staff_type', ['' => 'Select staff type', 'teaching' => 'Teaching', 'non-teaching' => 'Non-Teaching', 'administration' => 'Administration'], old('staff_type', isset($staff) ? $staff->staff_type : 'teaching'), ['class' => 'form-control' . ($err('staff_type') ? ' is-invalid' : ''), 'required']) !!}
    @if($err('staff_type'))<span class="form-field-error"><i class="fas fa-exclamation-circle"></i>{{ $err('staff_type') }}</span>@endif
</div>

{{-- EMPLOYMENT --}}
<div class="col-12">
    <h4 class="form-section-title"><i class="fas fa-briefcase"></i> Employment</h4>
</div>

<div class="form-group col-sm-6">
    {!! Form::label('department_id', 'Department', ['class' => 'form-label-soft is-required']) !!}
    {!! Form::select('department_id', $departments ?? [], old('department_id'), ['class' => 'form-control' . ($err('department_id') ? ' is-invalid' : ''), 'required', 'placeholder' => 'Select department']) !!}
    @if($err('department_id'))<span class="form-field-error"><i class="fas fa-exclamation-circle"></i>{{ $err('department_id') }}</span>@endif
</div>

<div class="form-group col-sm-6">
    {!! Form::label('job_position_id', 'Job Position', ['class' => 'form-label-soft']) !!}
    {!! Form::select('job_position_id', $jobPositions ?? [], old('job_position_id'), ['class' => 'form-control' . ($err('job_position_id') ? ' is-invalid' : ''), 'placeholder' => 'Select position']) !!}
    @if($err('job_position_id'))<span class="form-field-error"><i class="fas fa-exclamation-circle"></i>{{ $err('job_position_id') }}</span>@endif
</div>

<div class="form-group col-sm-6">
    {!! Form::label('date_of_joining', 'Date of Joining', ['class' => 'form-label-soft is-required']) !!}
    {!! Form::date('date_of_joining', old('date_of_joining'), ['class' => 'form-control' . ($err('date_of_joining') ? ' is-invalid' : ''), 'required']) !!}
    @if($err('date_of_joining'))<span class="form-field-error"><i class="fas fa-exclamation-circle"></i>{{ $err('date_of_joining') }}</span>@endif
</div>

<div class="form-group col-sm-6">
    {!! Form::label('employment_type', 'Employment Type', ['class' => 'form-label-soft is-required']) !!}
    {!! Form::select('employment_type', ['' => 'Select type', 'full_time' => 'Full Time', 'part_time' => 'Part Time', 'contract' => 'Contract', 'casual' => 'Casual', 'intern' => 'Intern'], old('employment_type', isset($staff) ? $staff->employment_type : 'full_time'), ['class' => 'form-control' . ($err('employment_type') ? ' is-invalid' : ''), 'required']) !!}
    @if($err('employment_type'))<span class="form-field-error"><i class="fas fa-exclamation-circle"></i>{{ $err('employment_type') }}</span>@endif
</div>

<div class="form-group col-sm-6">
    {!! Form::label('employment_status', 'Employment Status', ['class' => 'form-label-soft is-required']) !!}
    {!! Form::select('employment_status', [
        'active'     => 'Active',
        'on_leave'   => 'On Leave',
        'suspended'  => 'Suspended',
        'terminated' => 'Terminated',
        'resigned'   => 'Resigned',
        'retired'    => 'Retired',
    ], old('employment_status', 'active'), ['class' => 'form-control' . ($err('employment_status') ? ' is-invalid' : ''), 'required']) !!}
    @if($err('employment_status'))<span class="form-field-error"><i class="fas fa-exclamation-circle"></i>{{ $err('employment_status') }}</span>@endif
</div>

<div class="form-group col-sm-6">
    {!! Form::label('basic_salary', 'Basic Salary', ['class' => 'form-label-soft']) !!}
    {!! Form::number('basic_salary', old('basic_salary'), ['class' => 'form-control' . ($err('basic_salary') ? ' is-invalid' : ''), 'min' => 0, 'step' => '0.01', 'placeholder' => '0.00', 'inputmode' => 'decimal']) !!}
    @if($err('basic_salary'))<span class="form-field-error"><i class="fas fa-exclamation-circle"></i>{{ $err('basic_salary') }}</span>@endif
</div>

<div class="form-group col-sm-6">
    {!! Form::label('qualification', 'Qualification', ['class' => 'form-label-soft']) !!}
    {!! Form::text('qualification', old('qualification'), ['class' => 'form-control' . ($err('qualification') ? ' is-invalid' : ''), 'maxlength' => 255, 'placeholder' => 'Highest qualification held']) !!}
    @if($err('qualification'))<span class="form-field-error"><i class="fas fa-exclamation-circle"></i>{{ $err('qualification') }}</span>@endif
</div>

<div class="form-group col-sm-6">
    {!! Form::label('experience', 'Experience (Years)', ['class' => 'form-label-soft']) !!}
    {!! Form::number('experience', old('experience'), ['class' => 'form-control' . ($err('experience') ? ' is-invalid' : ''), 'min' => 0, 'max' => 50, 'step' => '0.5', 'inputmode' => 'decimal']) !!}
    @if($err('experience'))<span class="form-field-error"><i class="fas fa-exclamation-circle"></i>{{ $err('experience') }}</span>@endif
</div>

{{-- CONTACT --}}
<div class="col-12">
    <h4 class="form-section-title"><i class="fas fa-address-card"></i> Contact</h4>
</div>

<div class="form-group col-sm-6">
    {!! Form::label('work_email', 'Work Email', ['class' => 'form-label-soft is-required']) !!}
    {!! Form::email('work_email', old('work_email'), ['class' => 'form-control' . ($err('work_email') ? ' is-invalid' : ''), 'required', 'maxlength' => 100, 'placeholder' => 'name@school.org', 'autocomplete' => 'email']) !!}
    @if($err('work_email'))<span class="form-field-error"><i class="fas fa-exclamation-circle"></i>{{ $err('work_email') }}</span>@endif
</div>

<div class="form-group col-sm-6">
    {!! Form::label('phone_primary', 'Phone (Primary)', ['class' => 'form-label-soft is-required']) !!}
    {!! Form::tel('phone_primary', old('phone_primary'), ['class' => 'form-control' . ($err('phone_primary') ? ' is-invalid' : ''), 'required', 'maxlength' => 20, 'placeholder' => '07XX XXX XXX', 'autocomplete' => 'tel']) !!}
    @if($err('phone_primary'))<span class="form-field-error"><i class="fas fa-exclamation-circle"></i>{{ $err('phone_primary') }}</span>@endif
</div>

<div class="form-group col-sm-12">
    {!! Form::label('current_address', 'Current Address', ['class' => 'form-label-soft']) !!}
    {!! Form::textarea('current_address', old('current_address'), ['class' => 'form-control' . ($err('current_address') ? ' is-invalid' : ''), 'rows' => 2, 'autocomplete' => 'street-address']) !!}
    @if($err('current_address'))<span class="form-field-error"><i class="fas fa-exclamation-circle"></i>{{ $err('current_address') }}</span>@endif
</div>

<div class="form-group col-sm-6">
    {!! Form::label('city', 'City', ['class' => 'form-label-soft']) !!}
    {!! Form::text('city', old('city'), ['class' => 'form-control' . ($err('city') ? ' is-invalid' : ''), 'maxlength' => 50, 'autocomplete' => 'address-level2']) !!}
    @if($err('city'))<span class="form-field-error"><i class="fas fa-exclamation-circle"></i>{{ $err('city') }}</span>@endif
</div>

<div class="form-group col-sm-6">
    {!! Form::label('country', 'Country', ['class' => 'form-label-soft']) !!}
    {!! Form::text('country', old('country'), ['class' => 'form-control' . ($err('country') ? ' is-invalid' : ''), 'maxlength' => 50, 'autocomplete' => 'country-name']) !!}
    @if($err('country'))<span class="form-field-error"><i class="fas fa-exclamation-circle"></i>{{ $err('country') }}</span>@endif
</div>

{{-- ACCOUNT & MEDIA --}}
<div class="col-12">
    <h4 class="form-section-title"><i class="fas fa-user-cog"></i> Account &amp; Photo</h4>
</div>

<div class="form-group col-sm-6 staff-nonteacher">
    {!! Form::label('user_id', 'Linked User Account', ['class' => 'form-label-soft']) !!}
    {!! Form::select('user_id', $users ?? [], old('user_id'), ['class' => 'form-control' . ($err('user_id') ? ' is-invalid' : ''), 'placeholder' => 'No portal access']) !!}
    <span class="form-field-hint">Link an existing login, or leave empty until one is created.</span>
    @if($err('user_id'))<span class="form-field-error"><i class="fas fa-exclamation-circle"></i>{{ $err('user_id') }}</span>@endif
</div>

<div class="form-group col-sm-6 staff-teacher">
    {!! Form::label('personal_email', 'Personal Email', ['class' => 'form-label-soft']) !!}
    {!! Form::email('personal_email', old('personal_email'), ['class' => 'form-control' . ($err('personal_email') ? ' is-invalid' : ''), 'maxlength' => 100, 'placeholder' => 'name@personalmail.com', 'autocomplete' => 'email']) !!}
    @if(! isset($staff))
        <span class="form-field-hint">Where the account-setup link is sent. Falls back to the work email.</span>
    @endif
    @if($err('personal_email'))<span class="form-field-error"><i class="fas fa-exclamation-circle"></i>{{ $err('personal_email') }}</span>@endif
</div>

<div class="form-group col-sm-6 staff-teacher">
    {!! Form::label('tsc_number', 'TSC Number', ['class' => 'form-label-soft']) !!}
    {!! Form::text('tsc_number', old('tsc_number'), ['class' => 'form-control' . ($err('tsc_number') ? ' is-invalid' : ''), 'maxlength' => 20, 'placeholder' => 'If applicable', 'autocomplete' => 'off']) !!}
    @if($err('tsc_number'))<span class="form-field-error"><i class="fas fa-exclamation-circle"></i>{{ $err('tsc_number') }}</span>@endif
</div>

@if(! isset($staff))
    <div class="form-group col-sm-6 staff-teacher">
        <label class="form-label-soft">Portal Access</label>
        <div class="form-static-note">
            <i class="fas fa-envelope-open-text"></i>
            <span>A portal login is created and a password-setup link is emailed. The teacher sets their own password.</span>
        </div>
    </div>
@endif

<div class="form-group col-sm-6">
    {!! Form::label('photo', 'Profile Photo', ['class' => 'form-label-soft']) !!}
    {!! Form::file('photo', ['class' => 'form-control form-control-file' . ($err('photo') ? ' is-invalid' : ''), 'accept' => 'image/jpeg,image/png,image/gif']) !!}
    <span class="form-field-hint">JPG, PNG or GIF, up to 2 MB. A square crop fits best.</span>
    @if(isset($staff) && $staff->photo_url)
        <span class="form-field-hint">Current photo: <a href="{{ $staff->photo_url }}" target="_blank" rel="noopener">view</a></span>
    @endif
    @if($err('photo'))<span class="form-field-error"><i class="fas fa-exclamation-circle"></i>{{ $err('photo') }}</span>@endif
</div>

<script>
(function () {
    var typeField = document.getElementById('staff_type');
    if (!typeField) {
        return;
    }

    function sync() {
        var teaching = typeField.value === 'teaching';
        document.querySelectorAll('.staff-teacher').forEach(function (node) {
            node.hidden = !teaching;
            node.querySelectorAll('input, select, textarea').forEach(function (field) {
                field.disabled = !teaching;
            });
        });
        document.querySelectorAll('.staff-nonteacher').forEach(function (node) {
            node.hidden = teaching;
            node.querySelectorAll('input, select, textarea').forEach(function (field) {
                field.disabled = teaching;
            });
        });
    }

    typeField.addEventListener('change', sync);
    sync();
})();
</script>

<style>
.form-static-note {
    display: flex; align-items: flex-start; gap: 0.5rem; padding: 0.625rem 0.75rem;
    background: #eef2ff; border: 1px solid #e0e7ff; border-radius: 10px;
    font-size: 0.75rem; font-weight: 600; color: #3730a3; line-height: 1.5;
}
.form-static-note i { margin-top: 0.125rem; }
[hidden] { display: none !important; }
</style>
.form-section-title {
    font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em;
    color: #0f172a; margin: 0.5rem 0 0.25rem; padding-bottom: 0.5rem;
    border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; gap: 0.5rem;
}
.form-section-title i { color: #4f46e5; opacity: 0.75; }
.form-label-soft {
    font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;
    color: #64748b; margin-bottom: 0.375rem; display: block;
}
.form-label-soft.is-required::after { content: "\00a0*"; color: #f43f5e; }
.form-field-error {
    display: flex; align-items: flex-start; gap: 0.375rem; margin-top: 0.375rem;
    font-size: 0.75rem; font-weight: 600; color: #e11d48;
}
.form-field-error i { margin-top: 0.125rem; }
.form-field-hint { display: block; margin-top: 0.375rem; font-size: 0.75rem; color: #94a3b8; }
</style>
