<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="staff-table">
            <thead>
            <tr>
                <th>User</th>
                <th>Employee</th>
                <th>First Name</th>
                <!-- <th>Middle Name</th> -->
                <th>Last Name</th>
                <th>Date Of Birth</th>
                <th>Gender</th>
                <th>Joining Date</th>
                <th>Department</th>
                <th>Designation</th>
                <th>Qualification</th>
                <th>Experience</th>
                <th>Email</th>
                <th>Phone</th>
                <th>City</th>
                <th>Country</th>
                <th>Staff Type</th>
                <th>Status</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($staff as $staff)
                <tr>
                    <td>{{ $staff->user->name ?? '—' }}</td>
                    <td>{{ $staff->employee_number ?? '—' }}</td>
                    <td>{{ $staff->first_name }}</td>
                    <!-- <td>{{ $staff->middle_name }}</td> -->
                    <td>{{ $staff->last_name }}</td>
                    <td>{{ $staff->date_of_birth?->format('d/m/Y') ?? '—' }}</td>
                    <td>{{ ucfirst($staff->gender ?? '') }}</td>
                    <td>{{ $staff->date_of_joining?->format('d/m/Y') ?? '—' }}</td>
                    <td>{{ $staff->department->name ?? 'N/A' }}</td>
                    <td>{{ $staff->designation }}</td>
                    <td>{{ $staff->qualification }}</td>
                    <td>{{ $staff->experience }}</td>
                    <td>{{ $staff->email ?: $staff->work_email ?? '—' }}</td>
                    <td>{{ $staff->phone ?: $staff->phone_primary ?? '—' }}</td>
                    <td>{{ $staff->city }}</td>
                    <td>{{ $staff->country }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $staff->staff_type ?? '')) }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $staff->employment_status ?? $staff->status ?? '')) }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['staff.destroy', $staff->staff_id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('staff.show', [$staff->staff_id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('staff.edit', [$staff->staff_id]) }}"
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
<!-- -->
</div>
