<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="email-templates-table">
            <thead>
            <tr>
                <th>Title</th>
                <th>Subject</th>
                <th>Content</th>
                <th>Variables</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($emailTemplates as $emailTemplate)
                <tr>
                    <td>{{ $emailTemplate->title }}</td>
                    <td>{{ $emailTemplate->subject }}</td>
                    <td>{{ $emailTemplate->content }}</td>
                    <td>{{ $emailTemplate->variables }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['emailTemplates.destroy', $emailTemplate->id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('emailTemplates.show', [$emailTemplate->id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('emailTemplates.edit', [$emailTemplate->id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $emailTemplates])
        </div>
    </div>
</div>
