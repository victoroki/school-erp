<!-- Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('name', 'Name:') !!}
    {!! Form::text('name', null, ['class' => 'form-control', 'required', 'maxlength' => 100]) !!}
</div>

<!-- Code Field -->
<div class="form-group col-sm-6">
    {!! Form::label('code', 'Code:') !!}
    <div class="input-group">
        {!! Form::text('code', null, ['class' => 'form-control', 'maxlength' => 20, 'id' => 'code-field']) !!}
        <div class="input-group-append">
            <button type="button" class="btn btn-info" id="auto-code-btn" title="Generate auto code">
                <i class="fas fa-magic"></i> Auto
            </button>
        </div>
    </div>
    <small class="form-text text-muted">Click "Auto" to generate code from name</small>
</div>

<!-- Type Field -->
<div class="form-group col-sm-6">
    {!! Form::label('type', 'Type:') !!}
    {!! Form::select('type', ['mandatory' => 'Mandatory', 'optional' => 'Optional'], null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('status', 'Status:') !!}
    {!! Form::select('status', ['active' => 'Active', 'inactive' => 'Inactive'], null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Description Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('description', 'Description:') !!}
    {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => 3]) !!}
</div>