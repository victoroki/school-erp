<!-- Room Number Field -->
<div class="col-sm-6 mb-4">
    <div class="d-flex align-items-center">
        <div class="rounded-circle d-flex align-items-center justify-content-center mr-3 shadow-sm" style="width: 45px; height: 45px; background-color: #eff6ff; color: #1d4ed8;">
            <i class="fas fa-door-open"></i>
        </div>
        <div>
            <label class="text-muted d-block small mb-1" style="font-weight: 700; text-transform: uppercase;">Room Number</label>
            <p class="font-weight-bold mb-0 text-dark" style="font-size: 1.1rem;">{{ $classroom->room_number }}</p>
        </div>
    </div>
</div>

<!-- Building Field -->
<div class="col-sm-6 mb-4">
    <div class="d-flex align-items-center">
        <div class="rounded-circle d-flex align-items-center justify-content-center mr-3 shadow-sm" style="width: 45px; height: 45px; background-color: #f8fafc; color: #475569;">
            <i class="fas fa-building"></i>
        </div>
        <div>
            <label class="text-muted d-block small mb-1" style="font-weight: 700; text-transform: uppercase;">Building</label>
            <p class="font-weight-bold mb-0 text-dark" style="font-size: 1.1rem;">{{ $classroom->building }}</p>
        </div>
    </div>
</div>

<!-- Floor Field -->
<div class="col-sm-6 mb-4">
    <div class="d-flex align-items-center">
        <div class="rounded-circle d-flex align-items-center justify-content-center mr-3 shadow-sm" style="width: 45px; height: 45px; background-color: #f1f5f9; color: #334155;">
            <i class="fas fa-layer-group"></i>
        </div>
        <div>
            <label class="text-muted d-block small mb-1" style="font-weight: 700; text-transform: uppercase;">Floor Level</label>
            <p class="font-weight-bold mb-0 text-dark" style="font-size: 1.1rem;">{{ $classroom->floor }}</p>
        </div>
    </div>
</div>

<!-- Capacity Field -->
<div class="col-sm-6 mb-4">
    <div class="d-flex align-items-center">
        <div class="rounded-circle d-flex align-items-center justify-content-center mr-3 shadow-sm" style="width: 45px; height: 45px; background-color: #f0fdf4; color: #15803d;">
            <i class="fas fa-users"></i>
        </div>
        <div>
            <label class="text-muted d-block small mb-1" style="font-weight: 700; text-transform: uppercase;">Student Capacity</label>
            <p class="font-weight-bold mb-0 text-dark" style="font-size: 1.1rem;">{{ $classroom->capacity }} Students</p>
        </div>
    </div>
</div>

<!-- Facilities -->
<div class="col-sm-12 mt-2">
    <div class="p-4 rounded shadow-sm" style="background-color: #f8fafc; border-radius: 12px !important;">
        <h6 class="font-weight-bold mb-3 text-dark">Available Facilities</h6>
        <div class="d-flex flex-wrap" style="gap: 15px;">
            <div class="d-flex align-items-center px-3 py-2 rounded-pill {{ $classroom->has_sockets ? 'bg-white shadow-xs border text-success' : 'bg-light text-muted' }}" style="font-size: 0.9rem; font-weight: 600;">
                <i class="fas fa-plug mr-2"></i> Power Sockets: {{ $classroom->has_sockets ? 'Available' : 'None' }}
            </div>
            <div class="d-flex align-items-center px-3 py-2 rounded-pill {{ $classroom->has_whiteboard ? 'bg-white shadow-xs border text-success' : 'bg-light text-muted' }}" style="font-size: 0.9rem; font-weight: 600;">
                <i class="fas fa-chalkboard mr-2"></i> Whiteboard: {{ $classroom->has_whiteboard ? 'Available' : 'None' }}
            </div>
        </div>
    </div>
</div>
