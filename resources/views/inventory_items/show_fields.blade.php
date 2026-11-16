<!-- Name Field -->
<div class="col-sm-12">
    {!! Form::label('name', 'Name:') !!}
    <p>{{ $inventoryItem->name }}</p>
</div>

<!-- Category Id Field -->
<div class="col-sm-12">
    {!! Form::label('category_id', 'Category:') !!}
    <p>{{ $inventoryItem->category->name ?? 'N/A' }}</p>
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
    <p>{{ \App\Support\Money::format($inventoryItem->cost_per_unit ?? 0) }}</p>
</div>

<!-- Supplier Id Field -->
<div class="col-sm-12">
    {!! Form::label('supplier_id', 'Supplier:') !!}
    <p>{{ $inventoryItem->supplier->name ?? 'N/A' }}</p>
</div>

<!-- Location Field -->
<div class="col-sm-12">
    {!! Form::label('location', 'Location:') !!}
    <p>{{ $inventoryItem->location ?? 'Not specified' }}</p>
</div>

<!-- Description Field -->
<div class="col-sm-12">
    {!! Form::label('description', 'Description:') !!}
    <p>{{ $inventoryItem->description ?? '—' }}</p>
</div>

