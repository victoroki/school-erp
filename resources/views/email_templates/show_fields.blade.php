<!-- Title Field -->
<div class="col-sm-12">
    {!! Form::label('title', 'Title:') !!}
    <p>{{ $emailTemplate->title }}</p>
</div>

<!-- Subject Field -->
<div class="col-sm-12">
    {!! Form::label('subject', 'Subject:') !!}
    <p>{{ $emailTemplate->subject }}</p>
</div>

<!-- Content Field -->
<div class="col-sm-12">
    {!! Form::label('content', 'Content:') !!}
    <p>{{ $emailTemplate->content }}</p>
</div>

<!-- Variables Field -->
<div class="col-sm-12">
    {!! Form::label('variables', 'Variables:') !!}
    <p>{{ $emailTemplate->variables }}</p>
</div>

