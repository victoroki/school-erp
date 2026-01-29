@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>School Classes</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-primary float-right"
                       href="{{ route('school-classes.create') }}">
                        Add Class
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="row">
            @forelse($schoolClasses as $schoolClass)
                <div class="col-md-4">
                    <div class="card mb-3 h-100">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title mb-1">{{ $schoolClass->name }}</h5>

                            @if($schoolClass->numeric_value)
                                <p class="text-muted mb-2">Level {{ $schoolClass->numeric_value }}</p>
                            @endif

                            @if($schoolClass->description)
                                <p class="mb-3">
                                    {{ \Illuminate\Support\Str::limit($schoolClass->description, 120) }}
                                </p>
                            @endif

                            <div class="mb-3">
                                <span class="badge badge-info">
                                    {{ $schoolClass->sections_count ?? 0 }} sections
                                </span>
                                <span class="badge badge-primary">
                                    {{ $schoolClass->class_subjects_count ?? 0 }} subjects
                                </span>
                            </div>

                            <div class="mt-auto d-flex justify-content-between">
                                <div>
                                    <a href="{{ route('school-classes.show', $schoolClass->class_id) }}"
                                       class="btn btn-outline-secondary btn-sm">
                                        View
                                    </a>
                                    <a href="{{ route('school-classes.edit', $schoolClass->class_id) }}"
                                       class="btn btn-outline-primary btn-sm">
                                        Edit
                                    </a>
                                </div>
                                <div>
                                    {!! Form::open(['route' => ['school-classes.destroy', $schoolClass->class_id], 'method' => 'delete', 'class' => 'd-inline']) !!}
                                    {!! Form::button('Delete', [
                                        'type' => 'submit',
                                        'class' => 'btn btn-outline-danger btn-sm',
                                        'onclick' => "return confirm('Are you sure you want to delete this class?')"
                                    ]) !!}
                                    {!! Form::close() !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center text-muted">
                            No classes have been created yet.
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        @if($schoolClasses instanceof \Illuminate\Contracts\Pagination\Paginator)
            <div class="mt-3 d-flex justify-content-end">
                @include('adminlte-templates::common.paginate', ['records' => $schoolClasses])
            </div>
        @endif
    </div>
@endsection
