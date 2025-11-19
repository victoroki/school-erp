<!-- User Id Field -->
<!-- <div class="form-group col-sm-6">
    {!! Form::label('user_id', 'User Id:') !!}
    {!! Form::number('user_id', null, ['class' => 'form-control']) !!}
</div> -->

<!-- Admission No Field -->
<div class="form-group col-sm-6">
    {!! Form::label('admission_no', '* Admission No:') !!}
    {!! Form::text('admission_no', null, ['class' => 'form-control', 'required', 'maxlength' => 20, 'maxlength' => 20]) !!}
</div>

<!-- First Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('first_name', '*First Name:') !!}
    {!! Form::text('first_name', null, ['class' => 'form-control', 'required', 'maxlength' => 50, 'maxlength' => 50]) !!}
</div>

<!-- Middle Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('middle_name', 'Middle Name:') !!}
    {!! Form::text('middle_name', null, ['class' => 'form-control', 'maxlength' => 50, 'maxlength' => 50]) !!}
</div>

<!-- Last Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('last_name', '*Last Name:') !!}
    {!! Form::text('last_name', null, ['class' => 'form-control', 'required', 'maxlength' => 50, 'maxlength' => 50]) !!}
</div>

<!-- Date Of Birth Field -->
<div class="form-group col-sm-6">
    {!! Form::label('date_of_birth', '*Date Of Birth:') !!}
    {!! Form::date('date_of_birth', null, ['class' => 'form-control','id'=>'date_of_birth']) !!}
</div>

@push('page_scripts')
    <script type="text/javascript">
        $('#date_of_birth').datepicker()
    </script>
@endpush

<!-- Gender Field -->
<div class="form-group col-sm-6">
    {!! Form::label('gender', '*Gender:') !!}
    {!! Form::select('gender', ['male', 'female', 'other'], null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- City Field -->
<div class="form-group col-sm-6">
    {!! Form::label('city', '*City:') !!}
    {!! Form::text('city', null, ['class' => 'form-control', 'required', 'maxlength' => 50, 'maxlength' => 50]) !!}
</div>

<!-- Country Field -->
<div class="form-group col-sm-6">
    {!! Form::label('country', '*Country:') !!}
    {!! Form::text('country', null, ['class' => 'form-control', 'required', 'maxlength' => 50, 'maxlength' => 50]) !!}
</div>

<!-- Phone Field -->
<div class="form-group col-sm-6">
    {!! Form::label('phone', 'Phone:') !!}
    {!! Form::text('phone', null, ['class' => 'form-control', 'maxlength' => 20, 'maxlength' => 20]) !!}
</div>

<!-- Emergency Contact Field -->
<div class="form-group col-sm-6">
    {!! Form::label('emergency_contact', '*Emergency Contact:') !!}
    {!! Form::text('emergency_contact', null, ['class' => 'form-control', 'required', 'maxlength' => 20, 'maxlength' => 20]) !!}
</div>

<!-- Admission Date Field -->
<div class="form-group col-sm-6">
    {!! Form::label('admission_date', '*Admission Date:') !!}
    {!! Form::date('admission_date', null, ['class' => 'form-control','id'=>'admission_date']) !!}
</div>

@push('page_scripts')
    <script type="text/javascript">
        $('#admission_date').datepicker()
    </script>
@endpush

<!-- Photo Url Field -->
<div class="form-group col-sm-6">
    {!! Form::label('photo', 'Profile Photo:') !!}
    {!! Form::file('photo', ['class' => 'form-control']) !!}
</div>

<!-- Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('status', 'Status:') !!}
    {!! Form::select('status', ['active', 'inactive', 'alumni', 'transfered'], null, ['class' => 'form-control']) !!}
</div>