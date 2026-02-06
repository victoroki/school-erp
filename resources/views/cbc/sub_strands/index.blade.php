@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold text-danger">
                        <i class="fas fa-project-diagram mr-2"></i> CBC Sub-Strands
                    </h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a class="btn btn-danger elevation-2 px-4" href="{{ route('sub-strands.create') }}">
                        <i class="fas fa-plus mr-1"></i> Add New Sub-Strand
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="card card-outline card-danger elevation-2 border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                        <tr>
                            <th class="pl-4">Sub-Strand Name</th>
                            <th>Strand</th>
                            <th>Learning Area</th>
                            <th class="text-right pr-4">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($subStrands as $subStrand)
                            <tr>
                                <td class="pl-4 font-weight-bold">{{ $subStrand->name }}</td>
                                <td>{{ $subStrand->strand->name ?? 'N/A' }}</td>
                                <td><span class="badge badge-info px-2 py-1">{{ $subStrand->strand->learningArea->name ?? 'N/A' }}</span></td>
                                <td class="text-right pr-4">
                                    <div class='btn-group'>
                                        <a href="{{ route('sub-strands.edit', [$subStrand->id]) }}"
                                           class='btn btn-light btn-sm shadow-sm'>
                                            <i class="far fa-edit text-primary"></i>
                                        </a>
                                        {!! Form::open(['route' => ['sub-strands.destroy', $subStrand->id], 'method' => 'delete', 'class' => 'd-inline']) !!}
                                        {!! Form::button('<i class="far fa-trash-alt"></i>', ['type' => 'submit', 'class' => 'btn btn-light btn-sm shadow-sm text-danger', 'onclick' => "return confirm('Are you sure?')"]) !!}
                                        {!! Form::close() !!}
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    <div class="float-right">
                        {{ $subStrands->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
