<!-- Title Field -->
<div class="col-sm-12">
    {!! Form::label('title', 'Title:') !!}
    <p>{{ $notification->title }}</p>
</div>

<!-- Type Field -->
<div class="col-sm-12">
    {!! Form::label('type', 'Type:') !!}
    <p>{{ ucfirst($notification->type) }}</p>
</div>

<!-- Recipient Type Field -->
<div class="col-sm-12">
    {!! Form::label('recipient_type', 'Recipient Type:') !!}
    <p>{{ ucfirst(str_replace('_', ' ', $notification->recipient_type)) }}</p>
</div>

<!-- Sender Field -->
<div class="col-sm-12">
    {!! Form::label('sender_id', 'Sender:') !!}
    <p>{{ $notification->sender->name ?? 'System' }}</p>
</div>

<!-- Message Field -->
<div class="col-sm-12">
    {!! Form::label('message', 'Message:') !!}
    <p>{{ $notification->message }}</p>
</div>

<!-- Created At Field -->
<div class="col-sm-12">
    {!! Form::label('created_at', 'Created At:') !!}
    <p>{{ $notification->created_at }}</p>
</div>