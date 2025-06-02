<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="sms-templates-table">
            <thead>
            <tr>
                <th>Title</th>
                <th>Content</th>
                <th>Variables</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($smsTemplates as $smsTemplate)
                <tr>
                    <td>{{ $smsTemplate->title }}</td>
                    <td>{{ $smsTemplate->content }}</td>
                    <td>{{ $smsTemplate->variables }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['smsTemplates.destroy', $smsTemplate->id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('smsTemplates.show', [$smsTemplate->id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('smsTemplates.edit', [$smsTemplate->id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $smsTemplates])
        </div>
    </div>
</div>
