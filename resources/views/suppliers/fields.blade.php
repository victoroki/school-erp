<div class="row">
    <!-- Supplier Name -->
    <div class="form-group col-sm-6">
        {!! Form::label('name', 'Supplier Name:') !!}
        {!! Form::text('name', null, ['class' => 'form-control', 'required', 'placeholder' => 'e.g. Wal-Mart Ltd']) !!}
    </div>

    <!-- Contact Person -->
    <div class="form-group col-sm-6">
        {!! Form::label('contact_person', 'Contact Person:') !!}
        {!! Form::text('contact_person', null, ['class' => 'form-control', 'placeholder' => 'Full name']) !!}
    </div>

    <!-- Phone -->
    <div class="form-group col-sm-4">
        {!! Form::label('phone', 'Phone Number:') !!}
        {!! Form::text('phone', null, ['class' => 'form-control']) !!}
    </div>

    <!-- Email -->
    <div class="form-group col-sm-4">
        {!! Form::label('email', 'Email Address:') !!}
        {!! Form::email('email', null, ['class' => 'form-control']) !!}
    </div>

    <!-- Website -->
    <div class="form-group col-sm-4">
        {!! Form::label('website', 'Website:') !!}
        {!! Form::text('website', null, ['class' => 'form-control', 'placeholder' => 'https://example.com']) !!}
    </div>

    <!-- Address -->
    <div class="form-group col-sm-12">
        {!! Form::label('address', 'Physical Address:') !!}
        {!! Form::text('address', null, ['class' => 'form-control']) !!}
    </div>

    <div class="col-12"><hr></div>

    <!-- What do they supply? -->
    <div class="form-group col-sm-12">
        <label class="d-block">What do they supply?</label>
        <div class="d-flex flex-wrap">
            @php
                $supplyOptions = ['Stationery', 'Electronics', 'Furniture', 'Lab Equipment', 'Cleaning Supplies', 'Uniforms', 'Food & Kitchen'];
                $selected = is_array($supplier->supply_categories ?? null) ? $supplier->supply_categories : json_decode($supplier->supply_categories ?? '[]', true);
            @endphp
            @foreach($supplyOptions as $option)
                <div class="custom-control custom-checkbox mr-4 mb-2">
                    <input type="checkbox" name="supply_categories[]" value="{{ $option }}" class="custom-control-input" id="cat_{{ Str::slug($option) }}" {{ in_array($option, $selected) ? 'checked' : '' }}>
                    <label class="custom-control-label font-weight-normal" for="cat_{{ Str::slug($option) }}">{{ $option }}</label>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Payment Terms -->
    <div class="form-group col-sm-4">
        {!! Form::label('payment_terms', 'Payment Terms:') !!}
        {!! Form::select('payment_terms', ['Cash' => 'Cash', 'Net 15' => 'Net 15', 'Net 30' => 'Net 30', 'Net 60' => 'Net 60', 'Net 90' => 'Net 90'], null, ['class' => 'form-control']) !!}
    </div>

    <!-- Rating -->
    <div class="form-group col-sm-4">
        {!! Form::label('rating', 'Supplier Rating (1-5):') !!}
        {!! Form::select('rating', [1 => '1 Star', 2 => '2 Stars', 3 => '3 Stars', 4 => '4 Stars', 5 => '5 Stars'], 3, ['class' => 'form-control']) !!}
    </div>

    <!-- Status -->
    <div class="form-group col-sm-4 pt-4">
        <div class="custom-control custom-switch mt-2">
            {!! Form::hidden('is_active', 0) !!}
            {!! Form::checkbox('is_active', '1', null, ['class' => 'custom-control-input', 'id' => 'is_active']) !!}
            <label class="custom-control-label" for="is_active">Active Supplier</label>
        </div>
    </div>

    <!-- Bank Details -->
    <div class="form-group col-sm-6">
        {!! Form::label('bank_details', 'Bank Details (for payments):') !!}
        {!! Form::textarea('bank_details', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Bank Name, Account Number, SWIFT, etc.']) !!}
    </div>

    <!-- Notes -->
    <div class="form-group col-sm-6">
        {!! Form::label('notes', 'Internal Notes:') !!}
        {!! Form::textarea('notes', null, ['class' => 'form-control', 'rows' => 3]) !!}
    </div>
</div>