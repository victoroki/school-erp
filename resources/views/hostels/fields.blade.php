<!-- Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('name', 'Hostel Name:') !!}
    {!! Form::text('name', null, ['class' => 'form-control', 'required', 'maxlength' => 100]) !!}
</div>

<!-- Type Field -->
<div class="form-group col-sm-6">
    {!! Form::label('type', 'Hostel Type:') !!}
    {!! Form::select('type', [
        'boys' => 'Boys Hostel',
        'girls' => 'Girls Hostel',
        'co-ed' => 'Co-ed / Shared'
    ], null, ['class' => 'form-control select2', 'required']) !!}
</div>

<!-- Address Field -->
<div class="form-group col-sm-12">
    {!! Form::label('address', 'Location/Address:') !!}
    {!! Form::textarea('address', null, ['class' => 'form-control', 'required', 'rows' => 2]) !!}
</div>

<!-- Warden Field -->
<div class="form-group col-sm-6">
    {!! Form::label('warden_id', 'Hostel Warden:') !!}
    {!! Form::select('warden_id', ['' => 'Select Warden'] + $staff, null, ['class' => 'form-control select2']) !!}
</div>

<!-- Capacity Field -->
<div class="form-group col-sm-6">
    {!! Form::label('capacity', 'Total Capacity (Beds):') !!}
    {!! Form::number('capacity', null, ['class' => 'form-control', 'required', 'min' => 1]) !!}
</div>

@push('page_scripts')
    <script>
        $(document).ready(function() {
            $('.select2').each(function() {
                $(this).select2({
                    theme: 'bootstrap4'
                });
            });
        });
    </script>
@endpush