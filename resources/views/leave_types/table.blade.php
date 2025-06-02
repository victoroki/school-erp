<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="leave-types-table">
            <thead>
            <tr>
                <th>Name</th>
                <th>Days Allowed</th>
                <th>Description</th>
                <th>Is Paid</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($leaveTypes as $leaveType)
                <tr>
                    <td>{{ $leaveType->name }}</td>
                    <td>{{ $leaveType->days_allowed }}</td>
                    <td>{{ $leaveType->description }}</td>
                    <td>{{ $leaveType->is_paid }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['leaveTypes.destroy', $leaveType->id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('leaveTypes.show', [$leaveType->id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('leaveTypes.edit', [$leaveType->id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $leaveTypes])
        </div>
    </div>
</div>
