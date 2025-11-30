<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="transport-registrations-table">
            <thead>
            <tr>
                <th>Student</th>
                <th>Route</th>
                <th>Stop</th>
                <th>Fee Amount</th>
                <th>Payment Status</th>
                <th>Academic Year</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($transportRegistrations as $transportRegistration)
                <tr>
                    <td>{{ $transportRegistration->student->name ?? 'N/A' }}</td>
                    <td>{{ $transportRegistration->route->name ?? 'N/A' }}</td>
                    <td>{{ $transportRegistration->stop->stop_name ?? 'N/A' }}</td>
                    <td>{{ $transportRegistration->fee_amount }}</td>
                    <td>{{ $transportRegistration->payment_status }}</td>
                    <td>{{ $transportRegistration->academicYear->name ?? 'N/A' }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['transport-registrations.destroy', $transportRegistration->registration_id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('transport-registrations.show', [$transportRegistration->registration_id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('transport-registrations.edit', [$transportRegistration->registration_id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $transportRegistrations])
        </div>
    </div>
</div>