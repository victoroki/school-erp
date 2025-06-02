<!-- Subject Code Field -->
<div class="col-sm-12">
    {!! Form::label('subject_code', 'Subject Code:') !!}
    <p>{{ $subject->subject_code }}</p>
</div>

<!-- Name Field -->
<div class="col-sm-12">
    {!! Form::label('name', 'Name:') !!}
    <p>{{ $subject->name }}</p>
</div>

<!-- Description Field -->
<div class="col-sm-12">
    {!! Form::label('description', 'Description:') !!}
    <p>{{ $subject->description }}</p>
</div>

<!-- Is Elective Field -->
<div class="col-sm-12">
    {!! Form::label('is_elective', 'Is Elective:') !!}
    <p>{{ $subject->is_elective }}</p>
</div>

