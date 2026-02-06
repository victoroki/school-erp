@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold text-danger">
                        <i class="fas fa-project-diagram mr-2"></i> CBC Strands & Sub-Strands
                    </h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a class="btn btn-danger elevation-2 px-4" href="{{ route('strands.create') }}">
                        <i class="fas fa-plus mr-1"></i> Add New Strand
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
                            <th class="pl-4">Strand Name</th>
                            <th>Learning Area</th>
                            <th>Description</th>
                            <th class="text-right pr-4">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($strands as $strand)
                            <tr>
                                <td class="pl-4 font-weight-bold">{{ $strand->name }}</td>
                                <td><span class="badge badge-info px-2 py-1">{{ $strand->learningArea->name ?? 'N/A' }}</span></td>
                                <td class="small text-muted">{{ Str::limit($strand->description, 50) }}</td>
                                <td class="text-right pr-4">
                                    <div class='btn-group'>
                                        <a href="{{ route('strands.edit', [$strand->id]) }}"
                                           class='btn btn-light btn-sm shadow-sm'>
                                            <i class="far fa-edit text-primary"></i>
                                        </a>
                                        {!! Form::open(['route' => ['strands.destroy', $strand->id], 'method' => 'delete', 'class' => 'd-inline']) !!}
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
                        {{ $strands->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
