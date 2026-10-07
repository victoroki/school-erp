@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold text-dark" style="font-size: 1.75rem;">
                        <i class="fas fa-door-open text-primary mr-2"></i> Classroom Details
                        @if(!$classroom->is_active)
                            <span class="badge badge-secondary ml-2" style="vertical-align: middle;">Archived</span>
                        @endif
                    </h1>
                    <p class="text-muted mb-0">Overview of room resources and facilities.</p>
                </div>
                <div class="col-sm-6 d-flex justify-content-end">
                    <a class="btn px-4 py-2 shadow-sm mr-2"
                       href="{{ route('classrooms.index') }}"
                       style="background-color: #f1f5f9; color: #475569; font-weight: 600; border-radius: 8px;">
                        <i class="fas fa-arrow-left mr-2"></i> Back to List
                    </a>
                    @can('academics.settings.manage')
                        @if($classroom->is_active)
                            <a class="btn btn-primary px-4 py-2 shadow-sm mr-2"
                               href="{{ route('classrooms.edit', $classroom->classroom_id) }}"
                               style="font-weight: 600; border-radius: 8px;">
                                <i class="fas fa-edit mr-2"></i> Edit
                            </a>
                            <form method="POST" action="{{ route('classrooms.archive', $classroom->classroom_id) }}" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-warning px-4 py-2 shadow-sm" style="font-weight: 600; border-radius: 8px;">
                                    <i class="fas fa-archive mr-2"></i> Archive
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('classrooms.restore', $classroom->classroom_id) }}" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-success px-4 py-2 shadow-sm" style="font-weight: 600; border-radius: 8px;">
                                    <i class="fas fa-undo mr-2"></i> Restore
                                </button>
                            </form>
                        @endif
                    @endcan
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3 mt-2">
        {{-- Usage / deletion preview --}}
        <div class="card card-outline card-primary elevation-2 mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h3 class="card-title text-uppercase small font-weight-bold mb-0">
                    <i class="fas fa-link mr-1 text-primary"></i> Where this room is used
                </h3>
                @php $totalUsage = array_sum(array_column($usage, 'count')); @endphp
                <span class="badge badge-secondary">
                    {{ $totalUsage }} {{ \Illuminate\Support\Str::plural('record', $totalUsage) }}
                </span>
            </div>
            <div class="card-body">
                @if($totalUsage > 0)
                    <p class="small text-muted">
                        <i class="fas fa-info-circle mr-1"></i>
                        This room cannot be deleted while it is referenced by the records below.
                        Archive it instead to withdraw it from new allocations.
                    </p>
                @endif
                <div class="row">
                    @foreach($usage as $entry)
                        <div class="col-6 col-md-3 mb-3">
                            <div class="border rounded p-2 h-100 text-center">
                                <div class="h3 mb-0 {{ $entry['count'] > 0 ? 'text-primary' : 'text-muted' }}">
                                    {{ $entry['count'] }}
                                </div>
                                <div class="small text-muted">{{ $entry['label'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
            <div class="card-header bg-white border-bottom-0 py-4 px-4">
                <h5 class="mb-0 font-weight-bold">Facility Information</h5>
            </div>
            <div class="card-body px-4 pb-5">
                <div class="row">
                    @include('classrooms.show_fields')
                </div>
            </div>
        </div>
    </div>
@endsection
