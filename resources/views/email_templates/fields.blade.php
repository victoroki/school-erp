<!-- Title Field -->
<div class="form-group col-sm-6">
    {!! Form::label('title', 'Template Title:') !!}
    {!! Form::text('title', null, ['class' => 'form-control', 'required', 'maxlength' => 100, 'placeholder' => 'e.g. End of Term Report']) !!}
</div>

<!-- Category Field -->
<div class="form-group col-sm-6">
    {!! Form::label('category', 'Category:') !!}
    {!! Form::select('category', $categories['categories'] ?? ['General' => 'General', 'Fee' => 'Fee', 'Exam' => 'Exam'], null, ['class' => 'form-control select2']) !!}
</div>

<!-- Subject Field -->
<div class="form-group col-sm-12">
    {!! Form::label('subject', 'Email Subject:') !!}
    {!! Form::text('subject', null, ['class' => 'form-control', 'required', 'maxlength' => 255, 'placeholder' => 'Enter Subject Line']) !!}
</div>

<!-- Content Field -->
<div class="form-group col-sm-12">
    {!! Form::label('content', 'Email Body:') !!}
    {!! Form::textarea('content', null, ['class' => 'form-control', 'required', 'rows' => 10, 'id' => 'content_editor']) !!}
</div>

<!-- Variables Field -->
<div class="form-group col-sm-12">
    <label>Available Placeholders (Copy to use):</label>
    <div class="p-2 bg-light border rounded">
        <code class="mr-2">{student_name}</code>
        <code class="mr-2">{student_class}</code>
        <code class="mr-2">{school_name}</code>
    </div>
    <small class="form-text text-muted">
        These three are the only ones Compose supplies. {student_name} and {student_class} are filled for
        the All Students, Class and Class Section groups, but the All Parents and All Staff groups carry
        no per-person details, so a template using them should only be sent to a student group. Any other
        token is sent to the recipient as literal text.
    </small>
    {!! Form::hidden('variables', null) !!}
</div>

<!-- Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('status', 'Status:') !!}
    <div class="btn-group btn-group-toggle" data-toggle="buttons">
        <label class="btn btn-outline-success {{ (isset($emailTemplate) && $emailTemplate->status == 'active') ? 'active' : '' }}">
            <input type="radio" name="status" value="active" {{ (isset($emailTemplate) && $emailTemplate->status == 'active') ? 'checked' : '' }}> Active
        </label>
        <label class="btn btn-outline-secondary {{ (isset($emailTemplate) && $emailTemplate->status == 'inactive') ? 'active' : '' }}">
            <input type="radio" name="status" value="inactive" {{ (isset($emailTemplate) && $emailTemplate->status == 'inactive') ? 'checked' : '' }}> Inactive
        </label>
    </div>
</div>