<!-- Name Field -->
<div class="col-sm-12">
    {!! Form::label('name', 'Name:') !!}
    <p>{{ $academicYear->name }}</p>
</div>

<!-- Start Date Field -->
<div class="col-sm-12">
    {!! Form::label('start_date', 'Start Date:') !!}
    <p>{{ $academicYear->start_date ? $academicYear->start_date->format('d M Y') : 'N/A' }}</p>
</div>

<!-- End Date Field -->
<div class="col-sm-12">
    {!! Form::label('end_date', 'End Date:') !!}
    <p>{{ $academicYear->end_date ? $academicYear->end_date->format('d M Y') : 'N/A' }}</p>
</div>

<!-- Is Current Field -->
<div class="col-sm-12">
    {!! Form::label('is_current', 'Is Current:') !!}
    <p>
        @if($academicYear->is_current)
            <span class="badge badge-success">Yes</span>
        @else
            <span class="badge badge-secondary">No</span>
        @endif
    </p>
</div>

