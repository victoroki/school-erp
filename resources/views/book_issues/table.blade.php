<div class="table-responsive">
    <table class="table table-hover table-striped" id="book-issues-table">
        <thead class="bg-light">
        <tr>
            <th style="width: 25%">Book Title</th>
            <th style="width: 20%">Member</th>
            <th style="width: 12%">Issue Date</th>
            <th style="width: 12%">Due Date</th>
            <th style="width: 12%">Return Date</th>
            <th style="width: 8%" class="text-center">Fine</th>
            <th style="width: 8%" class="text-center">Status</th>
            <th class="text-center">Action</th>
        </tr>
        </thead>
        <tbody>
        @forelse($bookIssues as $bookIssue)
            @php
                // A book that is still out past its due date has not been
                // charged yet — fine_amount is only written on return — so the
                // amount it is currently owing is derived from the configured
                // daily rate.
                $daysOverdue = $bookIssue->status === 'issued'
                    ? \App\Services\LibrarySettings::daysOverdue($bookIssue->due_date)
                    : 0;
                $accruingFine = \App\Services\LibrarySettings::fineFor($daysOverdue);
            @endphp
            <tr class="{{ $daysOverdue > 0 ? 'table-danger' : '' }}">
                <td class="align-middle">
                    <strong>{{ $bookIssue->book->title ?? 'N/A' }}</strong>
                    <br><small class="text-muted">ISBN: {{ $bookIssue->book->isbn ?? 'N/A' }}</small>
                </td>
                <td class="align-middle">
                    {{ $bookIssue->member->display_name }}
                    <br><small class="text-muted">{{ $bookIssue->member->reference_id ?? 'N/A' }}</small>
                </td>
                <td class="align-middle">{{ $bookIssue->issue_date->format('d M Y') }}</td>
                <td class="align-middle">
                    {{ $bookIssue->due_date->format('d M Y') }}
                    @if($daysOverdue > 0)
                        <br><span class="badge badge-danger badge-sm">
                            {{ $daysOverdue }} {{ Str::plural('day', $daysOverdue) }} overdue
                        </span>
                    @endif
                </td>
                <td class="align-middle">{{ $bookIssue->return_date ? $bookIssue->return_date->format('d M Y') : '-' }}</td>
                <td class="text-center align-middle">
                    @if((float) $bookIssue->fine_amount > 0)
                        <span class="text-danger font-weight-bold">KES {{ number_format($bookIssue->fine_amount, 2) }}</span>
                    @elseif($accruingFine > 0)
                        <span class="text-danger">KES {{ number_format($accruingFine, 2) }}</span>
                        <br><small class="text-muted">accruing</small>
                    @else
                        -
                    @endif
                </td>
                <td class="text-center align-middle">
                    @php
                        $badgeClass = match($bookIssue->status) {
                            'returned' => 'success',
                            'overdue' => 'danger',
                            'lost' => 'dark',
                            default => 'warning'
                        };
                    @endphp
                    <span class="badge badge-{{ $badgeClass }}">
                        {{ ucfirst($bookIssue->status) }}
                    </span>
                </td>
                <td class="text-center align-middle" style="width: 150px">
                    <div class='btn-group'>
                        @if($bookIssue->status == 'issued' || $bookIssue->status == 'overdue')
                            <a href="{{ route('book-issues.return-modal', $bookIssue->issue_id) }}" class="btn btn-sm btn-info" title="Return Book">
                                <i class="fas fa-undo"></i>
                            </a>
                        @endif
                        <a href="{{ route('book-issues.show', [$bookIssue->issue_id]) }}"
                           class='btn btn-sm btn-default' title="View">
                            <i class="far fa-eye"></i>
                        </a>
                        {!! Form::open(['route' => ['book-issues.destroy', $bookIssue->issue_id], 'method' => 'delete', 'style' => 'display:inline']) !!}
                        {!! Form::button('<i class="far fa-trash-alt"></i>', ['type' => 'submit', 'class' => 'btn btn-sm btn-danger', 'title' => 'Delete', 'onclick' => "return confirm('Are you sure?')"]) !!}
                        {!! Form::close() !!}
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center p-4">
                    <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No book issues found.</p>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
