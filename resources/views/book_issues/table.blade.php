<div class="table-responsive">
    <table class="table" id="book-issues-table">
        <thead>
        <tr>
            <th>Book</th>
            <th>Member</th>
            <th>Issue Date</th>
            <th>Due Date</th>
            <th>Return Date</th>
            <th>Status</th>
            <th colspan="3">Action</th>
        </tr>
        </thead>
        <tbody>
        @foreach($bookIssues as $bookIssue)
            <tr>
                <td>{{ $bookIssue->book->title ?? 'N/A' }}</td>
                <td>{{ $bookIssue->member->reference_id ?? 'N/A' }}</td>
                <td>{{ $bookIssue->issue_date->format('d M Y') }}</td>
                <td>{{ $bookIssue->due_date->format('d M Y') }}</td>
                <td>{{ $bookIssue->return_date ? $bookIssue->return_date->format('d M Y') : '-' }}</td>
                <td>
                    <span class="badge badge-{{ $bookIssue->status == 'returned' ? 'success' : ($bookIssue->status == 'overdue' ? 'danger' : 'warning') }}">
                        {{ ucfirst($bookIssue->status) }}
                    </span>
                </td>
                <td width="120">
                    {!! Form::open(['route' => ['book-issues.destroy', $bookIssue->issue_id], 'method' => 'delete']) !!}
                    <div class='btn-group'>
                        <a href="{{ route('book-issues.show', [$bookIssue->issue_id]) }}"
                           class='btn btn-default btn-xs'>
                            <i class="far fa-eye"></i>
                        </a>
                        <a href="{{ route('book-issues.edit', [$bookIssue->issue_id]) }}"
                           class='btn btn-default btn-xs'>
                            <i class="far fa-edit"></i>
                        </a>
                        {!! Form::button('<i class="far fa-trash-alt"></i>', ['type' => 'submit', 'class' => 'btn btn-danger btn-xs', 'onclick' => "return confirm('Are you sure?')"]) !!}
                    </div>
                    {!! Form::close() !!}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
