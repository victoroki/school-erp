<!-- User Id Field -->
<div class="col-sm-12">
    {!! Form::label('user_id', 'User Id:') !!}
    <p>{{ $libraryMember->user_id }}</p>
</div>

<!-- Member Type Field -->
<div class="col-sm-12">
    {!! Form::label('member_type', 'Member Type:') !!}
    <p>{{ $libraryMember->member_type }}</p>
</div>

<!-- Reference Id Field -->
<div class="col-sm-12">
    {!! Form::label('reference_id', 'Reference Id:') !!}
    <p>{{ $libraryMember->reference_id }}</p>
</div>

<!-- Membership Date Field -->
<div class="col-sm-12">
    {!! Form::label('membership_date', 'Membership Date:') !!}
    <p>{{ $libraryMember->membership_date }}</p>
</div>

<!-- Max Allowed Books Field -->
<div class="col-sm-12">
    {!! Form::label('max_allowed_books', 'Max Allowed Books:') !!}
    <p>{{ $libraryMember->max_allowed_books }}</p>
</div>

<!-- Status Field -->
<div class="col-sm-12">
    {!! Form::label('status', 'Status:') !!}
    <p>{{ $libraryMember->status }}</p>
</div>

