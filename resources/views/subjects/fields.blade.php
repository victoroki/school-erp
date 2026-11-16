<!-- Subject Code Field -->
<div class="form-group col-sm-6 mb-3">
    {!! Form::label('subject_code', 'Subject Code', ['class' => 'dash-label']) !!}
    {!! Form::text('subject_code', null, ['class' => 'form-control dash-control', 'required', 'placeholder' => 'e.g. MATH101', 'maxlength' => 20]) !!}
</div>

<!-- Name Field -->
<div class="form-group col-sm-6 mb-3">
    {!! Form::label('name', 'Subject Name', ['class' => 'dash-label']) !!}
    {!! Form::text('name', null, ['class' => 'form-control dash-control', 'required', 'placeholder' => 'e.g. Mathematics', 'maxlength' => 100]) !!}
</div>

<!-- Description Field -->
<div class="form-group col-sm-12 mb-3">
    {!! Form::label('description', 'Subject Description', ['class' => 'dash-label']) !!}
    {!! Form::textarea('description', null, ['class' => 'form-control dash-control', 'placeholder' => 'Brief overview of the subject curriculum...', 'rows' => 3]) !!}
</div>

<!-- Is Elective Field -->
<div class="form-group col-sm-12 mb-3">
    <div class="dash-check-wrap">
        <label class="dash-check-container">
            {!! Form::hidden('is_elective', 0) !!}
            {!! Form::checkbox('is_elective', '1', null, ['class' => 'dash-check-input']) !!}
            <span class="dash-check-label">Mark as Elective Subject</span>
            <span class="dash-check-description">Elective subjects are optional for students to choose.</span>
        </label>
    </div>
</div>

<style>
/* ── Checkbox Styling ── */
.dash-check-wrap { background: #f8fafc; border: 1px solid var(--border); border-radius: 12px; padding: 1.25rem; }
.dash-check-container { display: flex; align-items: flex-start; gap: 0.75rem; cursor: pointer; margin-bottom: 0; }
.dash-check-input { width: 1.25rem; height: 1.25rem; border-radius: 6px; border: 2px solid var(--border); appearance: none; position: relative; cursor: pointer; transition: all 0.2s var(--ease-out); flex-shrink: 0; margin-top: 2px; }
.dash-check-input:checked { background-color: var(--indigo); border-color: var(--indigo); }
.dash-check-input:checked::after { content: '✓'; position: absolute; color: #fff; font-size: 0.875rem; font-weight: 900; left: 50%; top: 50%; transform: translate(-50%, -50%); }
.dash-check-label { font-size: 0.875rem; font-weight: 700; color: var(--text); display: block; line-height: 1.4; }
.dash-check-description { font-size: 0.75rem; color: var(--muted); font-weight: 500; display: block; margin-top: 0.125rem; }
</style>