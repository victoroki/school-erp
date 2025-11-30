<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-header">Personal Information</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        {!! Form::label('admission_no', 'Admission No') !!}
                        {!! Form::text('admission_no', null, ['class' => 'form-control', 'required', 'maxlength' => 20, 'placeholder' => 'e.g., ADM0001']) !!}
                    </div>
                    <div class="col-md-4">
                        {!! Form::label('first_name', 'First Name') !!}
                        {!! Form::text('first_name', null, ['class' => 'form-control', 'required', 'maxlength' => 50, 'placeholder' => 'e.g., John']) !!}
                    </div>
                    <div class="col-md-4">
                        {!! Form::label('middle_name', 'Middle Name') !!}
                        {!! Form::text('middle_name', null, ['class' => 'form-control', 'maxlength' => 50, 'placeholder' => 'Optional']) !!}
                    </div>
                    <div class="col-md-4">
                        {!! Form::label('last_name', 'Last Name') !!}
                        {!! Form::text('last_name', null, ['class' => 'form-control', 'required', 'maxlength' => 50, 'placeholder' => 'e.g., Doe']) !!}
                    </div>
                    <div class="col-md-4">
                        {!! Form::label('date_of_birth', 'Date of Birth') !!}
                        {!! Form::date('date_of_birth', null, ['class' => 'form-control']) !!}
                    </div>
                    <div class="col-md-4">
                        {!! Form::label('gender', 'Gender') !!}
                        {!! Form::select('gender', ['male' => 'Male', 'female' => 'Female', 'other' => 'Other'], null, ['class' => 'form-control', 'required']) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-header">Contact</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        {!! Form::label('city', 'City') !!}
                        {!! Form::text('city', null, ['class' => 'form-control', 'required', 'maxlength' => 50, 'placeholder' => 'e.g., Metro City']) !!}
                    </div>
                    <div class="col-md-4">
                        {!! Form::label('country', 'Country') !!}
                        {!! Form::text('country', null, ['class' => 'form-control', 'required', 'maxlength' => 50, 'placeholder' => 'e.g., Country']) !!}
                    </div>
                    <div class="col-md-4">
                        {!! Form::label('phone', 'Phone') !!}
                        {!! Form::text('phone', null, ['class' => 'form-control', 'maxlength' => 20, 'placeholder' => 'e.g., 555-1234', 'inputmode' => 'tel', 'pattern' => '[0-9\-\+\s]{7,20}']) !!}
                    </div>
                    <div class="col-md-4">
                        {!! Form::label('emergency_contact', 'Emergency Contact') !!}
                        {!! Form::text('emergency_contact', null, ['class' => 'form-control', 'required', 'maxlength' => 20, 'placeholder' => 'e.g., 555-9999', 'inputmode' => 'tel', 'pattern' => '[0-9\-\+\s]{7,20}']) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-header">Admission</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        {!! Form::label('admission_date', 'Admission Date') !!}
                        {!! Form::date('admission_date', null, ['class' => 'form-control']) !!}
                    </div>
                    <div class="col-md-4">
                        {!! Form::label('photo', 'Profile Photo') !!}
                        {!! Form::file('photo', ['class' => 'form-control']) !!}
                    </div>
                    <div class="col-md-4">
                        {!! Form::label('status', 'Status') !!}
                        {!! Form::select('status', ['active' => 'Active', 'inactive' => 'Inactive', 'alumni' => 'Alumni', 'transferred' => 'Transferred'], null, ['class' => 'form-control']) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>