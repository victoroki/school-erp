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
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
                        <div class="card-body d-flex flex-column p-4">
                            <h4 class="card-title font-weight-bold text-dark mb-1" style="font-size: 1.25rem;">{{ $schoolClass->name }}</h4>

                            @if($schoolClass->numeric_value)
                                <p class="text-muted small font-weight-bold text-uppercase tracking-wide mb-3">Level {{ $schoolClass->numeric_value }}</p>
                            @endif

                            @if($schoolClass->description)
                                <p class="text-secondary" style="font-size: 0.9rem; line-height: 1.4;">
                                    {{ \Illuminate\Support\Str::limit($schoolClass->description, 100) }}
                                </p>
                            @else
                                <p class="text-muted italic" style="font-size: 0.85rem;">No description provided.</p>
                            @endif

                            <div class="mb-4 mt-auto">
                                <span class="badge" style="background-color: #e0f2fe; color: #0284c7; padding: 0.4rem 0.6rem; border-radius: 6px; font-weight: 600;">
                                    <i class="fas fa-layer-group mr-1"></i> {{ $schoolClass->sections_count ?? 0 }} Sections
                                </span>
                                <span class="badge" style="background-color: #f1f5f9; color: #475569; padding: 0.4rem 0.6rem; border-radius: 6px; font-weight: 600; margin-left: 0.3rem;">
                                    <i class="fas fa-book mr-1"></i> {{ $schoolClass->class_subjects_count ?? 0 }} Subjects
                                </span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center pt-3 border-top" style="border-top-color: #f1f5f9 !important;">
                                <div class="d-flex" style="gap: 0.5rem;">
                                    <a href="{{ route('school-classes.show', $schoolClass->class_id) }}"
                                       class="btn btn-sm" style="background-color: #f1f5f9; color: #475569; border-radius: 6px; font-weight: 600;">
                                        View
                                    </a>
                                    <a href="{{ route('school-classes.edit', $schoolClass->class_id) }}"
                                       class="btn btn-sm" style="background-color: #eff6ff; color: #2563eb; border-radius: 6px; font-weight: 600;">
                                        Edit
                                    </a>
                                </div>
                                <div>
                                    {!! Form::open(['route' => ['school-classes.destroy', $schoolClass->class_id], 'method' => 'delete', 'class' => 'd-inline']) !!}
                                    {!! Form::button('<i class="fas fa-trash-alt"></i>', [
                                        'type' => 'submit',
                                        'class' => 'btn btn-sm',
                                        'style' => 'background-color: #fef2f2; color: #ef4444; border-radius: 6px;',
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
