@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Adjust Stock</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('inventory.dashboard') }}">Inventory</a></li>
                        <li class="breadcrumb-item active">Adjust Stock</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <div class="content">
        <div class="card">
            <div class="card-body">
                @include('adminlte-templates::common.errors')

                <form action="{{ route('inventory.adjust-stock') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="item_id">Item *</label>
                                <select name="item_id" id="item_id" class="form-control" required>
                                    <option value="">Select Item</option>
                                    @foreach($items as $item)
                                        <option value="{{ $item->item_id }}" 
                                            data-category="{{ $item->category->name ?? 'N/A' }}"
                                            data-current-qty="{{ $item->quantity }}"
                                            data-unit="{{ $item->unit ?? 'N/A' }}">
                                            {{ $item->name }} ({{ $item->category->name ?? 'N/A' }}) - Current: {{ $item->quantity }} {{ $item->unit ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="actual_quantity">Actual Physical Quantity *</label>
                                <input type="number" name="actual_quantity" id="actual_quantity" class="form-control" placeholder="Enter actual physical count" min="0" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="current_system_qty">Current System Quantity</label>
                                <input type="text" id="current_system_qty" class="form-control" readonly value="0">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="difference">Difference</label>
                                <input type="text" id="difference" class="form-control" readonly value="0">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="transaction_date">Date *</label>
                                <input type="date" name="transaction_date" id="transaction_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="remarks">Reason *</label>
                                <select name="remarks" id="remarks" class="form-control" required>
                                    <option value="">Select Reason</option>
                                    <option value="Physical Count Correction">Physical Count Correction</option>
                                    <option value="Damaged Items">Damaged Items</option>
                                    <option value="Lost Items">Lost Items</option>
                                    <option value="Expired Items">Expired Items</option>
                                    <option value="Theft">Theft</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-info">Adjust Stock</button>
                        <a href="{{ route('inventory.dashboard') }}" class="btn btn-default">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const itemSelect = document.getElementById('item_id');
            const currentQtyInput = document.getElementById('current_system_qty');
            const actualQtyInput = document.getElementById('actual_quantity');
            const differenceInput = document.getElementById('difference');

            itemSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const currentQty = selectedOption.getAttribute('data-current-qty') || 0;
                currentQtyInput.value = currentQty;
                calculateDifference();
            });

            actualQtyInput.addEventListener('input', calculateDifference);

            function calculateDifference() {
                const currentQty = parseFloat(currentQtyInput.value) || 0;
                const actualQty = parseFloat(actualQtyInput.value) || 0;
                const difference = actualQty - currentQty;
                differenceInput.value = difference;
            }
        });
    </script>
@endsection