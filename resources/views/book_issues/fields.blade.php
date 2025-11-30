<!-- Book Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('book_id', 'Book:') !!}
    {!! Form::select('book_id', $books, null, ['class' => 'form-control', 'placeholder' => 'Select Book', 'required']) !!}
</div>

<!-- Member Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('member_id', 'Member:') !!}
    {!! Form::select('member_id', $members, null, ['class' => 'form-control', 'placeholder' => 'Select Member', 'required']) !!}
</div>

<!-- Issue Date Field -->
<div class="form-group col-sm-6">
    {!! Form::label('issue_date', 'Issue Date:') !!}
    {!! Form::text('issue_date', null, ['class' => 'form-control', 'id' => 'issue_date', 'required']) !!}
</div>

<!-- Due Date Field -->
<div class="form-group col-sm-6">
    {!! Form::label('due_date', 'Due Date:') !!}
    {!! Form::text('due_date', null, ['class' => 'form-control', 'id' => 'due_date', 'required']) !!}
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
        {!! Form::label('fine_amount', 'Fine Amount:') !!}
        {!! Form::number('fine_amount', null, ['class' => 'form-control', 'step' => '0.01']) !!}
    </div>
@endif

<!-- Remarks Field -->
<div class="form-group col-sm-12">
    {!! Form::label('remarks', 'Remarks:') !!}
    {!! Form::textarea('remarks', null, ['class' => 'form-control', 'rows' => 3]) !!}
</div>

@push('page_scripts')
    <script type="text/javascript">
        $('#issue_date').datetimepicker({
            format: 'YYYY-MM-DD',
            useCurrent: true,
            sideBySide: true
        })
        $('#due_date').datetimepicker({
            format: 'YYYY-MM-DD',
            useCurrent: false,
            sideBySide: true
        })
        $('#return_date').datetimepicker({
            format: 'YYYY-MM-DD',
            useCurrent: false,
            sideBySide: true
        })
    </script>
@endpush
