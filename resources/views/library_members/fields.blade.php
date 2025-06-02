<!-- User Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('user_id', 'User Id:') !!}
    {!! Form::number('user_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Member Type Field -->
<div class="form-group col-sm-6">
    {!! Form::label('member_type', 'Member Type:') !!}
    {!! Form::text('member_type', null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Reference Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('reference_id', 'Reference Id:') !!}
    {!! Form::number('reference_id', null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Membership Date Field -->
<div class="form-group col-sm-6">
    {!! Form::label('membership_date', 'Membership Date:') !!}
    {!! Form::text('membership_date', null, ['class' => 'form-control','id'=>'membership_date']) !!}
</div>

@push('page_scripts')
    <script type="text/javascript">
        $('#membership_date').datepicker()
    </script>
@endpush

<!-- Max Allowed Books Field -->
<div class="form-group col-sm-6">
    {!! Form::label('max_allowed_books', 'Max Allowed Books:') !!}
    {!! Form::number('max_allowed_books', null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('status', 'Status:') !!}
    {!! Form::text('status', null, ['class' => 'form-control']) !!}
</div>