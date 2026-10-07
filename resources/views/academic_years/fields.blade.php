<!-- Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('name', 'Name:') !!}
    {!! Form::text('name', null, ['class' => 'form-control', 'required', 'maxlength' => 50, 'maxlength' => 50]) !!}
</div>

<!-- Start Date Field -->
<div class="form-group col-sm-6">
    {!! Form::label('start_date', 'Start Date:') !!}
    {!! Form::date('start_date', isset($academicYear) && $academicYear->start_date ? $academicYear->start_date->format('Y-m-d') : null, ['class' => 'form-control','id'=>'start_date']) !!}
</div>

<!-- End Date Field -->
<div class="form-group col-sm-6">
    {!! Form::label('end_date', 'End Date:') !!}
    {!! Form::date('end_date', isset($academicYear) && $academicYear->end_date ? $academicYear->end_date->format('Y-m-d') : null, ['class' => 'form-control','id'=>'end_date']) !!}
</div>



<!-- Is Current Field -->
<div class="form-group col-sm-6">
    <div class="form-check">
        {!! Form::hidden('is_current', 0, ['class' => 'form-check-input']) !!}
        {!! Form::checkbox('is_current', '1', null, ['class' => 'form-check-input']) !!}
        {!! Form::label('is_current', 'Is Current', ['class' => 'form-check-label']) !!}
    </div>
</div>

{{-- Terms are generated on save. Saying so up front removes the surprise that
     used to follow: an academic year would save fine and then fail to assign any
     fees with "No terms are defined for the selected academic year". --}}
@if(! isset($academicYear))
    <div class="form-group col-12">
        <div class="alert alert-info mb-0 d-flex align-items-start" style="gap:.75rem;">
            <i class="fas fa-calendar-alt mt-1"></i>
            <div>
                <strong class="d-block mb-1">Terms are created automatically</strong>
                @if(! empty($willCopyTerms))
                    <span class="d-block">
                        The shape of {{ $previousYear->name }} &mdash;
                        {{ $previousYear->terms()->count() }}
                        {{ Str::plural('term', $previousYear->terms()->count()) }} &mdash;
                        will be copied across and its dates remapped onto the new year,
                        so a term pattern you have already customised carries forward.
                    </span>
                @else
                    <span class="d-block">
                        No earlier year with terms was found, so the new year will be
                        split into {{ \App\Services\AcademicCalendarService::DEFAULT_TERM_COUNT }} equal terms.
                    </span>
                @endif
                <span class="d-block mt-1">
                    Each term's status follows its own dates, so terms mark themselves
                    completed or active as the year passes.
                </span>
            </div>
        </div>
    </div>
@endif