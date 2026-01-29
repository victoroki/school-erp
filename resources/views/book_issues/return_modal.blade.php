@extends('layouts.app')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Return Book</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('book-issues.index') }}" class="btn btn-default">Back to List</a>
            </div>
        </div>
    </div>
</section>

<div class="content px-3">
    <div class="row">
        <div class="col-md-6 mx-auto">
            <div class="card shadow-sm border-top border-info">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">Process Return</h3>
                </div>
                <form action="{{ route('book-issues.return', $issue->issue_id) }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-12 bg-light p-3 rounded border">
                                <h6 class="text-primary font-weight-bold border-bottom pb-1 mb-2">Book Details</h6>
                                <p class="mb-1"><strong>Title:</strong> {{ $issue->book->title }}</p>
                                <p class="mb-1"><strong>Member:</strong> {{ $issue->member->user->name ?? 'N/A' }} ({{ $issue->member->reference_id }})</p>
                                <p class="mb-0"><strong>Due Date:</strong> {{ $issue->due_date->format('d M Y') }}</p>
                            </div>
                        </div>

                        @if($fine > 0)
                            <div class="alert alert-warning border-left shadow-sm">
                                <h5><i class="icon fas fa-exclamation-triangle"></i> Overdue Notice!</h5>
                                This book is <strong>{{ $diff }} days overdue</strong>. 
                                <br>Provisional Fine: <span class="badge badge-danger text-lg px-3 ml-2">KSh {{ number_format($fine, 2) }}</span>
                                <p class="small mt-2 mb-0">Fine is calculated at KSh 50 per day.</p>
                            </div>
                        @else
                            <div class="alert alert-success border-left shadow-sm">
                                <i class="icon fas fa-check"></i> This book is being returned on time. No fines applicable.
                            </div>
                        @endif

                        <div class="form-group mt-3">
                            <label for="return_date">Actual Return Date:</label>
                            <input type="text" name="return_date" id="return_date" class="form-control" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" readonly>
                        </div>

                        <div class="form-group">
                            <label for="remarks">Remarks/Condition:</label>
                            <textarea name="remarks" id="remarks" rows="3" class="form-control" placeholder="Note any damages or missing pages..."></textarea>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-success px-4" onclick="return confirm('Process this return?')">
                            <i class="fas fa-check-circle mr-1"></i> Confirm Return
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
