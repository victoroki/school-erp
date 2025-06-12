<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="school-classes-table">
            <thead>
            <tr>
                <th>Name</th>
                <th>Numeric Value</th>
                <th>Description</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($schoolClasses as $schoolClass)
                <tr>
                    <td>{{ $schoolClass->name }}</td>
                    <td>{{ $schoolClass->numeric_value }}</td>
                    <td>{{ $schoolClass->description }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['school-classes.destroy', $schoolClass->class_id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('school-classes.show', [$schoolClass->class_id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('school-classes.edit', [$schoolClass->class_id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $schoolClasses])
        </div>
    </div>
</div>
