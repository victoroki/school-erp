@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-8">
                    <h1 class="mb-1">Edit Bed Allocation</h1>
                    <p class="text-muted mb-0">
                        @if($hostelAllocation->status === 'active')
                            Currently holding bed {{ $hostelAllocation->bed_number ?? '?' }} in room
                            {{ $hostelAllocation->room->room_number ?? 'N/A' }}.
                        @else
                            This allocation is {{ $hostelAllocation->status }} — its bed has been released.
                        @endif
                    </p>
                </div>
                <div class="col-sm-4 text-right">
                    <a href="{{ route('hostel-allocations.show', $hostelAllocation->allocation_id) }}" class="btn btn-default">
                        <i class="fas fa-eye mr-1"></i> View Record
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('adminlte-templates::common.errors')

        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-edit mr-1"></i> Allocation Details</h3>
            </div>

            {!! Form::model($hostelAllocation, ['route' => ['hostel-allocations.update', $hostelAllocation->allocation_id], 'method' => 'patch']) !!}

            <div class="card-body">
                <div class="row">
                    @include('hostel_allocations.fields')
                </div>
            </div>

            <div class="card-footer text-right">
                <a href="{{ route('hostel-allocations.index') }}" class="btn btn-default mr-2"> Cancel </a>
                {!! Form::submit('Save Changes', ['class' => 'btn btn-primary']) !!}
            </div>

            {!! Form::close() !!}
        </div>
    </div>
@endsection
