<!-- Name Field -->
<div class="col-sm-12">
    {!! Form::label('name', 'Name:') !!}
    <p>{{ $academicYear->name }}</p>
</div>

<!-- Start Date Field -->
<div class="col-sm-12">
    {!! Form::label('start_date', 'Start Date:') !!}
    <p>{{ $academicYear->start_date }}</p>
</div>

<!-- End Date Field -->
<div class="col-sm-12">
    {!! Form::label('end_date', 'End Date:') !!}
    <p>{{ $academicYear->end_date }}</p>
</div>

<!-- Is Current Field -->
<div class="col-sm-12">
    {!! Form::label('is_current', 'Is Current:') !!}
    <p>{{ $academicYear->is_current }}</p>
</div>

