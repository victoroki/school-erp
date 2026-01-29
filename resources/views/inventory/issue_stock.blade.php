@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Issue Stock</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('inventory.dashboard') }}">Inventory</a></li>
                        <li class="breadcrumb-item active">Issue Stock</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <div class="content">
        <div class="card">
            <div class="card-body">
                @include('adminlte-templates::common.errors')

                <form action="{{ route('inventory.issue-stock') }}" method="POST">
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
                                            {{ $item->name }} ({{ $item->category->name ?? 'N/A' }}) - Available: {{ $item->quantity }} {{ $item->unit ?? '' }}
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
                                <label for="issued_to">Issued To *</label>
                                <input type="text" name="issued_to" id="issued_to" class="form-control" placeholder="Enter recipient (staff, department, etc.)" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="transaction_date">Date *</label>
                                <input type="date" name="transaction_date" id="transaction_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="remarks">Purpose/Reason</label>
                        <textarea name="remarks" id="remarks" class="form-control" rows="3" placeholder="Enter purpose or reason for issuing"></textarea>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-warning">Issue Stock</button>
                        <a href="{{ route('inventory.dashboard') }}" class="btn btn-default">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection