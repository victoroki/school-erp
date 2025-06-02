<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="class-sections-table">
            <thead>
            <tr>
                <th>Academic Year Id</th>
                <th>Class Id</th>
                <th>Section Id</th>
                <th>Classroom Id</th>
                <th>Class Teacher Id</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($classSections as $classSection)
                <tr>
                    <td>{{ $classSection->academic_year_id }}</td>
                    <td>{{ $classSection->class_id }}</td>
                    <td>{{ $classSection->section_id }}</td>
                    <td>{{ $classSection->classroom_id }}</td>
                    <td>{{ $classSection->class_teacher_id }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['classSections.destroy', $classSection->id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('classSections.show', [$classSection->id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('classSections.edit', [$classSection->id]) }}"
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
