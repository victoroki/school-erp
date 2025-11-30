<!-- Sender Id Field -->
<div class="col-sm-12">
    {!! Form::label('sender_id', 'Sender:') !!}
    <p>{{ $message->sender->name ?? 'N/A' }}</p>
</div>

<!-- Receiver Id Field -->
<div class="col-sm-12">
    {!! Form::label('receiver_id', 'Receiver:') !!}
    <p>{{ $message->receiver->name ?? 'N/A' }}</p>
</div>

<!-- Subject Field -->
<div class="col-sm-12">
    {!! Form::label('subject', 'Subject:') !!}
    <p>{{ $message->subject }}</p>
</div>

<!-- Message Field -->
<div class="col-sm-12">
    {!! Form::label('message', 'Message:') !!}
    <p>{{ $message->message }}</p>
</div>

<!-- Is Read Field -->
<div class="col-sm-12">
    {!! Form::label('is_read', 'Is Read:') !!}
    <p>{{ $message->is_read ? 'Yes' : 'No' }}</p>
</div>

<!-- Created At Field -->
<div class="col-sm-12">
    {!! Form::label('created_at', 'Sent At:') !!}
    <p>{{ $message->created_at }}</p>
</div>