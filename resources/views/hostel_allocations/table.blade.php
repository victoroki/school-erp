<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="hostel-allocations-table">
            <thead>
            <tr>
                <th>Student Id</th>
                <th>Hostel Id</th>
                <th>Room Id</th>
                <th>Bed Number</th>
                <th>Allocation Date</th>
                <th>Vacating Date</th>
                <th>Status</th>
                <th>Academic Year Id</th>
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
                        {!! Form::open(['route' => ['hostelAllocations.destroy', $hostelAllocation->id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('hostelAllocations.show', [$hostelAllocation->id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('hostelAllocations.edit', [$hostelAllocation->id]) }}"
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
