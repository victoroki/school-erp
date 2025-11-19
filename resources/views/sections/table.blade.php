<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="sections-table">
            <thead>
            <tr>
                <th>Class</th>
                <th>Name</th>
                <th>Capacity</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($sections as $section)
                <tr>
                    <td>{{ optional($section->schoolClass)->name ?? 'N/A' }}</td>
                    <td>{{ $section->name }}</td>
                    <td>{{ $section->capacity }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['sections.destroy', $section->section_id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('sections.show', [$section->section_id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('sections.edit', [$section->section_id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $sections])
        </div>
    </div>
</div>
