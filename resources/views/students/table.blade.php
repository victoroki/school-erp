<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="students-table">
            <thead>
            <tr>
                <!-- <th>User Id</th> -->
                <th>Admission No</th>
                <th>First Name</th>
                <!-- <th>Middle Name</th> -->
                <th>Last Name</th>
                <th>Date Of Birth</th>
                <th>Gender</th>
                <th>City</th>
                <!-- <th>Country</th> -->
                <th>Phone</th>
                <th>Emergency Contact</th>
                <!-- <th>Admission Date</th> -->
                <!-- <th>Photo Url</th> -->
                <th>Status</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($students as $student)
                <tr>
                    <!-- <td>{{ $student->user_id }}</td> -->
                    <td>{{ $student->admission_no }}</td>
                    <td>{{ $student->first_name }}</td>
                    <!-- <td>{{ $student->middle_name }}</td> -->
                    <td>{{ $student->last_name }}</td>
                    <td>{{ $student->date_of_birth }}</td>
                    <td>{{ $student->gender }}</td>
                    <td>{{ $student->city }}</td>
                    <!-- <td>{{ $student->country }}</td> -->
                    <td>{{ $student->phone }}</td>
                    <td>{{ $student->emergency_contact }}</td>
                    <!-- <td>{{ $student->admission_date }}</td> -->
                    <!-- <td>{{ $student->photo_url }}</td> -->
                    <td>{{ $student->status }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['students.destroy', $student->student_id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('students.show', [$student->student_id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('students.edit', [$student->student_id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $students])
        </div>
    </div>
</div>
