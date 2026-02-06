@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold text-danger">
                        <i class="fas fa-book-open mr-2"></i> CBC Learning Areas
                    </h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a class="btn btn-danger elevation-2 px-4" href="{{ route('learning-areas.create') }}">
                        <i class="fas fa-plus mr-1"></i> Add New Area
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
                            <th class="pl-4">Name</th>
                            <th>Code</th>
                            <th>Grade Level</th>
                            <th>Status</th>
                            <th class="text-right pr-4">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($learningAreas as $area)
                            <tr>
                                <td class="pl-4 font-weight-bold">{{ $area->name }}</td>
                                <td><code class="text-danger">{{ $area->code ?: '-' }}</code></td>
                                <td><span class="badge badge-info px-2 py-1">{{ $area->level }}</span></td>
                                <td>
                                    @if($area->status)
                                        <span class="badge badge-success px-2 py-1">Active</span>
                                    @else
                                        <span class="badge badge-secondary px-2 py-1">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-right pr-4">
                                    <div class='btn-group'>
                                        <a href="{{ route('learning-areas.edit', [$area->id]) }}"
                                           class='btn btn-light btn-sm shadow-sm'>
                                            <i class="far fa-edit text-primary"></i>
                                        </a>
                                        {!! Form::open(['route' => ['learning-areas.destroy', $area->id], 'method' => 'delete', 'class' => 'd-inline']) !!}
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
                        {{ $learningAreas->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
