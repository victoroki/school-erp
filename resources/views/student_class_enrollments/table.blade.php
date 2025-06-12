<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="student-class-enrollments-table">
            <thead>
            <tr>
                <th>Student </th>
                <th>Class Section </th>
                <th>Roll Number</th>
                <th>Academic Year </th>
                <th>Enrollment Date</th>
                <th>Status</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($studentClassEnrollments as $studentClassEnrollment)
                <tr>
                    <td>{{ $studentClassEnrollment->student_id }}</td>
                    <td>{{ $studentClassEnrollment->class_section_id }}</td>
                    <td>{{ $studentClassEnrollment->roll_number }}</td>
                    <td>{{ $studentClassEnrollment->academic_year_id }}</td>
                    <td>{{ $studentClassEnrollment->enrollment_date }}</td>
                    <td>{{ $studentClassEnrollment->status }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['student-class-enrollments.destroy', $studentClassEnrollment->enrollment_id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('student-class-enrollments.show', [$studentClassEnrollment->enrollment_id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('student-class-enrollments.edit', [$studentClassEnrollment->enrollment_id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $studentClassEnrollments])
        </div>
    </div>
</div>
