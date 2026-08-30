@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-7">
                    <h1 class="font-weight-bold text-danger">
                        <i class="fas fa-book-open mr-2"></i> CBE Learning Areas
                    </h1>
                    <h6 class="text-muted font-weight-normal">
                        Kenya Competency Based Education curriculum structure — {{ $totalCount }} area(s) configured
                    </h6>
                </div>
                <div class="col-sm-5 text-right">
                    @if($totalCount === 0)
                    <form action="{{ route('learning-areas.seed') }}" method="POST" class="d-inline"
                          onsubmit="return confirm('Load the official Kenyan CBE curriculum (learning areas, strands & sub-strands)? Existing areas are kept.')">
                        @csrf
                        <button type="submit" class="btn btn-outline-success elevation-1">
                            <i class="fas fa-download mr-1"></i> Load Kenyan Curriculum
                        </button>
                    </form>
                    @else
                    <button type="button" class="btn btn-outline-success elevation-1 disabled" title="Curriculum already loaded ({{ $totalCount }} areas)" disabled>
                        <i class="fas fa-check-circle mr-1"></i> Curriculum Loaded
                    </button>
                    @endif
                    <a class="btn btn-danger elevation-2 px-4" href="{{ route('learning-areas.create') }}">
                        <i class="fas fa-plus mr-1"></i> Add New Area
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        {{-- Filters --}}
        <div class="card elevation-2 border-0 mb-4">
            <div class="card-body py-3">
                <form action="{{ route('learning-areas.index') }}" method="GET" class="form-inline justify-content-end flex-wrap">
                    <select name="level" class="form-control select2 mr-2 mb-1" style="width: 200px;">
                        <option value="">All Levels</option>
                        @foreach($levels as $level)
                            <option value="{{ $level }}" @selected(request('level') === $level)>{{ $level }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search name or code..."
                           class="form-control mr-2 mb-1" style="width: 230px;">
                    <button type="submit" class="btn btn-primary mb-1"><i class="fas fa-filter mr-1"></i> Filter</button>
                </form>
            </div>
        </div>

        {{-- Areas grouped by level --}}
        @forelse($levels as $level)
            @php
                $areasForLevel = $learningAreas->where('level', $level);
            @endphp
            @if($areasForLevel->isNotEmpty())
                <div class="card card-outline card-danger elevation-2 border-0 mb-4">
                    <div class="card-header bg-white d-flex align-items-center">
                        <h3 class="card-title font-weight-bold mb-0">{{ $level }}</h3>
                        <span class="badge badge-light border ml-2">{{ $areasForLevel->count() }} learning area(s)</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                <tr>
                                    <th class="pl-4">Learning Area</th>
                                    <th style="width: 90px;">Code</th>
                                    <th class="text-center" style="width: 100px;">Strands</th>
                                    <th class="text-center" style="width: 110px;">Sub-Strands</th>
                                    <th style="width: 110px;">Status</th>
                                    <th class="text-right pr-4" style="width: 300px;">Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($areasForLevel as $area)
                                    @php
                                        $subStrandCount = $area->strands->sum(fn ($s) => $s->subStrands->count());
                                    @endphp
                                    <tr>
                                        <td class="pl-4">
                                            <span class="font-weight-bold text-dark">{{ $area->name }}</span>
                                            @if($area->description)
                                                <small class="text-muted d-block">{{ \Illuminate\Support\Str::limit($area->description, 90) }}</small>
                                            @endif
                                        </td>
                                        <td><code class="text-danger">{{ $area->code ?: '—' }}</code></td>
                                        <td class="text-center font-weight-bold">{{ $area->strands_count }}</td>
                                        <td class="text-center font-weight-bold">{{ $subStrandCount }}</td>
                                        <td>
                                            @if($area->status)
                                                <span class="badge badge-success px-2 py-1">Active</span>
                                            @else
                                                <span class="badge badge-secondary px-2 py-1">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-right pr-4">
                                            <div class='btn-group'>
                                                <a href="{{ route('strands.index', ['learning_area_id' => $area->id]) }}"
                                                   class='btn btn-light btn-sm shadow-sm' title="Manage strands"
                                                   data-toggle="tooltip">
                                                    <i class="fas fa-layer-group text-info"></i> Strands
                                                </a>
                                                <a href="{{ route('cbc-assessments.index', ['learning_area_id' => $area->id]) }}"
                                                   class='btn btn-light btn-sm shadow-sm' title="Record competency assessments">
                                                    <i class="fas fa-clipboard-check text-success"></i> Assess
                                                </a>
                                                <a href="{{ route('learning-areas.edit', [$area->id]) }}"
                                                   class='btn btn-light btn-sm shadow-sm' title="Edit">
                                                    <i class="far fa-edit text-primary"></i>
                                                </a>
                                                {!! Form::open(['route' => ['learning-areas.destroy', $area->id], 'method' => 'delete', 'class' => 'd-inline']) !!}
                                                {!! Form::button('<i class="far fa-trash-alt"></i>', ['type' => 'submit', 'class' => 'btn btn-light btn-sm shadow-sm text-danger', 'title' => 'Delete', 'onclick' => "return confirm('Are you sure? This also removes its strands.')"]) !!}
                                                {!! Form::close() !!}
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        @empty
            <div class="alert alert-info border-0 elevation-1 text-center py-5">
                <i class="fas fa-chalkboard fa-2x text-muted d-block mb-3"></i>
                No learning areas yet. Click <b>Load Kenyan Curriculum</b> to install the official CBE
                learning areas (Pre-Primary → Junior School) with their strands and sub-strands.
            </div>
        @endforelse
    </div>
@endsection
