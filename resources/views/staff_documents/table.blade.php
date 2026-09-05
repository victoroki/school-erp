<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table table-striped table-hover table-sm" id="staffDocuments-table">
            <thead>
            <tr>
                <th>Staff</th>
                <th>Document Type</th>
                <th>Document Name</th>
                <th>File</th>
                <th>Uploaded At</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($staffDocuments as $staffDocument)
                <tr>
                    <td>
                        <span class="font-weight-bold">{{ optional($staffDocument->staff)->full_name ?? optional($staffDocument->staff)->first_name }}</span>
                        @if(optional($staffDocument->staff)->employee_number)
                            <span class="badge badge-light border ml-1">{{ $staffDocument->staff->employee_number }}</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-secondary">{{ $staffDocument->document_type }}</span>
                    </td>
                    <td>{{ $staffDocument->document_name }}</td>
                    <td>
                        @if($staffDocument->file_path)
                            <a href="{{ route('staffDocuments.download', [$staffDocument->document_id]) }}" class="btn btn-outline-primary btn-xs">
                                <i class="fas fa-download mr-1"></i>Download
                            </a>
                        @else
                            <span class="text-muted small">No File</span>
                        @endif
                    </td>
                    <td><span class="text-muted">{{ optional($staffDocument->uploaded_at)->format('M d, Y') }}</span></td>
                    <td style="width: 120px">
                        {!! Form::open(['route' => ['staffDocuments.destroy', $staffDocument->document_id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('staffDocuments.show', [$staffDocument->document_id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('staffDocuments.edit', [$staffDocument->document_id]) }}"
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