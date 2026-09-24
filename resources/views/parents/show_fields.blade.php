<!-- User Id Field -->
<div class="col-sm-12">
    {!! Form::label('user_id', 'Portal Account:') !!}
    <p>{{ $parents->user->name ?? 'No portal account linked' }}</p>
</div>

<!-- First Name Field -->
<div class="col-sm-12">
    {!! Form::label('first_name', 'First Name:') !!}
    <p>{{ $parents->first_name }}</p>
</div>

<!-- Last Name Field -->
<div class="col-sm-12">
    {!! Form::label('last_name', 'Last Name:') !!}
    <p>{{ $parents->last_name }}</p>
</div>

<!-- Relationship Field -->
<div class="col-sm-12">
    {!! Form::label('relationship', 'Relationship:') !!}
    <p>{{ $parents->relationship ? ucfirst($parents->relationship) : '—' }}</p>
</div>

<!-- Email Field -->
<div class="col-sm-12">
    {!! Form::label('email', 'Email:') !!}
    <p>{{ $parents->email }}</p>
</div>

<!-- Phone Field -->
<div class="col-sm-12">
    {!! Form::label('phone', 'Phone:') !!}
    <p>{{ $parents->phone ? $parents->formatted_phone ?? $parents->phone : '—' }}</p>
</div>

<!-- Alternate Phone Field -->
<div class="col-sm-12">
    {!! Form::label('alternate_phone', 'Alternate Phone:') !!}
    <p>{{ $parents->alternate_phone ?? '—' }}</p>
</div>

<!-- Occupation Field -->
<div class="col-sm-12">
    {!! Form::label('occupation', 'Occupation:') !!}
    <p>{{ $parents->occupation ?? '—' }}</p>
</div>

