<!-- Name Field -->
<div class="col-sm-12">
    {!! Form::label('name', 'Name:') !!}
    <p>{{ $hostel->name }}</p>
</div>

<!-- Type Field -->
<div class="col-sm-12">
    {!! Form::label('type', 'Type:') !!}
    <p>{{ $hostel->type }}</p>
</div>

<!-- Address Field -->
<div class="col-sm-12">
    {!! Form::label('address', 'Address:') !!}
    <p>{{ $hostel->address }}</p>
</div>

<!-- Warden Id Field -->
<div class="col-sm-12">
    {!! Form::label('warden_id', 'Warden Id:') !!}
    <p>{{ $hostel->warden_id }}</p>
</div>

<!-- Capacity Field -->
<div class="col-sm-12">
    {!! Form::label('capacity', 'Capacity:') !!}
    <p>{{ $hostel->capacity }}</p>
</div>

