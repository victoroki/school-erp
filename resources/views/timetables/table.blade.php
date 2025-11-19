<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="timetables-table">
            <thead>
            <tr>
                <th>Class Section </th>
                <th>Day Of Week</th>
                <th>Period </th>
                <th>Subject </th>
                <th>Teacher</th>
                <th>Classroom</th>
                <th>Academic Year</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($timetables as $timetable)
                <tr>
                    <td>{{ optional($timetable->classSection->class)->name }} - {{ optional($timetable->classSection->section)->name }}</td>
                    <td>{{ $timetable->day_of_week }}</td>
                    <td>{{ $timetable->period->name }}</td>
                    <td>{{ $timetable->subject->name }}</td>
                    <td>{{ $timetable->teacher ? trim(($timetable->teacher->first_name ?? '') . ' ' . ($timetable->teacher->last_name ?? '')) : 'N/A' }}</td>
                    <td>{{ $timetable->classroom->room_number }}</td>
                    <td>{{ $timetable->academicYear->name }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['timetables.destroy', $timetable->timetable_id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('timetables.show', [$timetable->timetable_id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('timetables.edit', [$timetable->timetable_id]) }}"
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
