@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>User Details</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-default float-right"
                       href="{{ route('users.index') }}">
                        Back
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-12">
                        {!! Form::label('name', 'Name:') !!}
                        <p>{{ $user->name }}</p>
                    </div>
                    <div class="col-sm-12">
                        {!! Form::label('email', 'Email:') !!}
                        <p>{{ $user->email }}</p>
                    </div>
                    <div class="col-sm-12">
                        {!! Form::label('roles', 'Roles:') !!}
                        <p>
                            @foreach($user->roles as $role)
                                <span class="badge badge-success">{{ $role->role_name }}</span>
                            @endforeach
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
