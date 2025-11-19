<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="timetables-table">
            <thead>
            <tr>
                <th>Class Section Id</th>
                <th>Day Of Week</th>
                <th>Period Id</th>
                <th>Subject Id</th>
                <th>Teacher Id</th>
                <th>Classroom Id</th>
                <th>Academic Year Id</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($timetables as $timetable)
                <tr>
                    <td>{{ $timetable->class_section_id }}</td>
                    <td>{{ $timetable->day_of_week }}</td>
                    <td>{{ $timetable->period_id }}</td>
                    <td>{{ $timetable->subject_id }}</td>
                    <td>{{ $timetable->teacher_id }}</td>
                    <td>{{ $timetable->classroom_id }}</td>
                    <td>{{ $timetable->academic_year_id }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['timetables.destroy', $timetable->id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('timetables.show', [$timetable->id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('timetables.edit', [$timetable->id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $timetables])
        </div>
    </div>
</div>
