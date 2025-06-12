<!-- User Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('user_id', 'User Id:') !!}
    {!! Form::number('user_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Employee Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('employee_id', 'Employee Id:') !!}
    {!! Form::text('employee_id', null, ['class' => 'form-control', 'required', 'maxlength' => 20, 'maxlength' => 20]) !!}
</div>

<!-- First Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('first_name', 'First Name:') !!}
    {!! Form::text('first_name', null, ['class' => 'form-control', 'required', 'maxlength' => 50, 'maxlength' => 50]) !!}
</div>

<!-- Middle Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('middle_name', 'Middle Name:') !!}
    {!! Form::text('middle_name', null, ['class' => 'form-control', 'maxlength' => 50, 'maxlength' => 50]) !!}
</div>

<!-- Last Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('last_name', 'Last Name:') !!}
    {!! Form::text('last_name', null, ['class' => 'form-control', 'required', 'maxlength' => 50, 'maxlength' => 50]) !!}
</div>

<!-- Date Of Birth Field -->
<div class="form-group col-sm-6">
    {!! Form::label('date_of_birth', 'Date Of Birth:') !!}
    {!! Form::text('date_of_birth', null, ['class' => 'form-control','id'=>'date_of_birth']) !!}
</div>

@push('page_scripts')
    <script type="text/javascript">
        $('#date_of_birth').datepicker()
    </script>
@endpush

<!-- Gender Field -->
<div class="form-group col-sm-6">
    {!! Form::label('gender', 'Gender:') !!}
    {!! Form::text('gender', null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Joining Date Field -->
<div class="form-group col-sm-6">
    {!! Form::label('joining_date', 'Joining Date:') !!}
    {!! Form::text('joining_date', null, ['class' => 'form-control','id'=>'joining_date']) !!}
</div>

@push('page_scripts')
    <script type="text/javascript">
        $('#joining_date').datepicker()
    </script>
@endpush

<!-- Department Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('department_id', 'Department Id:') !!}
    {!! Form::number('department_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Designation Field -->
<div class="form-group col-sm-6">
    {!! Form::label('designation', 'Designation:') !!}
    {!! Form::text('designation', null, ['class' => 'form-control', 'required', 'maxlength' => 100, 'maxlength' => 100]) !!}
</div>

<!-- Qualification Field -->
<div class="form-group col-sm-6">
    {!! Form::label('qualification', 'Qualification:') !!}
    {!! Form::text('qualification', null, ['class' => 'form-control', 'maxlength' => 255, 'maxlength' => 255]) !!}
</div>

<!-- Experience Field -->
<div class="form-group col-sm-6">
    {!! Form::label('experience', 'Experience:') !!}
    {!! Form::number('experience', null, ['class' => 'form-control']) !!}
</div>

<!-- Email Field -->
<div class="form-group col-sm-6">
    {!! Form::label('email', 'Email:') !!}
    {!! Form::email('email', null, ['class' => 'form-control', 'required', 'maxlength' => 100, 'maxlength' => 100]) !!}
</div>

<!-- Phone Field -->
<div class="form-group col-sm-6">
    {!! Form::label('phone', 'Phone:') !!}
    {!! Form::text('phone', null, ['class' => 'form-control', 'required', 'maxlength' => 20, 'maxlength' => 20]) !!}
</div>

<!-- Address Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('address', 'Address:') !!}
    {!! Form::textarea('address', null, ['class' => 'form-control', 'required', 'maxlength' => 65535, 'maxlength' => 65535]) !!}
</div>

<!-- City Field -->
<div class="form-group col-sm-6">
    {!! Form::label('city', 'City:') !!}
    {!! Form::text('city', null, ['class' => 'form-control', 'required', 'maxlength' => 50, 'maxlength' => 50]) !!}
</div>

<!-- Country Field -->
<div class="form-group col-sm-6">
    {!! Form::label('country', 'Country:') !!}
    {!! Form::text('country', null, ['class' => 'form-control', 'required', 'maxlength' => 50, 'maxlength' => 50]) !!}
</div>

<!-- Photo Url Field -->
<div class="form-group col-sm-6">
    {!! Form::label('photo_url', 'Photo Url:') !!}
    {!! Form::text('photo_url', null, ['class' => 'form-control', 'maxlength' => 255, 'maxlength' => 255]) !!}
</div>

<!-- Staff Type Field -->
<div class="form-group col-sm-6">
    {!! Form::label('staff_type', 'Staff Type:') !!}
    {!! Form::text('staff_type', null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('status', 'Status:') !!}
    {!! Form::text('status', null, ['class' => 'form-control']) !!}
</div>