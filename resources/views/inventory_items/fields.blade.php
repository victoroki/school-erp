<div class="row">
    <!-- Common Fields -->
    <div class="form-group col-sm-6">
        {!! Form::label('name', 'Item Name:') !!}
        {!! Form::text('name', null, ['class' => 'form-control', 'required', 'maxlength' => 255, 'placeholder' => 'e.g. HP Laptop 15" or A4 Printing Paper']) !!}
    </div>

    <div class="form-group col-sm-6">
        {!! Form::label('category_id', 'Category:') !!}
        <select name="category_id" id="category_id" class="form-control select2" required>
            <option value="">Select Category</option>
            @foreach($categories_objects as $cat)
                <option value="{{ $cat->category_id }}" data-type="{{ $cat->category_type }}" {{ (isset($inventoryItem) && $inventoryItem->category_id == $cat->category_id) || request('category_id') == $cat->category_id ? 'selected' : '' }}>
                    {{ $cat->name }} ({{ ucfirst($cat->category_type) }})
                </option>
            @endforeach
        </select>
    </div>

    <div class="form-group col-sm-6">
        {!! Form::label('item_code', 'Item Code (Auto-generated if blank):') !!}
        {!! Form::text('item_code', null, ['class' => 'form-control', 'placeholder' => 'e.g. COMP-2026-001']) !!}
    </div>

    <div class="form-group col-sm-6">
        {!! Form::label('supplier_id', 'Main Supplier:') !!}
        {!! Form::select('supplier_id', ['' => 'Select Supplier'] + $suppliers->toArray(), null, ['class' => 'form-control select2']) !!}
    </div>

    <div class="form-group col-sm-12 col-lg-12">
        {!! Form::label('description', 'Description:') !!}
        {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => 2]) !!}
    </div>

    <div class="col-12"><hr></div>

    <!-- Consumable Specific Fields -->
    <div class="col-12 consumable-fields">
        <h5 class="text-success mb-3"><i class="fas fa-info-circle mr-2"></i>Stock Information (Consumables)</h5>
        <div class="row">
            <div class="form-group col-sm-3">
                {!! Form::label('quantity', 'Current Quantity:') !!}
                {!! Form::number('quantity', null, ['class' => 'form-control', 'min' => 0]) !!}
            </div>
            <div class="form-group col-sm-3">
                {!! Form::label('unit', 'Unit (pcs, boxes, reams):') !!}
                {!! Form::text('unit', null, ['class' => 'form-control', 'placeholder' => 'pcs']) !!}
            </div>
            <div class="form-group col-sm-3">
                {!! Form::label('minimum_quantity', 'Min Stock Level (Alert):') !!}
                {!! Form::number('minimum_quantity', null, ['class' => 'form-control', 'min' => 0]) !!}
            </div>
            <div class="form-group col-sm-3">
                {!! Form::label('reorder_quantity', 'Reorder Quantity:') !!}
                {!! Form::number('reorder_quantity', null, ['class' => 'form-control', 'min' => 0]) !!}
            </div>
            <div class="form-group col-sm-4">
                {!! Form::label('cost_per_unit', 'Unit Price (KES):') !!}
                {!! Form::number('cost_per_unit', null, ['class' => 'form-control', 'step' => '0.01']) !!}
            </div>
            <div class="form-group col-sm-4">
                {!! Form::label('location', 'Storage Location:') !!}
                {!! Form::text('location', null, ['class' => 'form-control', 'placeholder' => 'Shelf A-1']) !!}
            </div>
            <div class="form-group col-sm-4 pt-4">
                <div class="custom-control custom-checkbox mt-2">
                    {!! Form::hidden('has_expiry', 0) !!}
                    {!! Form::checkbox('has_expiry', '1', null, ['class' => 'custom-control-input', 'id' => 'has_expiry']) !!}
                    <label class="custom-control-label" for="has_expiry">Has Expiry Date?</label>
                </div>
            </div>
        </div>
    </div>

    <!-- Asset Specific Fields -->
    <div class="col-12 asset-fields" style="display: none;">
        <h5 class="text-warning mb-3"><i class="fas fa-desktop mr-2"></i>Asset Information (Equipment/Furniture)</h5>
        <div class="row">
            <div class="form-group col-sm-4">
                {!! Form::label('asset_tag', 'Asset Tag Number:') !!}
                {!! Form::text('asset_tag', null, ['class' => 'form-control', 'placeholder' => 'TAG-COMP-001']) !!}
            </div>
            <div class="form-group col-sm-4">
                {!! Form::label('serial_number', 'Serial / Model Number:') !!}
                {!! Form::text('serial_number', null, ['class' => 'form-control']) !!}
            </div>
            <div class="form-group col-sm-4">
                {!! Form::label('current_condition', 'Condition:') !!}
                {!! Form::select('current_condition', ['Excellent' => 'Excellent', 'Good' => 'Good', 'Fair' => 'Fair', 'Poor' => 'Poor', 'Damaged' => 'Damaged'], 'Good', ['class' => 'form-control']) !!}
            </div>
            <div class="form-group col-sm-4">
                {!! Form::label('purchase_date', 'Purchase Date:') !!}
                {!! Form::date('purchase_date', null, ['class' => 'form-control']) !!}
            </div>
            <div class="form-group col-sm-4">
                {!! Form::label('warranty_period', 'Warranty Period (Months):') !!}
                {!! Form::number('warranty_period', null, ['class' => 'form-control', 'min' => 0]) !!}
            </div>
            <div class="form-group col-sm-4">
                {!! Form::label('warranty_expiry', 'Warranty Expiry:') !!}
                {!! Form::date('warranty_expiry', null, ['class' => 'form-control']) !!}
            </div>
        </div>
    </div>
</div>

@push('page_scripts')
<script>
    $(document).ready(function() {
        function toggleFields() {
            var type = $('#category_id option:selected').data('type');
            if (type === 'asset') {
                $('.asset-fields').show();
                $('.consumable-fields h5').html('<i class="fas fa-info-circle mr-2"></i>Stock & Location');
                $('#quantity').val(1); // Usually assets are unique, but we keep quantity field
            } else {
                $('.asset-fields').hide();
                $('.consumable-fields h5').html('<i class="fas fa-info-circle mr-2"></i>Stock Information (Consumables)');
            }
        }

        $('#category_id').on('change', toggleFields);
        toggleFields(); // Initial check
    });
</script>
@endpush