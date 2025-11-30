<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table table-striped table-hover table-sm" id="student-documents-table">
            <thead>
            <tr>
                <th>Student </th>
                <th>Document Type</th>
                <th>Document Name</th>
                <th>File</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($studentDocuments as $studentDocument)
                <tr>
                    <td>{{ optional($studentDocument->student)->first_name }} {{ optional($studentDocument->student)->last_name }}</td>
                    <td>{{ $studentDocument->document_type }}</td>
                    <td>{{ $studentDocument->document_name }}</td>
                    <td>
                        @if($studentDocument->file_path)
                            <a href="{{ route('student-documents.download', [$studentDocument->document_id]) }}" class="btn btn-outline-primary btn-xs">Download</a>
                        @else
                            N/A
                        @endif
                    </td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['student-documents.destroy', $studentDocument->document_id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('student-documents.show', [$studentDocument->document_id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('student-documents.edit', [$studentDocument->document_id]) }}"
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
