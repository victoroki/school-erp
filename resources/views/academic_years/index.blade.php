@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Academic Years</h1>
                </div>
                <div class="col-sm-6">
                    {!! Form::open(['route' => ['academic-years.roll-forward'], 'method' => 'post', 'class' => 'd-inline', 'id' => 'roll-forward-form']) !!}
                    {!! Form::button('<i class="fas fa-forward"></i> Set Up Next Year', [
                        'type' => 'submit',
                        'class' => 'btn btn-success btn-sm mr-2',
                        'onclick' => "return confirm('Create the academic year after the current one, carrying its terms across and making it current?')"
                    ]) !!}
                    {!! Form::close() !!}

                    <a class="btn btn-primary btn-sm float-right"
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

                            <p class="mb-2">
                                @if($academicYear->is_current)
                                    <span class="badge badge-success">Current academic year</span>
                                @else
                                    <span class="badge badge-secondary">Not current</span>
                                @endif

                                @if(($academicYear->terms_count ?? 0) > 0)
                                    <span class="badge badge-light border">
                                        {{ $academicYear->terms_count }} {{ Str::plural('term', $academicYear->terms_count) }}
                                    </span>
                                @else
                                    {{-- A year with no terms cannot be used: fee assignment
                                         rejects it and the fee structure form has nothing
                                         to price against. Flagged here so it is not left
                                         to surface as a confusing error later. --}}
                                    <span class="badge badge-danger">No terms set up</span>
                                @endif
                            </p>

                            @if(($academicYear->terms_count ?? 0) > 0)
                                <ul class="list-unstyled small text-muted mb-3">
                                    @foreach($academicYear->terms as $term)
                                        <li class="d-flex justify-content-between">
                                            <span>{{ $term->name }}</span>
                                            <span>
                                                {{ $term->start_date->format('M d') }} &ndash; {{ $term->end_date->format('M d') }}
                                                <span class="badge badge-{{ $term->status === 'active' ? 'success' : ($term->status === 'completed' ? 'secondary' : 'info') }}">{{ $term->status }}</span>
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif

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
