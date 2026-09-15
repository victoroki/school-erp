@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="text-warning font-weight-bold">
                        <i class="fas fa-book-open mr-2"></i> {{ $homework->title }}
                    </h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('homework.edit', [$homework->id]) }}" class="btn btn-warning shadow-sm">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3 text-muted">Class</dt>
                    <dd class="col-sm-9">{{ $homework->class_name ?: '—' }}</dd>
                    <dt class="col-sm-3 text-muted">Subject</dt>
                    <dd class="col-sm-9">{{ $homework->subject ?: '—' }}</dd>
                    <dt class="col-sm-3 text-muted">Due Date</dt>
                    <dd class="col-sm-9">
                        @if($homework->due_date)
                            {{ $homework->due_date->format('d M, Y') }}
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </dd>
                    <dt class="col-sm-3 text-muted">Created By</dt>
                    <dd class="col-sm-9">{{ optional($homework->creator)->name }}</dd>
                    <dt class="col-sm-3 text-muted">Description</dt>
                    <dd class="col-sm-9">{{ $homework->description ?: '—' }}</dd>
                </dl>
            </div>
        </div>
    </div>
@endsection