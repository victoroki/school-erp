@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="text-warning font-weight-bold">
                        <i class="fas fa-bullhorn mr-2"></i> Student Notices
                    </h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('student-notices.create') }}" class="btn btn-primary shadow-sm">
                        <i class="fas fa-plus mr-1"></i> Post Notice
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="clearfix"></div>

        <div class="card card-outline card-primary shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Student</th>
                                <th>Type</th>
                                <th>Title</th>
                                <th>Posted By</th>
                                <th>Posted On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($notices as $notice)
                                <tr>
                                    <td>
                                        <a href="{{ route('students.show', [$notice->student_id]) }}?tab=notices" class="font-weight-bold">
                                            {{ optional($notice->student)->first_name }} {{ optional($notice->student)->last_name }}
                                        </a>
                                        <br>
                                        <small class="text-muted">{{ optional($notice->student)->admission_no }}</small>
                                    </td>
                                    <td>
                                        @php
                                            $badgeClass = [
                                                'general'    => 'badge-primary',
                                                'behavior'   => 'badge-warning',
                                                'academic'   => 'badge-info',
                                                'medical'    => 'badge-danger',
                                                'attendance' => 'badge-secondary',
                                                'other'      => 'badge-dark',
                                            ][$notice->notice_type] ?? 'badge-secondary';
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ ucfirst($notice->notice_type) }}</span>
                                    </td>
                                    <td title="{{ $notice->body }}">
                                        <a href="{{ route('student-notices.show', [$notice->id]) }}" class="font-weight-bold">
                                            {{ $notice->title }}
                                        </a>
                                    </td>
                                    <td>{{ optional($notice->creator)->name }}</td>
                                    <td>{{ $notice->created_at->format('d M, Y') }}</td>
                                    <td>
                                        <form action="{{ route('student-notices.destroy', [$notice->id]) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete this notice?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light border text-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="fas fa-bullhorn mr-1"></i> No notices posted yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer clearfix bg-white">
                <div class="float-right">
                    {{ $notices->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection