@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold text-danger">
                        <i class="fas fa-door-open mr-2"></i> Exam Rooms
                    </h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a class="btn btn-danger elevation-2 px-4" href="{{ route('exam-rooms.create') }}">
                        <i class="fas fa-plus mr-1"></i> Add New Room
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
                            <th class="pl-4">Room No</th>
                            <th>Name</th>
                            <th>Capacity</th>
                            <th>Status</th>
                            <th class="text-right pr-4">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($examRooms as $room)
                            <tr>
                                <td class="pl-4 font-weight-bold text-danger">{{ $room->room_no }}</td>
                                <td>{{ $room->name ?: '-' }}</td>
                                <td><span class="badge badge-info px-2 py-1">{{ $room->capacity }} Seats</span></td>
                                <td>
                                    @if($room->status)
                                        <span class="badge badge-success px-2 py-1">Active</span>
                                    @else
                                        <span class="badge badge-secondary px-2 py-1">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-right pr-4">
                                    <div class='btn-group'>
                                        <a href="{{ route('exam-rooms.edit', [$room->id]) }}"
                                           class='btn btn-light btn-sm shadow-sm'>
                                            <i class="far fa-edit text-primary"></i>
                                        </a>
                                        {!! Form::open(['route' => ['exam-rooms.destroy', $room->id], 'method' => 'delete', 'class' => 'd-inline']) !!}
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
                        {{ $examRooms->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
