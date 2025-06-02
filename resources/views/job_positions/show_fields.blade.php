<!-- Title Field -->
<div class="col-sm-12">
    {!! Form::label('title', 'Title:') !!}
    <p>{{ $jobPosition->title }}</p>
</div>

<!-- Department Id Field -->
<div class="col-sm-12">
    {!! Form::label('department_id', 'Department Id:') !!}
    <p>{{ $jobPosition->department_id }}</p>
</div>

<!-- Description Field -->
<div class="col-sm-12">
    {!! Form::label('description', 'Description:') !!}
    <p>{{ $jobPosition->description }}</p>
</div>

<!-- Responsibilities Field -->
<div class="col-sm-12">
    {!! Form::label('responsibilities', 'Responsibilities:') !!}
    <p>{{ $jobPosition->responsibilities }}</p>
</div>

<!-- Qualifications Field -->
<div class="col-sm-12">
    {!! Form::label('qualifications', 'Qualifications:') !!}
    <p>{{ $jobPosition->qualifications }}</p>
</div>

<!-- Is Active Field -->
<div class="col-sm-12">
    {!! Form::label('is_active', 'Is Active:') !!}
    <p>{{ $jobPosition->is_active }}</p>
</div>

