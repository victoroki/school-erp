@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Add Stock</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('inventory.dashboard') }}">Inventory</a></li>
                        <li class="breadcrumb-item active">Add Stock</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <div class="content">
        <div class="card">
            <div class="card-body">
                @include('adminlte-templates::common.errors')

                <form action="{{ route('inventory.add-stock') }}" method="POST">
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
                                            {{ request()->get('item') == $item->item_id ? 'selected' : '' }}>
                                            {{ $item->name }} ({{ $item->category->name ?? 'N/A' }}) - Current: {{ $item->quantity }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="quantity">Quantity *</label>
                                <input type="number" name="quantity" id="quantity" class="form-control" placeholder="Enter quantity" min="1" required>
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
                                <label for="supplier_id">Supplier</label>
                                <select name="supplier_id" id="supplier_id" class="form-control">
                                    <option value="">Select Supplier (Optional)</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->supplier_id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="remarks">Remarks</label>
                        <textarea name="remarks" id="remarks" class="form-control" rows="3" placeholder="Enter remarks or notes"></textarea>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-success">Add Stock</button>
                        <a href="{{ route('inventory.dashboard') }}" class="btn btn-default">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection