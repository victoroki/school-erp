{{-- User Field --}}
<div class="form-group col-sm-6">
    {!! Form::label('user_id', 'Linked User Account:') !!}
    {!! Form::select('user_id', $users ?? [], old('user_id'), ['class' => 'form-control', 'placeholder' => '— Select User —']) !!}
    @error('user_id')<span class="text-danger">{{ $message }}</span>@enderror
</div>

{{-- Employee Number Field --}}
<div class="form-group col-sm-6">
    {!! Form::label('employee_number', 'Employee Number:') !!}
    {!! Form::text('employee_number', old('employee_number'), ['class' => 'form-control', 'placeholder' => 'e.g. T-1001', 'maxlength' => 20]) !!}
    @error('employee_number')<span class="text-danger">{{ $message }}</span>@enderror
</div>

{{-- First Name Field --}}
<div class="form-group col-sm-6">
    {!! Form::label('first_name', 'First Name:') !!}
    {!! Form::text('first_name', old('first_name'), ['class' => 'form-control', 'required', 'maxlength' => 50]) !!}
    @error('first_name')<span class="text-danger">{{ $message }}</span>@enderror
</div>

{{-- Middle Name Field --}}
<div class="form-group col-sm-6">
    {!! Form::label('middle_name', 'Middle Name:') !!}
    {!! Form::text('middle_name', old('middle_name'), ['class' => 'form-control', 'maxlength' => 50]) !!}
    @error('middle_name')<span class="text-danger">{{ $message }}</span>@enderror
</div>

{{-- Last Name Field --}}
<div class="form-group col-sm-6">
    {!! Form::label('last_name', 'Last Name:') !!}
    {!! Form::text('last_name', old('last_name'), ['class' => 'form-control', 'required', 'maxlength' => 50]) !!}
    @error('last_name')<span class="text-danger">{{ $message }}</span>@enderror
</div>

{{-- Date Of Birth Field --}}
<div class="form-group col-sm-6">
    {!! Form::label('date_of_birth', 'Date Of Birth:') !!}
    {!! Form::date('date_of_birth', old('date_of_birth'), ['class' => 'form-control']) !!}
    @error('date_of_birth')<span class="text-danger">{{ $message }}</span>@enderror
</div>

{{-- Gender Field --}}
<div class="form-group col-sm-6">
    {!! Form::label('gender', 'Gender:') !!}
    {!! Form::select('gender', ['' => '— Select Gender —', 'male' => 'Male', 'female' => 'Female', 'other' => 'Other'], old('gender'), ['class' => 'form-control', 'required']) !!}
    @error('gender')<span class="text-danger">{{ $message }}</span>@enderror
</div>

{{-- Date of Joining Field --}}
<div class="form-group col-sm-6">
    {!! Form::label('date_of_joining', 'Date Of Joining:') !!}
    {!! Form::date('date_of_joining', old('date_of_joining'), ['class' => 'form-control']) !!}
    @error('date_of_joining')<span class="text-danger">{{ $message }}</span>@enderror
</div>

{{-- Department Field --}}
<div class="form-group col-sm-6">
    {!! Form::label('department_id', 'Department:') !!}
    {!! Form::select('department_id', $departments ?? [], old('department_id'), ['class' => 'form-control', 'placeholder' => '— Select Department —']) !!}
    @error('department_id')<span class="text-danger">{{ $message }}</span>@enderror
</div>

{{-- Job Position Field --}}
<div class="form-group col-sm-6">
    {!! Form::label('job_position_id', 'Job Position:') !!}
    {!! Form::select('job_position_id', $jobPositions ?? [], old('job_position_id'), ['class' => 'form-control', 'placeholder' => '— Select Position —']) !!}
    @error('job_position_id')<span class="text-danger">{{ $message }}</span>@enderror
</div>

{{-- Basic Salary Field --}}
<div class="form-group col-sm-6">
    {!! Form::label('basic_salary', 'Basic Salary:') !!}
    {!! Form::number('basic_salary', old('basic_salary'), ['class' => 'form-control', 'min' => 0, 'step' => '0.01', 'placeholder' => '0.00']) !!}
    @error('basic_salary')<span class="text-danger">{{ $message }}</span>@enderror
</div>

{{-- Qualification Field --}}
<div class="form-group col-sm-6">
    {!! Form::label('qualification', 'Qualification:') !!}
    {!! Form::text('qualification', old('qualification'), ['class' => 'form-control', 'maxlength' => 255]) !!}
    @error('qualification')<span class="text-danger">{{ $message }}</span>@enderror
</div>

{{-- Experience Field --}}
<div class="form-group col-sm-6">
    {!! Form::label('experience', 'Experience (Years):') !!}
    {!! Form::number('experience', old('experience'), ['class' => 'form-control', 'min' => 0, 'max' => 50, 'step' => '0.5']) !!}
    @error('experience')<span class="text-danger">{{ $message }}</span>@enderror
</div>

{{-- Work Email Field --}}
<div class="form-group col-sm-6">
    {!! Form::label('work_email', 'Work Email:') !!}
    {!! Form::email('work_email', old('work_email'), ['class' => 'form-control', 'maxlength' => 100]) !!}
    @error('work_email')<span class="text-danger">{{ $message }}</span>@enderror
</div>

{{-- Primary Phone Field --}}
<div class="form-group col-sm-6">
    {!! Form::label('phone_primary', 'Phone (Primary):') !!}
    {!! Form::tel('phone_primary', old('phone_primary'), ['class' => 'form-control', 'maxlength' => 20]) !!}
    @error('phone_primary')<span class="text-danger">{{ $message }}</span>@enderror
</div>

{{-- Current Address Field --}}
<div class="form-group col-sm-12">
    {!! Form::label('current_address', 'Current Address:') !!}
    {!! Form::textarea('current_address', old('current_address'), ['class' => 'form-control', 'rows' => 3]) !!}
    @error('current_address')<span class="text-danger">{{ $message }}</span>@enderror
</div>

{{-- City Field --}}
<div class="form-group col-sm-6">
    {!! Form::label('city', 'City:') !!}
    {!! Form::text('city', old('city'), ['class' => 'form-control', 'maxlength' => 50]) !!}
    @error('city')<span class="text-danger">{{ $message }}</span>@enderror
</div>

{{-- Country Field --}}
<div class="form-group col-sm-6">
    {!! Form::label('country', 'Country:') !!}
    {!! Form::text('country', old('country'), ['class' => 'form-control', 'maxlength' => 50]) !!}
    @error('country')<span class="text-danger">{{ $message }}</span>@enderror
</div>

{{-- Photo Upload Field --}}
<div class="form-group col-sm-6">
    {!! Form::label('photo', 'Profile Photo:') !!}
    {!! Form::file('photo', ['class' => 'form-control', 'accept' => 'image/*']) !!}
    @if(isset($staff) && $staff->photo_url)
        <small class="form-text text-muted">Current: <a href="{{ $staff->photo_url }}" target="_blank">View Photo</a></small>
    @endif
    @error('photo')<span class="text-danger">{{ $message }}</span>@enderror
</div>

{{-- Staff Type Field --}}
<div class="form-group col-sm-6">
    {!! Form::label('staff_type', 'Staff Type:') !!}
    {!! Form::select('staff_type', ['' => '— Select Staff Type —', 'teaching' => 'Teaching', 'non-teaching' => 'Non-Teaching', 'administration' => 'Administration'], old('staff_type'), ['class' => 'form-control', 'required']) !!}
    @error('staff_type')<span class="text-danger">{{ $message }}</span>@enderror
</div>

{{-- Employment Status Field --}}
<div class="form-group col-sm-6">
    {!! Form::label('employment_status', 'Employment Status:') !!}
    {!! Form::select('employment_status', [
        'active'     => 'Active',
        'on_leave'   => 'On Leave',
        'suspended'  => 'Suspended',
        'terminated' => 'Terminated',
        'resigned'   => 'Resigned',
        'retired'    => 'Retired',
    ], old('employment_status', 'active'), ['class' => 'form-control']) !!}
    @error('employment_status')<span class="text-danger">{{ $message }}</span>@enderror
</div>

{{-- Employment Type Field --}}
<div class="form-group col-sm-6">
    {!! Form::label('employment_type', 'Employment Type:') !!}
    {!! Form::select('employment_type', ['' => '— Select Type —', 'full_time' => 'Full Time', 'part_time' => 'Part Time', 'contract' => 'Contract', 'casual' => 'Casual', 'intern' => 'Intern'], old('employment_type'), ['class' => 'form-control']) !!}
    @error('employment_type')<span class="text-danger">{{ $message }}</span>@enderror
</div>
