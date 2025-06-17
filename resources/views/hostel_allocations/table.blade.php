<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="hostel-allocations-table">
            <thead>
            <tr>
                <th>Student </th>
                <th>Hostel </th>
                <th>Room </th>
                <th>Bed Number</th>
                <th>Allocation Date</th>
                <th>Vacating Date</th>
                <th>Status</th>
                <th>Academic Year </th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($hostelAllocations as $hostelAllocation)
                <tr>
                    <td>{{ $hostelAllocation->student_id }}</td>
                    <td>{{ $hostelAllocation->hostel_id }}</td>
                    <td>{{ $hostelAllocation->room_id }}</td>
                    <td>{{ $hostelAllocation->bed_number }}</td>
                    <td>{{ $hostelAllocation->allocation_date }}</td>
                    <td>{{ $hostelAllocation->vacating_date }}</td>
                    <td>{{ $hostelAllocation->status }}</td>
                    <td>{{ $hostelAllocation->academic_year_id }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['hostel-allocations.destroy', $hostelAllocation->allocation_id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('hostel-allocations.show', [$hostelAllocation->allocation_id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('hostel-allocations.edit', [$hostelAllocation->allocation_id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $hostelAllocations])
        </div>
    </div>
</div>
