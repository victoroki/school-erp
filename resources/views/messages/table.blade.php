<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="messages-table">
            <thead>
            <tr>
                <th>Sender</th>
                <th>Receiver</th>
                <th>Subject</th>
                <th>Is Read</th>
                <th>Sent At</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($messages as $message)
                <tr>
                    <td>{{ $message->sender->name ?? 'N/A' }}</td>
                    <td>{{ $message->receiver->name ?? 'N/A' }}</td>
                    <td>{{ $message->subject }}</td>
                    <td>{{ $message->is_read ? 'Yes' : 'No' }}</td>
                    <td>{{ $message->created_at }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['messages.destroy', $message->message_id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('messages.show', [$message->message_id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('messages.edit', [$message->message_id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $messages])
        </div>
    </div>
</div>