<!-- Name Field -->
<div class="form-group col-sm-12 mb-4">
    {!! Form::label('name', 'Period Name', ['class' => 'dash-label']) !!}
    {!! Form::text('name', null, ['class' => 'form-control dash-control', 'required', 'placeholder' => 'e.g. First Period, Morning Session, Break', 'maxlength' => 50]) !!}
</div>

<div class="row w-100 m-0">
    <!-- Start Time Field -->
    <div class="form-group col-sm-6 ps-0 mb-3">
        {!! Form::label('start_time', 'Start Time', ['class' => 'dash-label']) !!}
        <div class="input-group dash-time-group">
            <span class="input-group-text bg-white border-end-0"><i class="far fa-clock text-muted"></i></span>
            {!! Form::time('start_time', null, ['class' => 'form-control dash-control border-start-0', 'required']) !!}
        </div>
    </div>

    <!-- End Time Field -->
    <div class="form-group col-sm-6 pe-0 mb-3">
        {!! Form::label('end_time', 'End Time', ['class' => 'dash-label']) !!}
        <div class="input-group dash-time-group">
            <span class="input-group-text bg-white border-end-0"><i class="far fa-clock text-muted"></i></span>
            {!! Form::time('end_time', null, ['class' => 'form-control dash-control border-start-0', 'required']) !!}
        </div>
    </div>
</div>

<style>
.dash-time-group .input-group-text { border-color: var(--border); border-radius: 8px 0 0 8px; font-size: 0.875rem; }
.dash-time-group .dash-control { border-radius: 0 8px 8px 0 !important; }
</style>