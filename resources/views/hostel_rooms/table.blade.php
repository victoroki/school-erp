<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="hostel-rooms-table">
            <thead>
            <tr>
                <th>Hostel Id</th>
                <th>Room Number</th>
                <th>Room Type</th>
                <th>Capacity</th>
                <th>Occupied</th>
                <th>Floor</th>
                <th>Status</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($hostelRooms as $hostelRoom)
                <tr>
                    <td>{{ $hostelRoom->hostel_id }}</td>
                    <td>{{ $hostelRoom->room_number }}</td>
                    <td>{{ $hostelRoom->room_type }}</td>
                    <td>{{ $hostelRoom->capacity }}</td>
                    <td>{{ $hostelRoom->occupied }}</td>
                    <td>{{ $hostelRoom->floor }}</td>
                    <td>{{ $hostelRoom->status }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['hostelRooms.destroy', $hostelRoom->id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('hostelRooms.show', [$hostelRoom->id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('hostelRooms.edit', [$hostelRoom->id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-edit"></i>
                            </a>
                            {!! Form::button('<i class="far fa-trash-alt"></i>', ['type' => 'submit', 'class' => 'btn btn-danger btn-xs', 'onclick' => "return confirm('Are you sure?')"]) !!}
                        </div>
                        {!! Form::close() !!}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="card-footer clearfix">
        <div class="float-right">
            @include('adminlte-templates::common.paginate', ['records' => $hostelRooms])
        </div>
    </div>
</div>
