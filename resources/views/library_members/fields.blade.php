<!-- User Selection -->
<div class="form-group col-sm-6">
    {!! Form::label('user_id', 'Select Member (Student/Staff):') !!}
    {!! Form::select('user_id', $users ?? [], null, ['class' => 'form-control select2', 'placeholder' => 'Search by name...', 'required']) !!}
</div>

<!-- Member Type Field -->
<div class="form-group col-sm-6">
    {!! Form::label('member_type', 'Member Type:') !!}
    {!! Form::select('member_type', ['student' => 'Student', 'teacher' => 'Teacher', 'staff' => 'Staff'], null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Reference ID (Read Only / Auto) -->
<div class="form-group col-sm-6">
    {!! Form::label('reference_id', 'Membership ID:') !!}
    {!! Form::text('reference_id', null, ['class' => 'form-control', 'placeholder' => 'Auto-generated if empty']) !!}
    <small class="text-muted">Leave blank to auto-generate.</small>
</div>

<!-- Dates -->
<div class="form-group col-sm-6">
    {!! Form::label('membership_date', 'Membership Start Date:') !!}
    {!! Form::text('membership_date', null, ['class' => 'form-control', 'id'=>'membership_date', 'required']) !!}
</div>

<div class="form-group col-sm-6">
    {!! Form::label('membership_expiry_date', 'Expiry Date:') !!}
    {!! Form::text('membership_expiry_date', null, ['class' => 'form-control', 'id'=>'membership_expiry_date']) !!}
</div>

<!-- Limits and Status -->
<div class="form-group col-sm-6">
    {!! Form::label('max_allowed_books', 'Max Books Allowed:') !!}
    {!! Form::number('max_allowed_books', 3, ['class' => 'form-control', 'required', 'min' => 1]) !!}
</div>

<div class="form-group col-sm-6">
    {!! Form::label('status', 'Status:') !!}
    {!! Form::select('status', ['active' => 'Active', 'suspended' => 'Suspended', 'expired' => 'Expired'], null, ['class' => 'form-control']) !!}
</div>

@push('page_scripts')
    <script type="text/javascript">
        $('#membership_date').datetimepicker({
            format: 'YYYY-MM-DD',
            useCurrent: true
        });
        $('#membership_expiry_date').datetimepicker({
            format: 'YYYY-MM-DD',
            useCurrent: false
        });
    </script>
@endpush