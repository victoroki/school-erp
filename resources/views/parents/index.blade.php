@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Parents &amp; Guardians</h1>
                    <p class="text-muted small mb-0">Guardian contact records and the learners linked to each of them.</p>
                </div>
                <div class="col-sm-6 text-right">
                    <a class="btn btn-primary float-right" href="{{ route('parents.create') }}">
                        <i class="fas fa-user-plus mr-1"></i> Add New
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="row mb-3">
            <div class="col-lg-4 col-md-6">
                <div class="small-box bg-primary shadow-sm">
                    <span class="info-box-icon"><i class="fas fa-users"></i></span>
                    <div class="small-box-content">
                        <span class="small-box-text">Guardians on file</span>
                        <span class="small-box-number">{{ $totalParents }}</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="small-box bg-success shadow-sm">
                    <span class="info-box-icon"><i class="fas fa-link"></i></span>
                    <div class="small-box-content">
                        <span class="small-box-text">Linked to a learner</span>
                        <span class="small-box-number">{{ $totalLinked }}</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="small-box bg-info shadow-sm">
                    <span class="info-box-icon"><i class="fas fa-user-clock"></i></span>
                    <div class="small-box-content">
                        <span class="small-box-text">Not yet linked</span>
                        <span class="small-box-number">{{ max(0, $totalParents - $totalLinked) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-outline card-primary elevation-2 mb-4">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('parents.index') }}">
                    <div class="row g-2 align-items-end">
                        <div class="col-lg-6 col-md-6">
                            <div class="form-group mb-0">
                                <label class="small text-uppercase text-muted font-weight-bold" for="q">Search</label>
                                <div class="input-group shadow-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
                                    </div>
                                    <input type="text" id="q" name="q" value="{{ request('q') }}" class="form-control border-left-0"
                                           placeholder="Name, email, phone or occupation...">
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-3">
                            <div class="form-group mb-0">
                                <label class="small text-uppercase text-muted font-weight-bold" for="relationship">Relationship</label>
                                <select id="relationship" name="relationship" class="form-control shadow-sm">
                                    <option value="">All relationships</option>
                                    @foreach($relationships as $rel)
                                        <option value="{{ $rel }}" {{ request('relationship') === $rel ? 'selected' : '' }}>{{ ucfirst($rel) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-3">
                            <div class="btn-group w-100">
                                <button type="submit" class="btn btn-primary shadow-sm">
                                    <i class="fas fa-filter mr-1"></i> Filter
                                </button>
                                <a href="{{ route('parents.index') }}" class="btn btn-light border shadow-sm" title="Reset filters">
                                    <i class="fas fa-redo"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card card-outline card-primary elevation-2">
            @include('parents.table')
        </div>
    </div>
@endsection
