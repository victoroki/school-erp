<!-- User Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('user_id', 'User:') !!}
    {!! Form::select('user_id', $users ?? [], old('user_id'), ['class' => 'form-control', 'placeholder' => 'Select User']) !!}
    @error('user_id')
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>

<!-- Employee Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('employee_id', 'Employee Id:') !!}
    {!! Form::text('employee_id', old('employee_id'), ['class' => 'form-control', 'required', 'maxlength' => 20]) !!}
    @error('employee_id')
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>

<!-- First Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('first_name', 'First Name:') !!}
    {!! Form::text('first_name', old('first_name'), ['class' => 'form-control', 'required', 'maxlength' => 50]) !!}
    @error('first_name')
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>

<!-- Middle Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('middle_name', 'Middle Name:') !!}
    {!! Form::text('middle_name', old('middle_name'), ['class' => 'form-control', 'maxlength' => 50]) !!}
    @error('middle_name')
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>

<!-- Last Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('last_name', 'Last Name:') !!}
    {!! Form::text('last_name', old('last_name'), ['class' => 'form-control', 'required', 'maxlength' => 50]) !!}
    @error('last_name')
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>

<!-- Date Of Birth Field -->
<div class="form-group col-sm-6">
    {!! Form::label('date_of_birth', 'Date Of Birth:') !!}
    {!! Form::date('date_of_birth', old('date_of_birth'), ['class' => 'form-control', 'id' => 'date_of_birth',]) !!}
    @error('date_of_birth')
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>

<!-- Gender Field -->
<div class="form-group col-sm-6">
    {!! Form::label('gender', 'Gender:') !!}
    {!! Form::select('gender', [
        '' => 'Select Gender',
        'male' => 'Male',
        'female' => 'Female',
        'other' => 'Other'
    ], old('gender'), ['class' => 'form-control', 'required']) !!}
    @error('gender')
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>

<!-- Joining Date Field -->
<div class="form-group col-sm-6">
    {!! Form::label('joining_date', 'Joining Date:') !!}
    {!! Form::date('joining_date', old('joining_date'), ['class' => 'form-control', 'id' => 'joining_date',]) !!}
    @error('joining_date')
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>

<!-- Department Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('department_id', 'Department:') !!}
    {!! Form::select('department_id', $departments ?? [], old('department_id'), ['class' => 'form-control', 'placeholder' => 'Select Department']) !!}
    @error('department_id')
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>

<!-- Designation Field -->
<div class="form-group col-sm-6">
    {!! Form::label('designation', 'Designation:') !!}
    {!! Form::text('designation', old('designation'), ['class' => 'form-control', 'required', 'maxlength' => 100]) !!}
    @error('designation')
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>

<!-- Qualification Field -->
<div class="form-group col-sm-6">
    {!! Form::label('qualification', 'Qualification:') !!}
    {!! Form::text('qualification', old('qualification'), ['class' => 'form-control', 'maxlength' => 255]) !!}
    @error('qualification')
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>

<!-- Experience Field -->
<div class="form-group col-sm-6">
    {!! Form::label('experience', 'Experience (Years):') !!}
    {!! Form::number('experience', old('experience'), ['class' => 'form-control', 'min' => 0, 'max' => 50, 'step' => '0.5']) !!}
    @error('experience')
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>

<!-- Email Field -->
<div class="form-group col-sm-6">
    {!! Form::label('email', 'Email:') !!}
    {!! Form::email('email', old('email'), ['class' => 'form-control', 'required', 'maxlength' => 100]) !!}
    @error('email')
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>

<!-- Phone Field -->
<div class="form-group col-sm-6">
    {!! Form::label('phone', 'Phone:') !!}
    {!! Form::tel('phone', old('phone'), ['class' => 'form-control', 'required', 'maxlength' => 20]) !!}
    @error('phone')
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>

<!-- Address Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('address', 'Address:') !!}
    {!! Form::textarea('address', old('address'), ['class' => 'form-control', 'required', 'rows' => 3]) !!}
    @error('address')
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>

<!-- City Field -->
<div class="form-group col-sm-6">
    {!! Form::label('city', 'City:') !!}
    {!! Form::text('city', old('city'), ['class' => 'form-control', 'required', 'maxlength' => 50]) !!}
    @error('city')
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>

<!-- Country Field -->
<div class="form-group col-sm-6">
    {!! Form::label('country', 'Country:') !!}
    {!! Form::text('country', old('country'), ['class' => 'form-control', 'required', 'maxlength' => 50]) !!}
    @error('country')
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>

<!-- Photo Upload Field -->
<div class="form-group col-sm-6">
    {!! Form::label('photo', 'Photo:') !!}
    {!! Form::file('photo', ['class' => 'form-control', 'accept' => 'image/*']) !!}
    @if(isset($staff) && $staff->photo_url)
        <small class="form-text text-muted">
            Current: <a href="{{ $staff->photo_url }}" target="_blank">View Photo</a>
        </small>
    @endif
    @error('photo')
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>

<!-- Is Teacher Field -->
<div class="form-group col-sm-6">
    {!! Form::label('is_teacher', 'Is Teacher:') !!}
    {!! Form::select('is_teacher', [
        '0' => 'No',
        '1' => 'Yes'
    ], old('is_teacher', '0'), ['class' => 'form-control', 'required']) !!}
    @error('is_teacher')
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>

<!-- Staff Type Field -->
<div class="form-group col-sm-6">
    {!! Form::label('staff_type', 'Staff Type:') !!}
    {!! Form::select('staff_type', [
        '' => 'Select Staff Type',
        'teaching' => 'teaching',
        'non-teaching' => 'non-teaching',
        'administration' => 'administration'
    ], old('staff_type'), ['class' => 'form-control', 'required']) !!}
    @error('staff_type')
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>

<!-- Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('status', 'Status:') !!}
    {!! Form::select('status', [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'suspended' => 'Suspended'
    ], old('status', 'active'), ['class' => 'form-control']) !!}
    @error('status')
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>

