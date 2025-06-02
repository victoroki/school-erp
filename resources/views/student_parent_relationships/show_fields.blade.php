<!-- Student Id Field -->
<div class="col-sm-12">
    {!! Form::label('student_id', 'Student Id:') !!}
    <p>{{ $studentParentRelationship->student_id }}</p>
</div>

<!-- Parent Id Field -->
<div class="col-sm-12">
    {!! Form::label('parent_id', 'Parent Id:') !!}
    <p>{{ $studentParentRelationship->parent_id }}</p>
</div>

<!-- Is Primary Contact Field -->
<div class="col-sm-12">
    {!! Form::label('is_primary_contact', 'Is Primary Contact:') !!}
    <p>{{ $studentParentRelationship->is_primary_contact }}</p>
</div>

