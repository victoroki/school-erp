@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Onboarding Checklist: {{ $staff->full_name }}</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('hr.onboarding') }}" class="btn btn-default">
                        <i class="fas fa-arrow-left"></i> Back to Onboarding
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4">
                    <div class="card card-primary card-outline">
                        <div class="card-body box-profile">
                            <div class="text-center">
                                <img class="profile-user-img img-fluid img-circle"
                                     src="{{ $staff->photo_url ?? asset('images/default-avatar.png') }}"
                                     alt="Staff photo">
                            </div>
                            <h3 class="profile-username text-center">{{ $staff->full_name }}</h3>
                            <p class="text-muted text-center">{{ $staff->jobPosition->title ?? 'N/A' }}</p>
                            
                            <ul class="list-group list-group-unbordered mb-3">
                                <li class="list-group-item">
                                    <b>Department</b> <a class="float-right">{{ $staff->department->name ?? 'N/A' }}</a>
                                </li>
                                <li class="list-group-item">
                                    <b>Employee ID</b> <a class="float-right">{{ $staff->employee_number }}</a>
                                </li>
                                <li class="list-group-item">
                                    <b>Joining Date</b> <a class="float-right">{{ $staff->date_of_joining ? $staff->date_of_joining->format('d M, Y') : 'N/A' }}</a>
                                </li>
                            </ul>

                            <div class="text-center">
                                <h5>Onboarding Progress</h5>
                                <div class="progress">
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         style="width: {{ $completionPercentage }}%" 
                                         aria-valuenow="{{ $completionPercentage }}" 
                                         aria-valuemin="0" aria-valuemax="100">
                                        {{ $completionPercentage }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-primary">
                            <h3 class="card-title">Onboarding Tasks</h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th style="width: 10px">#</th>
                                        <th>Task</th>
                                        <th>Status</th>
                                        <th>Completed On</th>
                                        <th style="width: 40px">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($staff->onboardingChecklist as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $item->checklist_item }}</td>
                                            <td>
                                                @if($item->is_completed)
                                                    <span class="badge badge-success">Completed</span>
                                                @else
                                                    <span class="badge badge-warning">Pending</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $item->completed_date ? $item->completed_date->format('d M, Y H:i') : '-' }}
                                            </td>
                                            <td>
                                                @if(!$item->is_completed)
                                                    <form action="{{ route('hr.onboarding.complete', [$staff->staff_id, $item->id]) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="btn btn-xs btn-success" title="Mark as Completed">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <button class="btn btn-xs btn-success disabled">
                                                        <i class="fas fa-check-double"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
