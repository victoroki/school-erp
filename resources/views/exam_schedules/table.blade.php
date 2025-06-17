<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="exam-schedules-table">
            <thead>
            <tr>
                <th>Exam </th>
                <th>Class</th>
                <th>Subject</th>
                <th>Exam Date</th>
                <th>Start Time</th>
                <th>End Time</th>
                <!-- <th>Room Id</th> -->
                <th>Max Marks</th>
                <th>Passing Marks</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($examSchedules as $examSchedule)
                <tr>
                    <td>{{ $examSchedule->exam_id }}</td>
                    <td>{{ $examSchedule->class_id }}</td>
                    <td>{{ $examSchedule->subject_id }}</td>
                    <td>{{ $examSchedule->exam_date }}</td>
                    <td>{{ $examSchedule->start_time }}</td>
                    <td>{{ $examSchedule->end_time }}</td>
                    <!-- pp -->
                    <td>{{ $examSchedule->max_marks }}</td>
                    <td>{{ $examSchedule->passing_marks }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['exam-schedules.destroy', $examSchedule->schedule_id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('exam-schedules.show', [$examSchedule->schedule_id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('exam-schedules.edit', [$examSchedule->schedule_id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $examSchedules])
        </div>
    </div>
</div>
