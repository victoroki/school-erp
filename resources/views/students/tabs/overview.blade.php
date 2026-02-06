<div class="row">
    <!-- Personal Information -->
    <div class="col-md-6">
        <div class="card info-card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0 font-weight-bold"><i class="fas fa-user-circle text-primary mr-2"></i> Personal Information</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" width="40%"><i class="fas fa-user mr-2"></i> Full Name:</td>
                        <td class="font-weight-bold">{{ $student->full_name }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted"><i class="fas fa-birthday-cake mr-2"></i> Date of Birth:</td>
                        <td class="font-weight-bold">{{ $student->kenyan_dob }} ({{ $student->age }} years)</td>
                    </tr>
                    <tr>
                        <td class="text-muted"><i class="fas fa-venus-mars mr-2"></i> Gender:</td>
                        <td class="font-weight-bold text-capitalize">{{ $student->gender }}</td>
                    </tr>
                    @if($student->nationality)
                    <tr>
                        <td class="text-muted"><i class="fas fa-flag mr-2"></i> Nationality:</td>
                        <td class="font-weight-bold">{{ $student->nationality }}</td>
                    </tr>
                    @endif
                    @if($student->religion)
                    <tr>
                        <td class="text-muted"><i class="fas fa-pray mr-2"></i> Religion:</td>
                        <td class="font-weight-bold">{{ $student->religion }}</td>
                    </tr>
                    @endif
                    @if($student->blood_group)
                    <tr>
                        <td class="text-muted"><i class="fas fa-tint mr-2"></i> Blood Group:</td>
                        <td><span class="badge badge-danger">{{ $student->blood_group }}</span></td>
                    </tr>
                    @endif
                    @if($student->student_category)
                    <tr>
                        <td class="text-muted"><i class="fas fa-tag mr-2"></i> Category:</td>
                        <td><span class="badge badge-info">{{ $student->student_category }}</span></td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <!-- Contact Information -->
    <div class="col-md-6">
        <div class="card info-card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0 font-weight-bold"><i class="fas fa-address-book text-success mr-2"></i> Contact Information</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    @if($student->address)
                    <tr>
                        <td class="text-muted" width="40%"><i class="fas fa-map-marker-alt mr-2"></i> Address:</td>
                        <td class="font-weight-bold">{{ $student->address }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="text-muted"><i class="fas fa-city mr-2"></i> City:</td>
                        <td class="font-weight-bold">{{ $student->city }}</td>
                    </tr>
                    @if($student->county)
                    <tr>
                        <td class="text-muted"><i class="fas fa-map mr-2"></i> County:</td>
                        <td class="font-weight-bold">{{ $student->county }}</td>
                    </tr>
                    @endif
                    @if($student->sub_county)
                    <tr>
                        <td class="text-muted"><i class="fas fa-map-marked mr-2"></i> Sub-County:</td>
                        <td class="font-weight-bold">{{ $student->sub_county }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="text-muted"><i class="fas fa-globe mr-2"></i> Country:</td>
                        <td class="font-weight-bold">{{ $student->country }}</td>
                    </tr>
                    @if($student->postal_code)
                    <tr>
                        <td class="text-muted"><i class="fas fa-mail-bulk mr-2"></i> Postal Code:</td>
                        <td class="font-weight-bold">{{ $student->postal_code }}</td>
                    </tr>
                    @endif
                    @if($student->phone)
                    <tr>
                        <td class="text-muted"><i class="fas fa-phone mr-2"></i> Phone:</td>
                        <td class="font-weight-bold">{{ $student->formatted_phone }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <!-- Emergency Contact -->
    <div class="col-md-6">
        <div class="card info-card border-0 shadow-sm mb-4" style="border-left-color: #dc3545 !important;">
            <div class="card-header bg-white">
                <h6 class="mb-0 font-weight-bold"><i class="fas fa-exclamation-triangle text-danger mr-2"></i> Emergency Contact</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    @if($student->emergency_contact_name)
                    <tr>
                        <td class="text-muted" width="40%"><i class="fas fa-user mr-2"></i> Name:</td>
                        <td class="font-weight-bold">{{ $student->emergency_contact_name }}</td>
                    </tr>
                    @endif
                    @if($student->emergency_contact_relationship)
                    <tr>
                        <td class="text-muted"><i class="fas fa-link mr-2"></i> Relationship:</td>
                        <td class="font-weight-bold">{{ $student->emergency_contact_relationship }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="text-muted"><i class="fas fa-phone-alt mr-2"></i> Primary Phone:</td>
                        <td class="font-weight-bold text-danger">{{ $student->formatted_emergency_phone }}</td>
                    </tr>
                    @if($student->emergency_contact_phone_2)
                    <tr>
                        <td class="text-muted"><i class="fas fa-phone mr-2"></i> Secondary Phone:</td>
                        <td class="font-weight-bold">{{ $student->formatted_emergency_phone_2 }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <!-- Admission Information -->
    <div class="col-md-6">
        <div class="card info-card border-0 shadow-sm mb-4" style="border-left-color: #ffc107 !important;">
            <div class="card-header bg-white">
                <h6 class="mb-0 font-weight-bold"><i class="fas fa-door-open text-warning mr-2"></i> Admission Information</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" width="40%"><i class="fas fa-id-badge mr-2"></i> Admission No:</td>
                        <td class="font-weight-bold">{{ $student->admission_no }}</td>
                    </tr>
                    @if($student->nemis_number)
                    <tr>
                        <td class="text-muted"><i class="fas fa-fingerprint mr-2"></i> NEMIS ID:</td>
                        <td class="font-weight-bold">{{ $student->nemis_number }}</td>
                    </tr>
                    @endif
                    @if($student->upi_number)
                    <tr>
                        <td class="text-muted"><i class="fas fa-id-card-alt mr-2"></i> UPI Number:</td>
                        <td class="font-weight-bold">{{ $student->upi_number }}</td>
                    </tr>
                    @endif
                    @if($student->birth_certificate_no)
                    <tr>
                        <td class="text-muted"><i class="fas fa-file-contract mr-2"></i> Birth Cert No:</td>
                        <td class="font-weight-bold">{{ $student->birth_certificate_no }}</td>
                    </tr>
                    @endif
                    @if($student->education_system)
                    <tr>
                        <td class="text-muted"><i class="fas fa-school mr-2"></i> System:</td>
                        <td><span class="badge badge-primary">{{ $student->education_system }}</span></td>
                    </tr>
                    @endif
                    <tr>
                        <td class="text-muted"><i class="fas fa-info-circle mr-2"></i> Status:</td>
                        <td>{!! $student->enrollment_status_badge !!}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Transport & Hostel -->
    @if($student->uses_transport || $student->is_hosteller)
    <div class="col-md-6">
        <div class="card info-card border-0 shadow-sm mb-4" style="border-left-color: #17a2b8 !important;">
            <div class="card-header bg-white">
                <h6 class="mb-0 font-weight-bold"><i class="fas fa-bus text-info mr-2"></i> Transport & Hostel</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    @if($student->uses_transport)
                    <tr>
                        <td class="text-muted" width="40%"><i class="fas fa-bus mr-2"></i> Uses Transport:</td>
                        <td><span class="badge badge-success">Yes</span></td>
                    </tr>
                    @if($student->pickup_point)
                    <tr>
                        <td class="text-muted"><i class="fas fa-map-pin mr-2"></i> Pickup Point:</td>
                        <td class="font-weight-bold">{{ $student->pickup_point }}</td>
                    </tr>
                    @endif
                    @endif
                    @if($student->is_hosteller)
                    <tr>
                        <td class="text-muted"><i class="fas fa-home mr-2"></i> Hosteller:</td>
                        <td><span class="badge badge-primary">Yes</span></td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Previous School -->
    @if($student->previous_school)
    <div class="col-md-12">
        <div class="card info-card border-0 shadow-sm mb-4" style="border-left-color: #6c757d !important;">
            <div class="card-header bg-white">
                <h6 class="mb-0 font-weight-bold"><i class="fas fa-school text-secondary mr-2"></i> Previous School Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <p class="text-muted mb-1"><i class="fas fa-building mr-2"></i> School Name:</p>
                        <p class="font-weight-bold">{{ $student->previous_school }}</p>
                    </div>
                    @if($student->previous_class)
                    <div class="col-md-3">
                        <p class="text-muted mb-1"><i class="fas fa-chalkboard mr-2"></i> Last Class:</p>
                        <p class="font-weight-bold">{{ $student->previous_class }}</p>
                    </div>
                    @endif
                    @if($student->transfer_date)
                    <div class="col-md-3">
                        <p class="text-muted mb-1"><i class="fas fa-calendar mr-2"></i> Transfer Date:</p>
                        <p class="font-weight-bold">{{ $student->transfer_date->format('d/m/Y') }}</p>
                    </div>
                    @endif
                    @if($student->transfer_reason)
                    <div class="col-md-12 mt-2">
                        <p class="text-muted mb-1"><i class="fas fa-comment mr-2"></i> Transfer Reason:</p>
                        <p class="font-weight-bold">{{ $student->transfer_reason }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
