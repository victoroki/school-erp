@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Message History</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('communication.compose') }}" class="btn btn-primary">Compose New</a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Sent Messages Log</h3>
                <div class="card-tools">
                    <div class="input-group input-group-sm" style="width: 150px;">
                        <input type="text" name="table_search" class="form-control float-right" placeholder="Search">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-default"><i class="fas fa-search"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Subject / Content</th>
                            <th>Recipients</th>
                            <th>Sent By</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($history as $message)
                            <tr>
                                <td>{{ $message->created_at->format('d M Y, h:i A') }}</td>
                                <td>
                                    <span class="badge badge-{{ $message->message_type == 'SMS' ? 'info' : 'success' }}">
                                        {{ $message->message_type }}
                                    </span>
                                </td>
                                <td>
                                    @if($message->message_type == 'Email')
                                        <strong>{{ Str::limit($message->subject, 30) }}</strong><br>
                                    @endif
                                    <small class="text-muted">{{ Str::limit($message->content, 50) }}</small>
                                </td>
                                <td>
                                    {{ $message->recipient_count }} 
                                    <small>({{ $message->recipient_type }})</small>
                                </td>
                                <td>{{ $message->sender->name ?? 'System' }}</td>
                                <td>
                                    <span class="badge badge-{{ $message->status == 'Sent' ? 'success' : ($message->status == 'Failed' ? 'danger' : 'warning') }}">
                                        {{ $message->status }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('communication.history.show', $message->id) }}" class="btn btn-xs btn-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No messages sent yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer clearfix">
                <div class="float-right">
                    {{ $history->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
