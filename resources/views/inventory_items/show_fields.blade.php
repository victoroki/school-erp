<!-- Name Field -->
<div class="col-sm-12">
    {!! Form::label('name', 'Name:') !!}
    <p>{{ $inventoryItem->name }}</p>
</div>

<!-- Category Id Field -->
<div class="col-sm-12">
    {!! Form::label('category_id', 'Category Id:') !!}
    <p>{{ $inventoryItem->category_id }}</p>
</div>

<!-- Quantity Field -->
<div class="col-sm-12">
    {!! Form::label('quantity', 'Quantity:') !!}
    <p>{{ $inventoryItem->quantity }}</p>
</div>

<!-- Unit Field -->
<div class="col-sm-12">
    {!! Form::label('unit', 'Unit:') !!}
    <p>{{ $inventoryItem->unit }}</p>
</div>

<!-- Minimum Quantity Field -->
<div class="col-sm-12">
    {!! Form::label('minimum_quantity', 'Minimum Quantity:') !!}
    <p>{{ $inventoryItem->minimum_quantity }}</p>
</div>

<!-- Cost Per Unit Field -->
<div class="col-sm-12">
    {!! Form::label('cost_per_unit', 'Cost Per Unit:') !!}
    <p>{{ $inventoryItem->cost_per_unit }}</p>
</div>

<!-- Supplier Id Field -->
<div class="col-sm-12">
    {!! Form::label('supplier_id', 'Supplier Id:') !!}
    <p>{{ $inventoryItem->supplier_id }}</p>
</div>

<!-- Location Field -->
<div class="col-sm-12">
    {!! Form::label('location', 'Location:') !!}
    <p>{{ $inventoryItem->location }}</p>
</div>

<!-- Description Field -->
<div class="col-sm-12">
    {!! Form::label('description', 'Description:') !!}
    <p>{{ $inventoryItem->description }}</p>
</div>

