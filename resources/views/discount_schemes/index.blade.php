@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Discount Schemes</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-primary float-right"
                       href="{{ route('fees.discounts.create') }}">
                        Add New
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('flash::message')

        <div class="clearfix"></div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table" id="discount-schemes-table">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Type</th>
                            <th>Value</th>
                            <th>Status</th>
                            <th colspan="3">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($discountSchemes as $discountScheme)
                            <tr>
                                <td>{{ $discountScheme->name }}</td>
                                <td>{{ $discountScheme->code }}</td>
                                <td>{{ ucfirst($discountScheme->type) }}</td>
                                <td>{{ $discountScheme->value }}</td>
                                <td>{{ ucfirst($discountScheme->status) }}</td>
                                <td style="width: 120px">
                                    {!! Form::open(['route' => ['fees.discounts.destroy', $discountScheme->id], 'method' => 'delete']) !!}
                                    <div class='btn-group'>
                                        <a href="{{ route('fees.discounts.show', [$discountScheme->id]) }}"
                                           class='btn btn-default btn-xs'>
                                            <i class="far fa-eye"></i>
                                        </a>
                                        <a href="{{ route('fees.discounts.edit', [$discountScheme->id]) }}"
                                           class='btn btn-default btn-xs'>
                                            <i class="far fa-edit"></i>
                                        </a>
                                        {!! Form::button('<i class="far fa-trash-alt"></i>', ['type' => 'submit', 'class' => 'btn btn-danger btn-xs', 'onclick' => "return confirm('Are you sure?')"]) !!}
                                    </div>
                                    {!! Form::close() !!}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="card-footer clearfix">
                    <div class="float-right">
                        @include('adminlte-templates::common.paginate', ['records' => $discountSchemes])
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
