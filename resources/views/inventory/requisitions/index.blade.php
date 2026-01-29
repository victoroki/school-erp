@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-file-signature text-info mr-2"></i>My Requisitions</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a class="btn btn-primary" href="{{ route('inventory.requisitions.create') }}">
                        <i class="fas fa-plus mr-1"></i> New Request
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light small uppercase">
                            <tr>
                                <th class="pl-4">REQ #</th>
                                <th>Requested By</th>
                                <th>Department</th>
                                <th>Needed By</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th class="text-right pr-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requisitions as $requisition)
                                <tr>
                                    <td class="pl-4 font-weight-bold">{{ $requisition->requisition_number }}</td>
                                    <td>{{ $requisition->requestedBy->name }}</td>
                                    <td><span class="badge badge-light border">{{ $requisition->department->name }}</span></td>
                                    <td>{{ $requisition->date_needed->format('d M Y') }}</td>
                                    <td>
                                        <span class="text-{{ $requisition->priority == 'Urgent' ? 'danger' : ($requisition->priority == 'High' ? 'warning' : 'primary') }} font-weight-bold">
                                            {{ $requisition->priority }}
                                        </span>
                                    </td>
                                    <td>{!! $requisition->status_badge !!}</td>
                                    <td class="text-right pr-4">
                                        <a href="{{ route('inventory.requisitions.show', $requisition->requisition_id) }}" class="btn btn-default btn-sm shadow-sm">
                                            <i class="fas fa-eye text-primary"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">No requisitions found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white border-top-0">
                <div class="d-flex justify-content-center">
                    {{ $requisitions->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
