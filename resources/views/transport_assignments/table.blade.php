<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="transport-assignments-table">
            <thead>
            <tr>
                <th>Route</th>
                <th>Vehicle</th>
                <th>Driver</th>
                <th>Assistant</th>
                <th>Departure Time</th>
                <th>Return Time</th>
                <th>Status</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($transportAssignments as $transportAssignment)
                <tr>
                    <td>{{ $transportAssignment->route->name ?? 'N/A' }}</td>
                    <td>{{ $transportAssignment->vehicle->vehicle_number ?? 'N/A' }}</td>
                    <td>{{ $transportAssignment->driver->name ?? 'N/A' }}</td>
                    <td>{{ $transportAssignment->assistant->name ?? 'N/A' }}</td>
                    <td>{{ $transportAssignment->departure_time }}</td>
                    <td>{{ $transportAssignment->return_time }}</td>
                    <td>{{ $transportAssignment->status }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['transport-assignments.destroy', $transportAssignment->assignment_id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('transport-assignments.show', [$transportAssignment->assignment_id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('transport-assignments.edit', [$transportAssignment->assignment_id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $transportAssignments])
        </div>
    </div>
</div>