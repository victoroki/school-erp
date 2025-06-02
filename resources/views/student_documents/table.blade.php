<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="student-documents-table">
            <thead>
            <tr>
                <th>Student Id</th>
                <th>Document Type</th>
                <th>Document Name</th>
                <th>File Path</th>
                <th>Uploaded At</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($studentDocuments as $studentDocument)
                <tr>
                    <td>{{ $studentDocument->student_id }}</td>
                    <td>{{ $studentDocument->document_type }}</td>
                    <td>{{ $studentDocument->document_name }}</td>
                    <td>{{ $studentDocument->file_path }}</td>
                    <td>{{ $studentDocument->uploaded_at }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['studentDocuments.destroy', $studentDocument->id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('studentDocuments.show', [$studentDocument->id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('studentDocuments.edit', [$studentDocument->id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $studentDocuments])
        </div>
    </div>
</div>
