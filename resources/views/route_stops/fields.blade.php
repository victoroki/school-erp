<!-- Route Field -->
<div class="form-group col-sm-6">
    {!! Form::label('route_id', 'Bus Route:') !!}
    {!! Form::select('route_id', $route, null, ['class' => 'form-control select2', 'placeholder' => 'Select Route', 'required']) !!}
</div>

<!-- Stop Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('stop_name', 'Stop Name:') !!}
    {!! Form::text('stop_name', null, ['class' => 'form-control', 'required', 'maxlength' => 100, 'placeholder' => 'e.g. Westlands Stage']) !!}
</div>

<!-- Landmark Field -->
<div class="form-group col-sm-12">
    {!! Form::label('landmark', 'Landmark / Description:') !!}
    {!! Form::text('landmark', null, ['class' => 'form-control', 'placeholder' => 'e.g. Near Shell Petrol Station']) !!}
</div>

<!-- Stop Time Field -->
<div class="form-group col-sm-4">
    {!! Form::label('stop_time', 'Est. Pickup Time:') !!}
    {!! Form::time('stop_time', null, ['class' => 'form-control']) !!}
</div>

<!-- Sequence Field -->
<div class="form-group col-sm-4">
    {!! Form::label('sequence', 'Stop Order / Sequence:') !!}
    {!! Form::number('sequence', null, ['class' => 'form-control', 'required', 'min' => 1, 'placeholder' => '1, 2, 3...']) !!}
</div>

<!-- Stop Fee Field -->
<div class="form-group col-sm-4">
    {!! Form::label('stop_fee', 'Stop Specific Fee (if any):') !!}
    {!! Form::number('stop_fee', null, ['class' => 'form-control', 'step' => '0.01', 'placeholder' => 'Leave 0 for route default']) !!}
</div>

<!-- Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('status', 'Status:') !!}
    {!! Form::select('status', ['active' => 'Active', 'inactive' => 'Inactive'], null, ['class' => 'form-control']) !!}
</div>

@push('page_scripts')
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap4'
            });
        });
    </script>
@endpush