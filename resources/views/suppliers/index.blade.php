@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-truck-loading text-primary mr-2"></i>Suppliers Management</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a class="btn btn-primary" href="{{ route('suppliers.create') }}">
                        <i class="fas fa-plus mr-1"></i> Add Supplier
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3">
                <form action="{{ route('suppliers.index') }}" method="GET">
                    <div class="row">
                        <div class="col-md-5">
                            <input type="text" name="search" class="form-control" placeholder="Search by name or code..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-control">
                                <option value="">All Statuses</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active Only</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-block">Filter</button>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('suppliers.index') }}" class="btn btn-default btn-block">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            @forelse($suppliers as $supplier)
                <div class="col-md-4 mb-4">
                    <div class="card h-100 border-0 shadow-sm overflow-hidden">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="icon-shape bg-light text-primary rounded shadow-sm d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="fas fa-building fa-lg"></i>
                                </div>
                                <div class="text-right">
                                    <span class="badge {{ $supplier->is_active ? 'badge-success' : 'badge-danger' }}">
                                        {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                    <div class="mt-1">
                                        @for($i=1; $i<=5; $i++)
                                            <i class="fas fa-star small {{ $i <= ($supplier->rating ?: 1) ? 'text-warning' : 'text-light' }}"></i>
                                        @endfor
                                    </div>
                                </div>
                            </div>

                            <h5 class="font-weight-bold mb-1">{{ $supplier->name }}</h5>
                            <p class="text-muted small mb-3">Code: <span class="badge badge-light border">{{ $supplier->code ?: 'N/A' }}</span></p>

                            <div class="small mb-2">
                                <i class="fas fa-user text-muted mr-2"></i> {{ $supplier->contact_person ?: 'No contact person' }}
                            </div>
                            <div class="small mb-2 text-truncate">
                                <i class="fas fa-phone text-muted mr-2"></i> {{ $supplier->phone ?: 'No phone' }}
                            </div>
                            <div class="small mb-0">
                                <i class="fas fa-envelope text-muted mr-2"></i> {{ $supplier->email ?: 'No email' }}
                            </div>

                            <div class="mt-3">
                                @php
                                    $cats = is_array($supplier->supply_categories) ? $supplier->supply_categories : json_decode($supplier->supply_categories ?: '[]', true);
                                @endphp
                                @foreach($cats as $cat)
                                    <span class="badge badge-outline-secondary small mr-1 mb-1">{{ $cat }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top-0 d-flex justify-content-between p-3">
                            <a href="{{ route('suppliers.show', $supplier->supplier_id) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                <i class="fas fa-eye mr-1"></i> Profile
                            </a>
                            <div class="btn-group">
                                <a href="{{ route('suppliers.edit', $supplier->supplier_id) }}" class="btn btn-link text-muted p-1 mr-2"><i class="fas fa-edit"></i></a>
                                {!! Form::open(['route' => ['suppliers.destroy', $supplier->supplier_id], 'method' => 'delete', 'style' => 'display:inline']) !!}
                                {!! Form::button('<i class="fas fa-trash"></i>', ['type' => 'submit', 'class' => 'btn btn-link text-danger p-1', 'onclick' => "return confirm('Are you sure?')"]) !!}
                                {!! Form::close() !!}
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <div class="text-muted">
                        <i class="fas fa-truck fa-4x mb-3 text-light"></i>
                        <h5>No suppliers found</h5>
                        <p>Track your vendors by adding a new supplier profile.</p>
                        <a href="{{ route('suppliers.create') }}" class="btn btn-primary mt-2">Add First Supplier</a>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="d-flex justify-content-center mt-3">
            {{ $suppliers->links() }}
        </div>
    </div>

    <style>
        .badge-outline-secondary { color: #6c757d; border: 1px solid #dee2e6; background: white; }
    </style>
@endsection
