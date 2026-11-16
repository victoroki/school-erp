@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1>
                        Edit Emergency Contact
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('adminlte-templates::common.errors')

        <div class="card shadow-sm border-0" style="border-radius: 12px;">

            {!! Form::model($emergencyContact, ['route' => ['emergencyContacts.update', $emergencyContact->emergency_contact_id], 'method' => 'patch']) !!}

            <div class="card-body p-4">
                <div class="row">
                    @include('emergency_contacts.fields')
                </div>
            </div>

            <div class="card-footer bg-light p-3">
                {!! Form::submit('Update Contact', ['class' => 'btn btn-primary px-4 font-weight-bold', 'style' => 'border-radius: 8px;']) !!}
                <a href="{{ route('emergencyContacts.index') }}" class="btn btn-default px-4 ml-2" style="border-radius: 8px;"> Cancel </a>
            </div>

            {!! Form::close() !!}

        </div>
    </div>
@endsection
