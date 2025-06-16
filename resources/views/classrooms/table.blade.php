<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="classrooms-table">
            <thead>
            <tr>
                <th>Room Number</th>
                <th>Building</th>
                <th>Floor</th>
                <th>Capacity</th>
                <th>Has Sockets</th>
                <th>Has Whiteboard</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($classrooms as $classroom)
                <tr>
                    <td>{{ $classroom->room_number }}</td>
                    <td>{{ $classroom->building }}</td>
                    <td>{{ $classroom->floor }}</td>
                    <td>{{ $classroom->capacity }}</td>
                    <td>{{ $classroom->has_sockets ? 'Yes' : 'No' }}</td>
                    <td>{{ $classroom->has_whiteboard ? 'Yes' : 'No' }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['classrooms.destroy', $classroom->classroom_id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('classrooms.show', [$classroom->classroom_id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('classrooms.edit', [$classroom->classroom_id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $classrooms])
        </div>
    </div>
</div>
