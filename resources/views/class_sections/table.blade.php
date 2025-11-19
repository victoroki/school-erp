<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="class-sections-table">
            <thead>
            <tr>
                <th>Academic Year </th>
                <th>Class </th>
                <th>Section </th>
                <th>Classroom </th>
                <th>Class Teacher</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($classSections as $classSection)
                <tr>
                    <td>{{ $classSection->academicYear->name }}</td>
                    <td>{{ $classSection->class->name }}</td>
                    <td>{{ $classSection->section->name }}</td>
                    <td>{{ optional($classSection->classroom)->room_number }}</td>
                    <td>{{ $classSection->classTeacher ? ($classSection->classTeacher->first_name . ' ' . $classSection->classTeacher->last_name) : 'N/A' }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['class-sections.destroy', $classSection->class_section_id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('class-sections.show', [$classSection->class_section_id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('class-sections.edit', [$classSection->class_section_id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $classSections])
        </div>
    </div>
</div>
