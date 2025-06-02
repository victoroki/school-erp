<!-- Title Field -->
<div class="col-sm-12">
    {!! Form::label('title', 'Title:') !!}
    <p>{{ $smsTemplate->title }}</p>
</div>

<!-- Content Field -->
<div class="col-sm-12">
    {!! Form::label('content', 'Content:') !!}
    <p>{{ $smsTemplate->content }}</p>
</div>

<!-- Variables Field -->
<div class="col-sm-12">
    {!! Form::label('variables', 'Variables:') !!}
    <p>{{ $smsTemplate->variables }}</p>
</div>

