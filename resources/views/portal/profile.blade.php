@extends('layouts.portal')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">My Profile</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('portal.profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Name</label>
                                    <input type="text" class="form-control" value="{{ $user->name }}" disabled>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>User Type</label>
                                    <input type="text" class="form-control" value="{{ ucfirst($user->user_type) }}" disabled>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Phone</label>
                                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                                </div>
                            </div>
                        </div>

                        @if($profile)
                        <hr>
                        <h5>{{ $user->user_type === 'parent' ? 'Parent Details' : 'Student Details' }}</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Full Name</label>
                                    <input type="text" class="form-control" value="{{ $profile->full_name ?? $profile->first_name . ' ' . $profile->last_name }}" disabled>
                                </div>
                            </div>
                            @if($user->user_type === 'student')
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Admission No</label>
                                    <input type="text" class="form-control" value="{{ $profile->admission_no }}" disabled>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Current Class</label>
                                    <input type="text" class="form-control" value="{{ $profile->current_enrollment?->classSection?->schoolClass?->name ?? 'N/A' }}" disabled>
                                </div>
                            </div>
                            @endif
                            @if($user->user_type === 'parent')
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Relationship</label>
                                    <input type="text" class="form-control" value="{{ $profile->relationship }}" disabled>
                                </div>
                            </div>
                            @endif
                        </div>
                        @endif

                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
