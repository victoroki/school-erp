<!-- Class Selection -->
<div class="form-group col-sm-6 mb-4">
    {!! Form::label('class_id', 'Target Class', ['class' => 'dash-label']) !!}
    {!! Form::select('class_id', $classes, null, ['class' => 'form-control dash-control', 'required', 'placeholder' => 'Select Class to Configure']) !!}
</div>

<!-- Academic Year Selection -->
<div class="form-group col-sm-6 mb-4">
    {!! Form::label('academic_year_id', 'Academic Period', ['class' => 'dash-label']) !!}
    {!! Form::select('academic_year_id', $academicYear, null, ['class' => 'form-control dash-control', 'required', 'placeholder'=> 'Select Academic Year']) !!}
</div>

@if(isset($classSubject))
    <!-- Single Subject Assignment (Edit Mode) -->
    <div class="form-group col-sm-12 mb-4">
        {!! Form::label('subject_id', 'Subject', ['class' => 'dash-label']) !!}
        {!! Form::select('subject_id', $subjects, null, ['class' => 'form-control dash-control', 'required', 'placeholder' => 'Select Subject']) !!}
    </div>
@else
    <!-- Bulk Subject Assignment (Create Mode) -->
    <div class="form-group col-sm-12">
        {!! Form::label('subject_id', 'Select Subjects for this Class (Bulk Assignment)', ['class' => 'dash-label mb-3']) !!}
        
        <div class="subject-selection-grid">
            @foreach($subjects as $id => $name)
                <label class="subject-check-card">
                    <input type="checkbox" name="subject_id[]" value="{{ $id }}" class="subject-check-input">
                    <div class="subject-check-content">
                        <span class="subject-check-name">{{ $name }}</span>
                        <span class="subject-check-badge">Curriculum</span>
                    </div>
                </label>
            @endforeach
        </div>
    </div>
@endif

<style>
/* ── Subject Multi-Select Grid ── */
.subject-selection-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem; max-height: 450px; overflow-y: auto; padding-right: 10px; }

.subject-check-card { position: relative; cursor: pointer; margin: 0; }
.subject-check-input { position: absolute; opacity: 0; width: 0; height: 0; }

.subject-check-content { background: #fff; border: 1px solid var(--border); border-radius: 12px; padding: 1rem; transition: all 200ms var(--ease-out); display: flex; flex-direction: column; gap: 0.25rem; }
.subject-check-card:hover .subject-check-content { border-color: var(--indigo); background-color: #f8fafc; }

.subject-check-input:checked + .subject-check-content { background: var(--indigo-light); border-color: var(--indigo); box-shadow: 0 4px 12px rgba(79, 70, 229, 0.1); }

.subject-check-name { font-size: 0.875rem; font-weight: 700; color: var(--text); }
.subject-check-badge { font-size: 0.625rem; font-weight: 800; text-transform: uppercase; color: var(--muted); letter-spacing: 0.05em; }

.subject-check-input:checked + .subject-check-content .subject-check-name { color: var(--indigo); }
.subject-check-input:checked + .subject-check-content .subject-check-badge { color: var(--indigo); opacity: 0.8; }

/* Custom Scrollbar for the grid */
.subject-selection-grid::-webkit-scrollbar { width: 4px; }
.subject-selection-grid::-webkit-scrollbar-track { background: #f1f5f9; }
.subject-selection-grid::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
</style>