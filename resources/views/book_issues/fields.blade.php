<!-- Book Selection -->
<div class="form-group col-sm-6">
    {!! Form::label('book_id', 'Select Book:') !!}
    {!! Form::select('book_id', $books, null, ['class' => 'form-control select2', 'placeholder' => 'Search for a book...', 'required']) !!}
    <small class="text-muted">Only available books are shown</small>
</div>

<!-- Member Selection -->
<div class="form-group col-sm-6">
    {!! Form::label('member_id', 'Select Member:') !!}
    {!! Form::select('member_id', $members, null, ['class' => 'form-control select2', 'placeholder' => 'Search for a member...', 'required']) !!}
    <small class="text-muted">Only active members are shown</small>
</div>

<!-- Issue Date Field -->
<div class="form-group col-sm-6">
    {!! Form::label('issue_date', 'Issue Date:') !!}
    {!! Form::text('issue_date', \Carbon\Carbon::now()->format('Y-m-d'), ['class' => 'form-control', 'id' => 'issue_date', 'required']) !!}
</div>

<!-- Due Date Field -->
<div class="form-group col-sm-6">
    {!! Form::label('due_date', 'Due Date:') !!}
    {!! Form::text('due_date', \Carbon\Carbon::now()->addDays(14)->format('Y-m-d'), ['class' => 'form-control', 'id' => 'due_date', 'required']) !!}
    <small class="text-muted">Default: 14 days from today</small>
</div>

@if(isset($bookIssue))
    <!-- Return Date Field -->
    <div class="form-group col-sm-6">
        {!! Form::label('return_date', 'Return Date:') !!}
        {!! Form::text('return_date', null, ['class' => 'form-control', 'id' => 'return_date']) !!}
    </div>

    <!-- Status Field -->
    <div class="form-group col-sm-6">
        {!! Form::label('status', 'Status:') !!}
        {!! Form::select('status', ['issued' => 'Issued', 'returned' => 'Returned', 'overdue' => 'Overdue', 'lost' => 'Lost'], null, ['class' => 'form-control', 'required']) !!}
    </div>

    <!-- Fine Amount Field -->
    <div class="form-group col-sm-6">
        {!! Form::label('fine_amount', 'Fine Amount (KSh):') !!}
        {!! Form::number('fine_amount', null, ['class' => 'form-control', 'step' => '0.01', 'min' => '0']) !!}
    </div>
@endif

<!-- Remarks Field -->
<div class="form-group col-sm-12">
    {!! Form::label('remarks', 'Remarks (Optional):') !!}
    {!! Form::textarea('remarks', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Any special notes or conditions...']) !!}
</div>

@push('page_scripts')
    <script type="text/javascript">
        $('#issue_date').datetimepicker({
            format: 'YYYY-MM-DD',
            useCurrent: true,
            sideBySide: true
        });
        
        $('#due_date').datetimepicker({
            format: 'YYYY-MM-DD',
            useCurrent: false,
            sideBySide: true
        });
        
        $('#return_date').datetimepicker({
            format: 'YYYY-MM-DD',
            useCurrent: false,
            sideBySide: true
        });

        // Auto-calculate due date when issue date changes
        $('#issue_date').on('change.datetimepicker', function(e) {
            var issueDate = moment(e.date);
            var dueDate = issueDate.clone().add(14, 'days');
            $('#due_date').datetimepicker('date', dueDate);
        });
    </script>
@endpush
