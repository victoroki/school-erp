<!-- Name Field -->
<div class="form-group col-12 mb-3">
    {!! Form::label('name', 'Source Name', ['class' => 'form-label-dash']) !!}
    {!! Form::text('name', null, ['class' => 'form-control-dash', 'required', 'placeholder' => 'e.g. Tuition Fees, Grants, Donations', 'maxlength' => 100]) !!}
</div>

<!-- Description Field -->
<div class="form-group col-12">
    {!! Form::label('description', 'Description', ['class' => 'form-label-dash']) !!}
    {!! Form::textarea('description', null, ['class' => 'form-control-dash', 'placeholder' => 'Briefly describe this income source...', 'rows' => 3, 'maxlength' => 65535]) !!}
</div>

<style>
.form-label-dash { font-size: .813rem; font-weight: 700; color: var(--text); margin-bottom: 0.5rem; display: block; }
.form-control-dash { width: 100%; padding: .625rem .875rem; font-size: .875rem; font-weight: 500; color: var(--text); background: #fff; border: 1px solid var(--border); border-radius: 8px; transition: all 150ms var(--ease-out); }
.form-control-dash:focus { outline: none; border-color: var(--emerald); box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1); }
.form-control-dash::placeholder { color: #94a3b8; }
</style>