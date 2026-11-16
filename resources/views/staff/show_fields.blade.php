<!-- User Id Field -->
<div class="col-sm-12">
    {!! Form::label('user_id', 'Portal Account:') !!}
    <p>{{ $staff->user->name ?? 'No portal account linked' }}</p>
</div>

<!-- Employee Id Field -->
<div class="col-sm-12">
    {!! Form::label('employee_id', 'Employee No:') !!}
    <p>{{ $staff->employee_number ?? '—' }}</p>
</div>

<!-- First Name Field -->
<div class="col-sm-12">
    {!! Form::label('first_name', 'First Name:') !!}
    <p>{{ $staff->first_name }}</p>
</div>

<!-- Middle Name Field -->
<div class="col-sm-12">
    {!! Form::label('middle_name', 'Middle Name:') !!}
    <p>{{ $staff->middle_name ?? '—' }}</p>
</div>

<!-- Last Name Field -->
<div class="col-sm-12">
    {!! Form::label('last_name', 'Last Name:') !!}
    <p>{{ $staff->last_name }}</p>
</div>

<!-- Date Of Birth Field -->
<div class="col-sm-12">
    {!! Form::label('date_of_birth', 'Date Of Birth:') !!}
    <p>{{ $staff->kenyan_dob ?? $staff->date_of_birth?->format('d/m/Y') ?? '—' }}</p>
</div>

<!-- Gender Field -->
<div class="col-sm-12">
    {!! Form::label('gender', 'Gender:') !!}
    <p>{{ ucfirst($staff->gender) }}</p>
</div>

<!-- Joining Date Field -->
<div class="col-sm-12">
    {!! Form::label('joining_date', 'Joining Date:') !!}
    <p>{{ $staff->date_of_joining ? \Carbon\Carbon::parse($staff->date_of_joining)->format('d/m/Y') : '—' }}</p>
</div>

<!-- Department Id Field -->
<div class="col-sm-12">
    {!! Form::label('department_id', 'Department:') !!}
    <p>{{ $staff->department->name ?? 'Not assigned' }}</p>
</div>

<!-- Designation Field -->
<div class="col-sm-12">
    {!! Form::label('designation', 'Designation:') !!}
    <p>{{ $staff->designation }}</p>
</div>

<!-- Qualification Field -->
<div class="col-sm-12">
    {!! Form::label('qualification', 'Qualification:') !!}
    <p>{{ $staff->qualification }}</p>
</div>

<!-- Experience Field -->
<div class="col-sm-12">
    {!! Form::label('experience', 'Experience:') !!}
    <p>{{ $staff->experience }}</p>
</div>

<!-- Email Field -->
<div class="col-sm-12">
    {!! Form::label('email', 'Email:') !!}
    <p>{{ $staff->email ?: $staff->work_email ?? '—' }}</p>
</div>

<!-- Phone Field -->
<div class="col-sm-12">
    {!! Form::label('phone', 'Phone:') !!}
    <p>{{ $staff->phone ?: $staff->phone_primary ?? '—' }}</p>
</div>

<!-- Address Field -->
<div class="col-sm-12">
    {!! Form::label('address', 'Address:') !!}
    <p>{{ $staff->address }}</p>
</div>

<!-- City Field -->
<div class="col-sm-12">
    {!! Form::label('city', 'City:') !!}
    <p>{{ $staff->city }}</p>
</div>

<!-- Country Field -->
<div class="col-sm-12">
    {!! Form::label('country', 'Country:') !!}
    <p>{{ $staff->country }}</p>
</div>

<!-- Photo Url Field -->
<div class="col-sm-12">
    {!! Form::label('photo_url', 'Photo Url:') !!}
    <p>{{ $staff->photo_url ? 'On file' : 'No photo uploaded' }}</p>
</div>

<!-- Staff Type Field -->
<div class="col-sm-12">
    {!! Form::label('staff_type', 'Staff Type:') !!}
    <p>{{ ucfirst(str_replace('_', ' ', $staff->staff_type ?? '')) ?: '—' }}</p>
</div>

<!-- Status Field -->
<div class="col-sm-12">
    {!! Form::label('status', 'Status:') !!}
    <p>{{ ucfirst(str_replace('_', ' ', $staff->employment_status ?? $staff->status ?? '')) ?: '—' }}</p>
</div>

