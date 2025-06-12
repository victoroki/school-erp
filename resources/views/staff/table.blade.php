<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="staff-table">
            <thead>
            <tr>
                <th>User Id</th>
                <th>Employee Id</th>
                <th>First Name</th>
                <th>Middle Name</th>
                <th>Last Name</th>
                <th>Date Of Birth</th>
                <th>Gender</th>
                <th>Joining Date</th>
                <th>Department Id</th>
                <th>Designation</th>
                <th>Qualification</th>
                <th>Experience</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Address</th>
                <th>City</th>
                <th>Country</th>
                <th>Photo Url</th>
                <th>Staff Type</th>
                <th>Status</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($staff as $staff)
                <tr>
                    <td>{{ $staff->user_id }}</td>
                    <td>{{ $staff->employee_id }}</td>
                    <td>{{ $staff->first_name }}</td>
                    <td>{{ $staff->middle_name }}</td>
                    <td>{{ $staff->last_name }}</td>
                    <td>{{ $staff->date_of_birth }}</td>
                    <td>{{ $staff->gender }}</td>
                    <td>{{ $staff->joining_date }}</td>
                    <td>{{ $staff->department_id }}</td>
                    <td>{{ $staff->designation }}</td>
                    <td>{{ $staff->qualification }}</td>
                    <td>{{ $staff->experience }}</td>
                    <td>{{ $staff->email }}</td>
                    <td>{{ $staff->phone }}</td>
                    <td>{{ $staff->address }}</td>
                    <td>{{ $staff->city }}</td>
                    <td>{{ $staff->country }}</td>
                    <td>{{ $staff->photo_url }}</td>
                    <td>{{ $staff->staff_type }}</td>
                    <td>{{ $staff->status }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['staff.destroy', $staff->id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('staff.show', [$staff->id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('staff.edit', [$staff->id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $staff])
        </div>
    </div>
</div>
