<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="staff-documents-table">
            <thead>
            <tr>
                <th>Staff Id</th>
                <th>Document Type</th>
                <th>Document Name</th>
                <th>File Path</th>
                <th>Uploaded At</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($staffDocuments as $staffDocument)
                <tr>
                    <td>{{ $staffDocument->staff_id }}</td>
                    <td>{{ $staffDocument->document_type }}</td>
                    <td>{{ $staffDocument->document_name }}</td>
                    <td>{{ $staffDocument->file_path }}</td>
                    <td>{{ $staffDocument->uploaded_at }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['staffDocuments.destroy', $staffDocument->id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('staffDocuments.show', [$staffDocument->id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('staffDocuments.edit', [$staffDocument->id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $staffDocuments])
        </div>
    </div>
</div>
