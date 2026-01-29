@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Academic Years</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-primary float-right"
                       href="{{ route('academic-years.create') }}">
                        Add Academic Year
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="row">
            @forelse($academicYears as $academicYear)
                <div class="col-md-4">
                    <div class="card mb-3 {{ $academicYear->is_current ? 'border-success' : '' }}">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title mb-1">{{ $academicYear->name }}</h5>

                            <p class="text-muted mb-2">
                                <span>{{ $academicYear->start_date->format('M d, Y') }}</span>
                                <span class="mx-1">–</span>
                                <span>{{ $academicYear->end_date->format('M d, Y') }}</span>
                            </p>

                            <p class="mb-3">
                                @if($academicYear->is_current)
                                    <span class="badge badge-success">Current academic year</span>
                                @else
                                    <span class="badge badge-secondary">Not current</span>
                                @endif
                            </p>

                            <div class="mt-auto d-flex justify-content-between">
                                <div>
                                    <a href="{{ route('academic-years.show', $academicYear->academic_year_id) }}"
                                       class="btn btn-outline-secondary btn-sm">
                                        View
                                    </a>
                                    <a href="{{ route('academic-years.edit', $academicYear->academic_year_id) }}"
                                       class="btn btn-outline-primary btn-sm">
                                        Edit
                                    </a>
                                </div>
                                <div>
                                    {!! Form::open(['route' => ['academic-years.destroy', $academicYear->academic_year_id], 'method' => 'delete', 'class' => 'd-inline']) !!}
                                    {!! Form::button('Delete', [
                                        'type' => 'submit',
                                        'class' => 'btn btn-outline-danger btn-sm',
                                        'onclick' => "return confirm('Are you sure you want to delete this academic year?')"
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
                            No academic years have been created yet.
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        @if($academicYears instanceof \Illuminate\Contracts\Pagination\Paginator)
            <div class="mt-3 d-flex justify-content-end">
                @include('adminlte-templates::common.paginate', ['records' => $academicYears])
            </div>
        @endif
    </div>
@endsection
