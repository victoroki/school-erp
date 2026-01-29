<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table table-hover" id="exam-types-table">
            <thead>
            <tr>
                <th>Exam Category</th>
                <th>Abbreviation</th>
                <th>Description</th>
                <th class="text-center">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($examTypes as $examType)
                <tr>
                    <td>
                        <strong>{{ $examType->name }}</strong>
                    </td>
                    <td>
                        <span class="badge badge-info">{{ $examType->short_name ?? 'N/A' }}</span>
                    </td>
                    <td class="text-muted small">
                        {{ \Illuminate\Support\Str::limit($examType->description, 100) }}
                    </td>
                    <td style="width: 120px" class="text-center">
                        {!! Form::open(['route' => ['exam-types.destroy', $examType->exam_type_id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('exam-types.edit', [$examType->exam_type_id]) }}"
                               class='btn btn-light btn-sm' title="Edit">
                                <i class="far fa-edit text-primary"></i>
                            </a>
                            {!! Form::button('<i class="far fa-trash-alt text-danger"></i>', ['type' => 'submit', 'class' => 'btn btn-light btn-sm', 'title' => 'Delete', 'onclick' => "return confirm('Are you sure?')"]) !!}
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
            @include('adminlte-templates::common.paginate', ['records' => $examTypes])
        </div>
    </div>
</div>
