@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Message Details</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-default float-right" href="{{ route('communication.history.index') }}">
                        Back to History
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="row">
            <div class="col-md-4">
                <div class="card card-primary card-outline">
                    <div class="card-body box-profile">
                        <h3 class="profile-username text-center">
                            @if($message->message_type == 'SMS')
                                <i class="fas fa-sms text-info"></i> SMS Message
                            @else
                                <i class="fas fa-envelope text-success"></i> Email Message
                            @endif
                        </h3>
                        <p class="text-muted text-center">{{ $message->created_at->format('d M Y, h:i A') }}</p>

                        <ul class="list-group list-group-unbordered mb-3">
                            <li class="list-group-item">
                                <b>Status</b> <a class="float-right">{{ $message->status }}</a>
                            </li>
                            <li class="list-group-item">
                                <b>Sent By</b> <a class="float-right">{{ $message->sender->name ?? 'System' }}</a>
                            </li>
                            <li class="list-group-item">
                                <b>Recipients</b> <a class="float-right">{{ $message->recipient_count }} ({{ $message->recipient_type }})</a>
                            </li>
                            @if($message->cost)
                            <li class="list-group-item">
                                <b>Cost</b> <a class="float-right">{{ $message->cost }}</a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>

                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Content</h3>
                    </div>
                    <div class="card-body">
                        @if($message->subject)
                            <strong>Subject:</strong> {{ $message->subject }}
                            <hr>
                        @endif
                        <p class="text-muted">
                            {!! nl2br(e($message->content)) !!}
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Recipient List</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Contact</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($message->recipients as $recipient)
                                    <tr>
                                        <td>{{ $recipient->recipient_name }}</td>
                                        <td>{{ $recipient->contact }}</td>
                                        <td>{{ $recipient->recipient_type }}</td>
                                        <td>
                                            <span class="badge badge-{{ $recipient->delivery_status == 'Sent' ? 'success' : 'danger' }}">
                                                {{ $recipient->delivery_status }}
                                            </span>
                                        </td>
                                        <td>{{ $recipient->delivery_time ? $recipient->delivery_time->format('H:i:s') : '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer text-center">
                        <a href="#" class="btn btn-sm btn-default disabled">View Full Report</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
