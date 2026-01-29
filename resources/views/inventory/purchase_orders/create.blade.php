@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-shopping-cart text-warning mr-2"></i>Create Purchase Order</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('adminlte-templates::common.errors')

        <div class="card border-0 shadow-sm">
            {!! Form::open(['route' => 'inventory.purchase-orders.store']) !!}
            <div class="card-body">
                <div class="row">
                    <!-- Supplier -->
                    <div class="form-group col-sm-6">
                        {!! Form::label('supplier_id', 'Choose Supplier:') !!}
                        <select name="supplier_id" class="form-control" required>
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->supplier_id }}">{{ $supplier->name }} (Code: {{ $supplier->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Order Date -->
                    <div class="form-group col-sm-3">
                        {!! Form::label('order_date', 'Order Date:') !!}
                        {!! Form::date('order_date', \Carbon\Carbon::today(), ['class' => 'form-control', 'required']) !!}
                    </div>

                    <!-- Expected Date -->
                    <div class="form-group col-sm-3">
                        {!! Form::label('expected_delivery_date', 'Expected Delivery:') !!}
                        {!! Form::date('expected_delivery_date', \Carbon\Carbon::today()->addWeeks(1), ['class' => 'form-control', 'required']) !!}
                    </div>

                    <!-- Delivery Address -->
                    <div class="form-group col-sm-12">
                        {!! Form::label('delivery_address', 'Delivery Address:') !!}
                        {!! Form::text('delivery_address', 'School Main Store, Gate B', ['class' => 'form-control']) !!}
                    </div>

                    <div class="col-12"><hr></div>

                    <!-- Items Selection -->
                    <div class="col-12 mb-3">
                        <h5 class="font-weight-bold">Order Items</h5>
                        <div id="po-items-container">
                            <div class="row po-item-row mb-2">
                                <div class="col-md-5">
                                    <select name="items[0][item_id]" class="form-control" required>
                                        <option value="">Select Item</option>
                                        @foreach($items as $item)
                                            <option value="{{ $item->item_id }}">{{ $item->name }} (Current: {{ $item->quantity }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="number" name="items[0][quantity]" class="form-control qty-input" placeholder="Qty" min="1" required>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" name="items[0][unit_price]" step="0.01" class="form-control price-input" placeholder="Unit Price (KES)" required>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-danger btn-block remove-po-item"><i class="fas fa-trash"></i></button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-warning btn-sm mt-2 font-weight-bold" id="add-po-item">
                            <i class="fas fa-plus mr-1"></i> Add Item
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                <a href="{{ route('inventory.purchase-orders.index') }}" class="btn btn-default">Cancel</a>
                <div>
                    <span class="mr-3 text-muted">Estimated Total (Before Tax): <span id="grand-total-display" class="font-weight-bold text-dark">0.00</span></span>
                    {!! Form::submit('Generate Purchase Order', ['class' => 'btn btn-primary px-4 shadow-sm']) !!}
                </div>
            </div>
            {!! Form::close() !!}
        </div>
    </div>

    @push('page_scripts')
    <script>
        $(document).ready(function() {
            let poIndex = 1;
            $('#add-po-item').click(function() {
                const newRow = $('.po-item-row:first').clone();
                newRow.find('select').attr('name', `items[${poIndex}][item_id]`).val('');
                newRow.find('.qty-input').attr('name', `items[${poIndex}][quantity]`).val('');
                newRow.find('.price-input').attr('name', `items[${poIndex}][unit_price]`).val('');
                $('#po-items-container').append(newRow);
                poIndex++;
            });

            $(document).on('click', '.remove-po-item', function() {
                if ($('.po-item-row').length > 1) {
                    $(this).closest('.po-item-row').remove();
                    calculateTotal();
                }
            });

            $(document).on('input', '.qty-input, .price-input', function() {
                calculateTotal();
            });

            function calculateTotal() {
                let total = 0;
                $('.po-item-row').each(function() {
                    const qty = $(this).find('.qty-input').val() || 0;
                    const price = $(this).find('.price-input').val() || 0;
                    total += parseFloat(qty) * parseFloat(price);
                });
                $('#grand-total-display').text(new Intl.NumberFormat().format(total.toFixed(2)));
            }
        });
    </script>
    @endpush
@endsection
