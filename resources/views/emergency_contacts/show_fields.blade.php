<!-- Student Field -->
<div class="col-sm-6 mb-3">
    <label class="text-muted small text-uppercase mb-1 d-block"><i class="fas fa-user-graduate mr-1"></i> Student</label>
    <p class="font-weight-bold h5 text-dark">{{ optional($emergencyContact->student)->full_name ?: 'N/A' }}</p>
</div>

<!-- Relationship Field -->
<div class="col-sm-6 mb-3">
    <label class="text-muted small text-uppercase mb-1 d-block"><i class="fas fa-users mr-1"></i> Relationship</label>
    <p class="font-weight-bold h5 text-dark text-capitalize">{{ $emergencyContact->relationship }}</p>
</div>

<!-- Name Field -->
<div class="col-sm-6 mb-3">
    <label class="text-muted small text-uppercase mb-1 d-block"><i class="fas fa-id-card mr-1"></i> Name</label>
    <p class="h6">{{ $emergencyContact->name }}</p>
</div>

<!-- Status Field -->
<div class="col-sm-6 mb-3">
    <label class="text-muted small text-uppercase mb-1 d-block">Status/Priority</label>
    <div>
        @if($emergencyContact->is_authorized_pickup)
            <span class="badge badge-success px-3 py-2" style="border-radius: 50px;"><i class="fas fa-check-circle mr-1"></i> Authorized Pickup</span>
        @endif
        <span class="badge badge-info px-3 py-2 ml-1" style="border-radius: 50px;">Priority: {{ $emergencyContact->priority }}</span>
    </div>
</div>

<div class="col-12 mt-3">
    <hr class="mt-0">
    <h6 class="text-info font-weight-bold mb-3"><i class="fas fa-address-book mr-2"></i> Contact Information</h6>
</div>

<!-- Phone Field -->
<div class="col-sm-4 mb-3">
    <label class="text-muted small text-uppercase mb-1 d-block"><i class="fas fa-phone mr-1"></i> Phone 1</label>
    <p class="h6">{{ $emergencyContact->phone }}</p>
</div>

<!-- Phone 2 Field -->
<div class="col-sm-4 mb-3">
    <label class="text-muted small text-uppercase mb-1 d-block"><i class="fas fa-phone-alt mr-1"></i> Phone 2</label>
    <p class="h6">{{ $emergencyContact->phone_2 ?: 'N/A' }}</p>
</div>

<!-- Email Field -->
<div class="col-sm-4 mb-3">
    <label class="text-muted small text-uppercase mb-1 d-block"><i class="far fa-envelope mr-1"></i> Email</label>
    <p class="h6">{{ $emergencyContact->email ?: 'N/A' }}</p>
</div>

<!-- Address Field -->
<div class="col-sm-12 mb-3">
    <label class="text-muted small text-uppercase mb-1 d-block"><i class="fas fa-map-marker-alt mr-1"></i> Address</label>
    <p class="h6 bg-light p-3 rounded" style="min-height: 60px; line-height: 1.6;">{{ $emergencyContact->address ?: 'No address provided.' }}</p>
</div>

