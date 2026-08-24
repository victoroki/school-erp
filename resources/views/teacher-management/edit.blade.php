@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>
                        <i class="fas fa-user-edit mr-2 text-info"></i>
                        Edit Teacher
                    </h1>
                    <p class="text-muted mb-0">Update {{ $teacher->full_name }}'s staff record. Login account and role stay managed under User Management.</p>
                </div>
                <div class="col-sm-6">
                    <a href="{{ route('teacher-management.show', $teacher->staff_id) }}" class="btn btn-outline-info float-right">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Profile
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')
        @include('adminlte-templates::common.errors')

        {!! Form::model($teacher, ['route' => ['teacher-management.update', $teacher->staff_id], 'method' => 'put']) !!}

        <div class="row">
            {{-- Personal Information --}}
            <div class="col-md-6">
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-id-badge mr-1"></i> Personal Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-sm-6">
                                {!! Form::label('first_name', 'First Name:') !!}
                                {!! Form::text('first_name', null, ['class' => 'form-control', 'required', 'maxlength' => 50]) !!}
                                @error('first_name')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group col-sm-6">
                                {!! Form::label('middle_name', 'Middle Name:') !!}
                                {!! Form::text('middle_name', null, ['class' => 'form-control', 'maxlength' => 50]) !!}
                                @error('middle_name')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group col-sm-6">
                                {!! Form::label('last_name', 'Last Name:') !!}
                                {!! Form::text('last_name', null, ['class' => 'form-control', 'required', 'maxlength' => 50]) !!}
                                @error('last_name')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group col-sm-6">
                                {!! Form::label('date_of_birth', 'Date Of Birth:') !!}
                                {!! Form::date('date_of_birth', null, ['class' => 'form-control', 'required']) !!}
                                @error('date_of_birth')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group col-sm-6">
                                {!! Form::label('gender', 'Gender:') !!}
                                {!! Form::select('gender', ['' => '— Select Gender —', 'male' => 'Male', 'female' => 'Female', 'other' => 'Other'], null, ['class' => 'form-control', 'required']) !!}
                                @error('gender')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group col-sm-6">
                                {!! Form::label('phone_primary', 'Phone (Primary):') !!}
                                {!! Form::tel('phone_primary', null, ['class' => 'form-control', 'required', 'maxlength' => 20]) !!}
                                @error('phone_primary')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Employment Information --}}
            <div class="col-md-6">
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-briefcase mr-1"></i> Employment Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-sm-6">
                                {!! Form::label('employee_number', 'Employee Number:') !!}
                                {!! Form::text('employee_number', null, ['class' => 'form-control', 'placeholder' => 'e.g. T-1001', 'maxlength' => 20]) !!}
                                @error('employee_number')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group col-sm-6">
                                {!! Form::label('tsc_number', 'TSC Number:') !!}
                                {!! Form::text('tsc_number', null, ['class' => 'form-control', 'maxlength' => 20]) !!}
                                @error('tsc_number')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group col-sm-6">
                                {!! Form::label('department_id', 'Department:') !!}
                                {!! Form::select('department_id', $departments, null, ['class' => 'form-control', 'placeholder' => '— Select Department —']) !!}
                                @error('department_id')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group col-sm-6">
                                {!! Form::label('job_position_id', 'Job Position:') !!}
                                {!! Form::select('job_position_id', $jobPositions, null, ['class' => 'form-control', 'placeholder' => '— Select Position —']) !!}
                                @error('job_position_id')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group col-sm-6">
                                {!! Form::label('designation', 'Designation:') !!}
                                {!! Form::text('designation', null, ['class' => 'form-control', 'maxlength' => 100]) !!}
                                @error('designation')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group col-sm-6">
                                {!! Form::label('qualification', 'Qualification:') !!}
                                {!! Form::text('qualification', null, ['class' => 'form-control', 'maxlength' => 255]) !!}
                                @error('qualification')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group col-sm-6">
                                {!! Form::label('date_of_joining', 'Date Of Joining:') !!}
                                {!! Form::date('date_of_joining', null, ['class' => 'form-control']) !!}
                                @error('date_of_joining')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group col-sm-6">
                                {!! Form::label('employment_type', 'Employment Type:') !!}
                                {!! Form::select('employment_type', ['' => '— Select Type —', 'full_time' => 'Full Time', 'part_time' => 'Part Time', 'contract' => 'Contract', 'casual' => 'Casual', 'intern' => 'Intern'], null, ['class' => 'form-control', 'required']) !!}
                                @error('employment_type')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group col-sm-6">
                                {!! Form::label('employment_status', 'Employment Status:') !!}
                                {!! Form::select('employment_status', ['active' => 'Active', 'on_leave' => 'On Leave', 'suspended' => 'Suspended', 'terminated' => 'Terminated', 'resigned' => 'Resigned', 'retired' => 'Retired'], null, ['class' => 'form-control', 'required']) !!}
                                @error('employment_status')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Contact Information --}}
            <div class="col-md-12">
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-address-book mr-1"></i> Contact Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-sm-6">
                                {!! Form::label('work_email', 'Work Email:') !!}
                                {!! Form::email('work_email', null, ['class' => 'form-control', 'required', 'maxlength' => 100]) !!}
                                @error('work_email')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group col-sm-6">
                                {!! Form::label('personal_email', 'Personal Email:') !!}
                                {!! Form::email('personal_email', null, ['class' => 'form-control', 'maxlength' => 100]) !!}
                                @error('personal_email')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group col-sm-12">
                                {!! Form::label('current_address', 'Current Address:') !!}
                                {!! Form::textarea('current_address', null, ['class' => 'form-control', 'rows' => 2]) !!}
                                @error('current_address')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group col-sm-6">
                                {!! Form::label('city', 'City:') !!}
                                {!! Form::text('city', null, ['class' => 'form-control', 'maxlength' => 50]) !!}
                                @error('city')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group col-sm-6">
                                {!! Form::label('country', 'Country:') !!}
                                {!! Form::text('country', null, ['class' => 'form-control', 'maxlength' => 50]) !!}
                                @error('country')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-outline card-primary">
            <div class="card-body">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save mr-1"></i> Save Changes
                </button>
                <a href="{{ route('teacher-management.show', $teacher->staff_id) }}" class="btn btn-default"> Cancel </a>
            </div>
        </div>

        {!! Form::close() !!}
    </div>
@endsection
