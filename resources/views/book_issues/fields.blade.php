<!-- Book Selection -->
<div class="form-group col-sm-6">
    {!! Form::label('book_id', 'Select Book:') !!}
    {!! Form::select('book_id', $books, null, [
        'class' => 'form-control select2 js-library-search',
        'id' => 'book_id',
        'data-search-placeholder' => 'Type a title, author or ISBN…',
        'required',
    ]) !!}
    <small class="text-muted">Only books with copies available are listed</small>
</div>

<!-- Member Selection -->
<div class="form-group col-sm-6">
    {!! Form::label('member_id', 'Select Member:') !!}
    {!! Form::select('member_id', $members, null, [
        'class' => 'form-control select2 js-library-search',
        'id' => 'member_id',
        'data-search-placeholder' => 'Type a name or membership no.…',
        'required',
    ]) !!}
    <small class="text-muted">Only active library members are listed</small>
</div>

<!-- Issue Date Field -->
<div class="form-group col-sm-6">
    {!! Form::label('issue_date', 'Issue Date:') !!}
    {!! Form::date('issue_date', \Carbon\Carbon::now()->format('Y-m-d'), ['class' => 'form-control', 'id' => 'issue_date', 'required']) !!}
</div>

<!-- Due Date Field -->
<div class="form-group col-sm-6">
    {!! Form::label('due_date', 'Due Date:') !!}
    {!! Form::date('due_date', \Carbon\Carbon::now()->addDays($loanPeriodDays ?? 14)->format('Y-m-d'), [
        'class' => 'form-control',
        'id' => 'due_date',
        'data-loan-days' => $loanPeriodDays ?? 14,
        'required',
    ]) !!}
    <small class="text-muted" id="due_date_hint">
        Default: {{ $loanPeriodDays ?? 14 }} days from the issue date
        @can('library.manage')
            &middot; <a href="{{ route('library.settings.edit') }}">change</a>
        @endcan
    </small>
</div>

@if(isset($bookIssue))
    <!-- Return Date Field -->
    <div class="form-group col-sm-6">
        {!! Form::label('return_date', 'Return Date:') !!}
        {!! Form::date('return_date', null, ['class' => 'form-control', 'id' => 'return_date']) !!}
    </div>

    <!-- Status Field -->
    <div class="form-group col-sm-6">
        {!! Form::label('status', 'Status:') !!}
        {!! Form::select('status', ['issued' => 'Issued', 'returned' => 'Returned', 'overdue' => 'Overdue', 'lost' => 'Lost'], null, ['class' => 'form-control', 'required']) !!}
    </div>

    <!-- Fine Amount Field -->
    <div class="form-group col-sm-6">
        {!! Form::label('fine_amount', 'Fine Amount (KES):') !!}
        {!! Form::number('fine_amount', null, ['class' => 'form-control', 'step' => '0.01', 'min' => '0']) !!}
    </div>
@endif

<!-- Remarks Field -->
<div class="form-group col-sm-12">
    {!! Form::label('remarks', 'Remarks (Optional):') !!}
    {!! Form::textarea('remarks', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Any special notes or conditions...']) !!}
</div>

<style>
    /* select2's own markup sits outside the form-control styles, so give it the
       same box the rest of the AdminLTE inputs use. */
    .select2-container--default .select2-selection--single {
        height: calc(1.5em + .75rem + 2px);
        border-color: #ced4da;
        border-radius: .25rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: calc(1.5em + .75rem);
        color: #495057;
        padding-left: .75rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #6c757d;
    }
    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #80bdff;
        box-shadow: 0 0 0 .2rem rgba(0, 123, 255, .25);
    }
    .select2-container--default .select2-search__field {
        border: 1px solid #ced4da;
        border-radius: .25rem;
        padding: .25rem .5rem;
    }
    /* Long titles should ellipsize rather than stretch the dropdown. */
    .select2-container--default .select2-selection__rendered {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .select2-dropdown {
        border-color: #ced4da;
        z-index: 1050;
    }
</style>

@push('page_scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Both jQuery (bundled as an ES module) and select2 (loaded with `defer`)
    // are absent while this file is parsed and only exist once deferred work
    // has run, so every call has to wait. The previous version called into
    // jQuery and a `datetimepicker` plugin that is not installed in this project
    // straight from the parser, which threw on the first line and silently
    // killed the rest of the block.
    if (!window.jQuery) { return; }

    var $ = window.jQuery;
    var attempts = 0;

    function whenReady(done) {
        if ($.fn.select2) { done(); return; }
        if (++attempts > 50) { return; } // ~5s: a blocked CDN must not hang the page
        window.setTimeout(function () { whenReady(done); }, 100);
    }

    whenReady(function () {
        // `theme: 'default'` is deliberate. The layout only ships core select2
        // CSS plus the bootstrap-5 theme, and this project runs AdminLTE 3 on
        // Bootstrap 4 — asking for the bootstrap4 theme renders an unstyled
        // dropdown because that stylesheet is not loaded here.
        $('.js-library-search').select2({
            theme: 'default',
            width: '100%',
            allowClear: false,
            // Search from the first keystroke instead of after 2 characters.
            minimumInputLength: 0,
            placeholder: function () {
                return $(this).data('search-placeholder') || 'Search…';
            }
        });

        // Each select gets its own hint so the user knows what is searchable:
        // books are indexed by title and ISBN, members by name and reference id.
        $('.js-library-search').each(function () {
            var $select = $(this);
            var label = $select.data('search-placeholder') || 'Search…';

            $select.on('select2:open', function () {
                var $field = $select.data('select2').$container
                    .find('.select2-search__field');
                $field.attr('placeholder', label);
            });
        });
    });

    // Due date follows the issue date by the standard loan period. Done with
    // plain date arithmetic so it needs no date plugin.
    var issueDate = document.getElementById('issue_date');
    var dueDate = document.getElementById('due_date');
    var dueHint = document.getElementById('due_date_hint');
    // From the configured loan period, so changing it in library settings
    // does not leave this form suggesting the old number.
    var LOAN_DAYS = parseInt(dueDate.getAttribute('data-loan-days'), 10) || 14;

    if (!issueDate || !dueDate) { return; }

    function syncDueDate() {
        if (!issueDate.value) { return; }

        var start = new Date(issueDate.value + 'T00:00:00');
        if (isNaN(start.getTime())) { return; }

        var due = new Date(start.getTime());
        due.setDate(due.getDate() + LOAN_DAYS);

        // toISOString is UTC, which can land on the previous day east of
        // Greenwich. Build the string from local parts instead.
        var month = String(due.getMonth() + 1).padStart(2, '0');
        var day = String(due.getDate()).padStart(2, '0');

        dueDate.value = due.getFullYear() + '-' + month + '-' + day;

        if (dueHint) {
            dueHint.textContent = LOAN_DAYS + ' days after the issue date.';
        }
    }

    issueDate.addEventListener('change', syncDueDate);
});
</script>
@endpush

