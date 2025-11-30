<!-- Sender Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('sender_id', 'Sender:') !!}
    {!! Form::select('sender_id', $users ?? [], null, ['class' => 'form-control', 'placeholder' => 'Select Sender']) !!}
</div>

<!-- Receiver Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('receiver_id', 'Receiver:') !!}
    {!! Form::select('receiver_id', $users ?? [], null, ['class' => 'form-control', 'placeholder' => 'Select Receiver']) !!}
</div>

<!-- Subject Field -->
<div class="form-group col-sm-6">
    {!! Form::label('subject', 'Subject:') !!}
    {!! Form::text('subject', null, ['class' => 'form-control', 'maxlength' => 255, 'maxlength' => 255]) !!}
</div>

<!-- Message Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('message', 'Message:') !!}
    {!! Form::textarea('message', null, ['class' => 'form-control']) !!}
</div>

<!-- Is Read Field -->
<div class="form-group col-sm-6">
    {!! Form::label('is_read', 'Is Read:') !!}
    {!! Form::select('is_read', ['1' => 'Yes', '0' => 'No'], null, ['class' => 'form-control']) !!}
</div>