<!-- Title Field -->
<div class="form-group col-sm-6">
    {!! Form::label('title', 'Template Title:') !!}
    {!! Form::text('title', null, ['class' => 'form-control', 'required', 'maxlength' => 100, 'placeholder' => 'e.g. Fee Reminder']) !!}
</div>

<!-- Category Field -->
<div class="form-group col-sm-6">
    {!! Form::label('category', 'Category:') !!}
    {!! Form::select('category', $categories['categories'] ?? ['General' => 'General', 'Fee' => 'Fee', 'Exam' => 'Exam', 'Attendance' => 'Attendance'], null, ['class' => 'form-control select2']) !!}
</div>

<!-- Content Field -->
<div class="form-group col-sm-12">
    {!! Form::label('content', 'SMS Content:') !!}
    {!! Form::textarea('content', null, ['class' => 'form-control', 'required', 'rows' => 4, 'placeholder' => 'Type your message here...', 'id' => 'content_area']) !!}
    <small class="text-muted float-right" id="char_counter">0 chars</small>
</div>

<!-- Variables Field -->
<div class="form-group col-sm-12">
    <label>Available Placeholders (Click to insert):</label>
    <div class="btn-group d-block">
        <button type="button" class="btn btn-xs btn-outline-info insert-var" data-var="{student_name}">{student_name}</button>
        <button type="button" class="btn btn-xs btn-outline-info insert-var" data-var="{parent_name}">{parent_name}</button>
        <button type="button" class="btn btn-xs btn-outline-info insert-var" data-var="{class}">{class}</button>
        <button type="button" class="btn btn-xs btn-outline-info insert-var" data-var="{fee_balance}">{fee_balance}</button>
        <button type="button" class="btn btn-xs btn-outline-info insert-var" data-var="{date}">{date}</button>
    </div>
    {!! Form::hidden('variables', null, ['id' => 'variables_field']) !!}
</div>

<!-- Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('status', 'Status:') !!}
    <div class="btn-group btn-group-toggle" data-toggle="buttons">
        <label class="btn btn-outline-success {{ (isset($smsTemplate) && $smsTemplate->status == 'active') ? 'active' : '' }}">
            <input type="radio" name="status" value="active" {{ (isset($smsTemplate) && $smsTemplate->status == 'active') ? 'checked' : '' }}> Active
        </label>
        <label class="btn btn-outline-secondary {{ (isset($smsTemplate) && $smsTemplate->status == 'inactive') ? 'active' : '' }}">
            <input type="radio" name="status" value="inactive" {{ (isset($smsTemplate) && $smsTemplate->status == 'inactive') ? 'checked' : '' }}> Inactive
        </label>
    </div>
</div>

@push('page_scripts')
<script>
    $(document).ready(function() {
        // Char counter
        $('#content_area').on('input', function() {
            var len = $(this).val().length;
            $('#char_counter').text(len + ' chars (' + Math.ceil(len/160) + ' SMS)');
        });

        // Insert placeholder
        $('.insert-var').click(function() {
            var text = $(this).data('var');
            var $txt = $("#content_area");
            var caretPos = $txt[0].selectionStart;
            var textAreaTxt = $txt.val();
            $txt.val(textAreaTxt.substring(0, caretPos) + text + textAreaTxt.substring(caretPos) );
            $txt.trigger('input');
        });
    });
</script>
@endpush