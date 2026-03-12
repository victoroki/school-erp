<style>
    .form-section-title {
        border-bottom: 2px solid #667eea;
        padding-bottom: 5px;
        margin-bottom: 20px;
        color: #667eea;
        font-weight: bold;
        text-transform: uppercase;
        font-size: 0.9rem;
        letter-spacing: 1px;
    }
    .form-group label {
        font-weight: 600;
        color: #555;
    }
    .required-star {
        color: #dc3545;
    }
</style>

<div class="col-12">
    <!-- Section 1: Basic Information -->
    <div class="card card-outline card-primary mb-4 shadow-sm">
        <div class="card-header bg-white">
            <h3 class="card-title text-primary"><i class="fas fa-user mr-2"></i> Basic Information</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="form-group col-sm-3">
                    {!! Form::label('admission_no', 'Admission No:') !!} <span class="required-star">*</span>
                    {!! Form::text('admission_no', null, ['class' => 'form-control', 'required', 'maxlength' => 20, 'placeholder' => 'ADM-XXXX']) !!}
                </div>
                <div class="form-group col-sm-3">
                    {!! Form::label('nemis_number', 'NEMIS Number:') !!}
                    {!! Form::text('nemis_number', null, ['class' => 'form-control', 'placeholder' => 'NEMIS ID']) !!}
                </div>
                <div class="form-group col-sm-3">
                    {!! Form::label('upi_number', 'UPI Number:') !!}
                    {!! Form::text('upi_number', null, ['class' => 'form-control', 'placeholder' => 'UPI ID']) !!}
                </div>
                <div class="form-group col-sm-3">
                    {!! Form::label('roll_number', 'Roll Number:') !!}
                    {!! Form::text('roll_number', null, ['class' => 'form-control', 'maxlength' => 20]) !!}
                </div>
            </div>
            <div class="row">
                <div class="form-group col-sm-4">
                    {!! Form::label('education_system', 'Education System:') !!}
                    {!! Form::select('education_system', ['CBC' => 'CBC (Competency Based)', '8-4-4' => '8-4-4 System'], null, ['class' => 'form-control select2']) !!}
                </div>
                <div class="form-group col-sm-4">
                    {!! Form::label('status', 'Account Status:') !!}
                    {!! Form::select('status', ['active' => 'Active', 'inactive' => 'Inactive', 'alumni' => 'Alumni', 'transferred' => 'Transferred'], null, ['class' => 'form-control select2']) !!}
                </div>
                <div class="form-group col-sm-4">
                    {!! Form::label('student_category', 'Student Category:') !!}
                    {!! Form::text('student_category', null, ['class' => 'form-control', 'placeholder' => 'e.g., General, Orphan, etc']) !!}
                </div>
            </div>
            <div class="row">
                <div class="form-group col-sm-4">
                    {!! Form::label('first_name', 'First Name:') !!} <span class="required-star">*</span>
                    {!! Form::text('first_name', null, ['class' => 'form-control', 'required', 'maxlength' => 50]) !!}
                </div>
                <div class="form-group col-sm-4">
                    {!! Form::label('middle_name', 'Middle Name:') !!}
                    {!! Form::text('middle_name', null, ['class' => 'form-control', 'maxlength' => 50]) !!}
                </div>
                <div class="form-group col-sm-4">
                    {!! Form::label('last_name', 'Last Name:') !!} <span class="required-star">*</span>
                    {!! Form::text('last_name', null, ['class' => 'form-control', 'required', 'maxlength' => 50]) !!}
                </div>
            </div>
            <div class="row">
                <div class="form-group col-sm-4">
                    {!! Form::label('date_of_birth', 'Date of Birth:') !!} <span class="required-star">*</span>
                    {!! Form::date('date_of_birth', null, ['class' => 'form-control', 'required']) !!}
                </div>
                <div class="form-group col-sm-4">
                    {!! Form::label('gender', 'Gender:') !!} <span class="required-star">*</span>
                    {!! Form::select('gender', ['male' => 'Male', 'female' => 'Female', 'other' => 'Other'], null, ['class' => 'form-control select2', 'required']) !!}
                </div>
                <div class="form-group col-sm-4">
                    {!! Form::label('blood_group', 'Blood Group:') !!}
                    {!! Form::select('blood_group', ['' => 'Select', 'A+' => 'A+', 'A-' => 'A-', 'B+' => 'B+', 'B-' => 'B-', 'O+' => 'O+', 'O-' => 'O-', 'AB+' => 'AB+', 'AB-' => 'AB-'], null, ['class' => 'form-control select2']) !!}
                </div>
            </div>
            <div class="row">
                <div class="form-group col-sm-4">
                    {!! Form::label('nationality', 'Nationality:') !!}
                    {!! Form::text('nationality', null, ['class' => 'form-control', 'maxlength' => 50]) !!}
                </div>
                <div class="form-group col-sm-4">
                    {!! Form::label('religion', 'Religion:') !!}
                    {!! Form::text('religion', null, ['class' => 'form-control', 'maxlength' => 50]) !!}
                </div>
                <div class="form-group col-sm-4">
                    {!! Form::label('student_category', 'Category:') !!}
                    {!! Form::text('student_category', null, ['class' => 'form-control', 'placeholder' => 'e.g., General, SC, ST']) !!}
                </div>
            </div>
        </div>
    </div>

    <!-- Section 2: Contact Information -->
    <div class="card card-outline card-success mb-4 shadow-sm">
        <div class="card-header bg-white">
            <h3 class="card-title text-success"><i class="fas fa-address-book mr-2"></i> Contact & Address</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="form-group col-sm-12">
                    {!! Form::label('address', 'Permanent Address:') !!}
                    {!! Form::textarea('address', null, ['class' => 'form-control', 'rows' => 2]) !!}
                </div>
            </div>
            <div class="row">
                <div class="form-group col-sm-3">
                    {!! Form::label('city', 'City/Town:') !!} <span class="required-star">*</span>
                    {!! Form::text('city', null, ['class' => 'form-control', 'required', 'maxlength' => 50]) !!}
                </div>
                <div class="form-group col-sm-3">
                    {!! Form::label('county', 'County:') !!}
                    {!! Form::text('county', null, ['class' => 'form-control', 'maxlength' => 50, 'placeholder' => 'e.g. Nairobi']) !!}
                </div>
                <div class="form-group col-sm-3">
                    {!! Form::label('sub_county', 'Sub-County:') !!}
                    {!! Form::text('sub_county', null, ['class' => 'form-control', 'maxlength' => 50]) !!}
                </div>
                <div class="form-group col-sm-3">
                    {!! Form::label('country', 'Country:') !!} <span class="required-star">*</span>
                    {!! Form::text('country', 'Kenya', ['class' => 'form-control', 'required', 'maxlength' => 50]) !!}
                </div>
            </div>
        </div>
    </div>

    <!-- Section 3: Emergency & Medical -->
    <div class="card card-outline card-danger mb-4 shadow-sm">
        <div class="card-header bg-white">
            <h3 class="card-title text-danger"><i class="fas fa-heartbeat mr-2"></i> Emergency & Medical</h3>
        </div>
        <div class="card-body">
            <h6 class="form-section-title">Emergency Contact</h6>
            <div class="row">
                <div class="form-group col-sm-4">
                    {!! Form::label('emergency_contact_name', 'Contact Name:') !!}
                    {!! Form::text('emergency_contact_name', null, ['class' => 'form-control']) !!}
                </div>
                <div class="form-group col-sm-4">
                    {!! Form::label('emergency_contact_relationship', 'Relationship:') !!}
                    {!! Form::text('emergency_contact_relationship', null, ['class' => 'form-control']) !!}
                </div>
                <div class="form-group col-sm-4">
                    {!! Form::label('emergency_contact', 'Primary Phone:') !!} <span class="required-star">*</span>
                    {!! Form::text('emergency_contact', null, ['class' => 'form-control', 'required']) !!}
                </div>
            </div>

            <h6 class="form-section-title mt-4">Medical Details</h6>
            <div class="row">
                <div class="form-group col-sm-6">
                    {!! Form::label('medical_conditions', 'Chronic Conditions:') !!}
                    {!! Form::textarea('medical_conditions', null, ['class' => 'form-control', 'rows' => 2]) !!}
                </div>
                <div class="form-group col-sm-6">
                    {!! Form::label('allergies', 'Allergies:') !!}
                    {!! Form::textarea('allergies', null, ['class' => 'form-control', 'rows' => 2]) !!}
                </div>
                <div class="form-group col-sm-6">
                    {!! Form::label('medications', 'Active Medications:') !!}
                    {!! Form::textarea('medications', null, ['class' => 'form-control', 'rows' => 2]) !!}
                </div>
                <div class="form-group col-sm-3">
                    {!! Form::label('doctor_name', 'Family Doctor:') !!}
                    {!! Form::text('doctor_name', null, ['class' => 'form-control']) !!}
                </div>
                <div class="form-group col-sm-3">
                    {!! Form::label('doctor_phone', 'Doctor Phone:') !!}
                    {!! Form::text('doctor_phone', null, ['class' => 'form-control']) !!}
                </div>
            </div>
        </div>
    </div>

    <!-- Section 4: Academic & Other -->
    <div class="card card-outline card-warning mb-4 shadow-sm">
        <div class="card-header bg-white">
            <h3 class="card-title text-warning"><i class="fas fa-graduation-cap mr-2"></i> Academic & Admission</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="form-group col-sm-4">
                    {!! Form::label('admission_date', 'Admission Date:') !!} <span class="required-star">*</span>
                    {!! Form::date('admission_date', date('Y-m-d'), ['class' => 'form-control', 'required']) !!}
                </div>
                <div class="form-group col-sm-4">
                    {!! Form::label('enrollment_status', 'Enrollment Status:') !!}
                    {!! Form::select('enrollment_status', ['enrolled' => 'Enrolled', 'graduated' => 'Graduated', 'transferred' => 'Transferred', 'expelled' => 'Expelled', 'dropped_out' => 'Dropped Out', 'on_leave' => 'On Leave'], null, ['class' => 'form-control select2']) !!}
                </div>
                <div class="form-group col-sm-4">
                    {!! Form::label('photo', 'Student Photo:') !!}
                    <div class="input-group">
                        <div class="custom-file">
                            {!! Form::file('photo', ['class' => 'custom-file-input', 'id' => 'photo']) !!}
                            {!! Form::label('photo', 'Choose file', ['class' => 'custom-file-label']) !!}
                        </div>
                    </div>
                </div>
            </div>
            
            <h6 class="form-section-title mt-4">Other Services</h6>
            <div class="row">
                <div class="form-group col-sm-4">
                    <div class="custom-control custom-switch mt-4">
                        {!! Form::hidden('uses_transport', 0) !!}
                        {!! Form::checkbox('uses_transport', 1, null, ['class' => 'custom-control-input', 'id' => 'uses_transport']) !!}
                        {!! Form::label('uses_transport', 'Uses School Transport', ['class' => 'custom-control-input-label']) !!}
                        <label class="custom-control-label" for="uses_transport">Uses School Transport</label>
                    </div>
                </div>
                <div class="form-group col-sm-4">
                    <div class="custom-control custom-switch mt-4">
                        {!! Form::hidden('is_hosteller', 0) !!}
                        {!! Form::checkbox('is_hosteller', 1, null, ['class' => 'custom-control-input', 'id' => 'is_hosteller']) !!}
                        <label class="custom-control-label" for="is_hosteller">Hostel Resident</label>
                    </div>
                </div>
                <div class="form-group col-sm-4">
                    <div class="custom-control custom-switch mt-4">
                        {!! Form::hidden('is_scholarship_holder', 0) !!}
                        {!! Form::checkbox('is_scholarship_holder', 1, null, ['class' => 'custom-control-input', 'id' => 'is_scholarship_holder']) !!}
                        <label class="custom-control-label" for="is_scholarship_holder">Scholarship Holder</label>
                    </div>
                </div>
            </div>
            
            <h6 class="form-section-title mt-4">Previous School Details</h6>
            <div class="row">
                <div class="form-group col-sm-6">
                    {!! Form::label('previous_school', 'Previous School Name:') !!}
                    {!! Form::text('previous_school', null, ['class' => 'form-control']) !!}
                </div>
                <div class="form-group col-sm-3">
                    {!! Form::label('previous_class', 'Last Class Attended:') !!}
                    {!! Form::text('previous_class', null, ['class' => 'form-control']) !!}
                </div>
                <div class="form-group col-sm-3">
                    {!! Form::label('transfer_certificate_no', 'Transfer Certificate No:') !!}
                    {!! Form::text('transfer_certificate_no', null, ['class' => 'form-control']) !!}
                </div>
            </div>
        </div>
    </div>

    <!-- Section 5: Initial Enrollment (Optional) -->
    @if(isset($classSections))
    <div class="card card-outline card-info mb-4 shadow-sm">
        <div class="card-header bg-white">
            <h3 class="card-title text-info"><i class="fas fa-sign-in-alt mr-2"></i> Initial Class Enrollment (Optional)</h3>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-4">Complete this section if you want to enroll the student in a class immediately upon admission.</p>
            <div class="row">
                <div class="form-group col-sm-6">
                    {!! Form::label('class_section_id', 'Class Section:') !!}
                    {!! Form::select('class_section_id', ['' => 'Select Class Section'] + $classSections, null, ['class' => 'form-control select2']) !!}
                </div>
                <div class="form-group col-sm-3">
                    {!! Form::label('academic_year_id', 'Academic Year:') !!}
                    {!! Form::select('academic_year_id', ['' => 'Select Academic Year'] + $academicYears, null, ['class' => 'form-control select2']) !!}
                </div>
                <div class="form-group col-sm-3">
                    {!! Form::label('roll_number_enrollment', 'Class Roll Number:') !!}
                    {!! Form::text('roll_number_enrollment', null, ['class' => 'form-control', 'placeholder' => 'Optional']) !!}
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('page_css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css">
    <style>
        .select2-container .select2-selection--single {
            height: 38px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px !important;
        }
    </style>
@endpush

@push('page_scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Handle file input label
        $('.custom-file-input').on('change', function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').addClass("selected").html(fileName);
        });

        // Initialize Select2
        $('.select2').select2({
            theme: 'bootstrap4',
            placeholder: "Select an option",
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endpush