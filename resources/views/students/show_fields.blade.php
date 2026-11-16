<!-- User Id Field -->
<div class="col-sm-12">
    {!! Form::label('user_id', 'Portal Account:') !!}
    <p>{{ $student->user->name ?? 'No portal account linked' }}</p>
</div>

<!-- Admission No Field -->
<div class="col-sm-12">
    {!! Form::label('admission_no', 'Admission No:') !!}
    <p>{{ $student->admission_no ?? '—' }}</p>
</div>

<!-- First Name Field -->
<div class="col-sm-12">
    {!! Form::label('first_name', 'First Name:') !!}
    <p>{{ $student->first_name }}</p>
</div>

<!-- Middle Name Field -->
<div class="col-sm-12">
    {!! Form::label('middle_name', 'Middle Name:') !!}
    <p>{{ $student->middle_name ?? '—' }}</p>
</div>

<!-- Last Name Field -->
<div class="col-sm-12">
    {!! Form::label('last_name', 'Last Name:') !!}
    <p>{{ $student->last_name }}</p>
</div>

<!-- Date Of Birth Field -->
<div class="col-sm-12">
    {!! Form::label('date_of_birth', 'Date Of Birth:') !!}
    <p>{{ $student->kenyan_dob }}</p>
</div>

<!-- Gender Field -->
<div class="col-sm-12">
    {!! Form::label('gender', 'Gender:') !!}
    <p>{{ ucfirst($student->gender) }}</p>
</div>

<!-- County Field -->
<div class="col-sm-12">
    {!! Form::label('county', 'County:') !!}
    <p>{{ $student->county ?? '—' }}</p>
</div>

<!-- City Field -->
<div class="col-sm-12">
    {!! Form::label('city', 'Town/City:') !!}
    <p>{{ $student->city }}</p>
</div>

<!-- Country Field -->
<div class="col-sm-12">
    {!! Form::label('country', 'Country:') !!}
    <p>{{ $student->country }}</p>
</div>

<!-- Phone Field -->
<div class="col-sm-12">
    {!! Form::label('phone', 'Phone:') !!}
    <p>{{ $student->formatted_phone }}</p>
</div>

<!-- Emergency Contact Field -->
<div class="col-sm-12">
    {!! Form::label('emergency_contact', 'Emergency Contact:') !!}
    <p>{{ $student->formatted_emergency_phone }}</p>
</div>

<!-- Admission Date Field -->
<div class="col-sm-12">
    {!! Form::label('admission_date', 'Admission Date:') !!}
    <p>{{ $student->kenyan_admission_date }}</p>
</div>

<!-- Education System Field -->
<div class="col-sm-12">
    {!! Form::label('education_system', 'Education System:') !!}
    <p>{{ $student->education_system ?? 'CBC' }}</p>
</div>

<!-- Photo Url Field -->
<div class="col-sm-12">
    {!! Form::label('photo_url', 'Photo:') !!}
    <p>{{ $student->photo_url ? 'On file' : 'No photo uploaded' }}</p>
</div>

<!-- Status Field -->
<div class="col-sm-12">
    {!! Form::label('status', 'Status:') !!}
    <p>{{ ucfirst($student->status) }}</p>
</div>

