<!-- Title Field -->
<div class="form-group col-sm-6">
    {!! Form::label('title', 'Title:') !!}
    {!! Form::text('title', null, ['class' => 'form-control', 'required', 'maxlength' => 255, 'maxlength' => 255]) !!}
</div>

<!-- Type Field -->
<div class="form-group col-sm-6">
    {!! Form::label('type', 'Type:') !!}
    {!! Form::select('type', ['announcement' => 'Announcement', 'alert' => 'Alert', 'event' => 'Event', 'exam' => 'Exam', 'fee' => 'Fee', 'general' => 'General'], null, ['class' => 'form-control', 'placeholder' => 'Select Type']) !!}
</div>

<!-- Recipient Type Field -->
<div class="form-group col-sm-6">
    {!! Form::label('recipient_type', 'Recipient Type:') !!}
    {!! Form::select('recipient_type', ['all' => 'All', 'students' => 'Students', 'parents' => 'Parents', 'teachers' => 'Teachers', 'staff' => 'Staff', 'specific' => 'Specific Users'], null, ['class' => 'form-control', 'placeholder' => 'Select Recipient Type']) !!}
</div>

<!-- Sender Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('sender_id', 'Sender:') !!}
    {!! Form::select('sender_id', $users ?? [], null, ['class' => 'form-control', 'placeholder' => 'Select Sender']) !!}
</div>

<!-- Message Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('message', 'Message:') !!}
    {!! Form::textarea('message', null, ['class' => 'form-control', 'required', 'maxlength' => 65535, 'maxlength' => 65535]) !!}
</div>