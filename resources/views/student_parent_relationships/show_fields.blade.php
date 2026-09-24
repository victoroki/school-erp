<!-- Student Id Field -->
<div class="col-sm-12">
    {!! Form::label('student_id', 'Student:') !!}
    <p>{{ $studentParentRelationship->student->full_name ?? 'N/A' }}</p>
</div>

<!-- Parent Id Field -->
<div class="col-sm-12">
    {!! Form::label('parent_id', 'Parent/Guardian:') !!}
    <p>{{ $studentParentRelationship->parent->full_name ?? 'N/A' }}</p>
</div>

<!-- Is Primary Contact Field -->
<div class="col-sm-12">
    {!! Form::label('is_primary_contact', 'Is Primary Contact:') !!}
    <p>{{ $studentParentRelationship->is_primary_contact ? 'Yes' : 'No' }}</p>
</div>

