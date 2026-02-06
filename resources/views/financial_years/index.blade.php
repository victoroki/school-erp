@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="text-dark font-weight-bold"><i class="fas fa-calendar-alt text-secondary mr-2"></i>Financial Years</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('financial-years.create') }}" class="btn btn-secondary rounded-pill px-4 shadow-sm">
                        <i class="fas fa-plus mr-1"></i> Setup New Year
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="card border-0 shadow-sm rounded-lg">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr class="bg-light text-muted small text-uppercase">
                            <th class="pl-4 border-0">Name</th>
                            <th class="border-0">Duration</th>
                            <th class="border-0">Status</th>
                            <th class="border-0 text-center pr-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($financialYears as $year)
                            <tr>
                                <td class="pl-4 py-3 font-weight-bold">{{ $year->name }}</td>
                                <td class="py-3">{{ $year->start_date->format('M d, Y') }} - {{ $year->end_date->format('M d, Y') }}</td>
                                <td class="py-3">
                                    <span class="badge badge-{{ $year->status == 'open' ? 'success' : 'secondary' }} px-3 py-1 rounded-pill">
                                        {{ ucfirst($year->status) }}
                                    </span>
                                </td>
                                <td class="py-3 text-center pr-4">
                                    <a href="{{ route('financial-years.edit', [$year->id]) }}" class="btn btn-sm btn-outline-info rounded-circle"><i class="fas fa-edit"></i></a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
