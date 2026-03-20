<!-- Academic Year Field -->
<div class="col-sm-6 mb-4">
    <div class="d-flex align-items-center">
        <div class="rounded-circle d-flex align-items-center justify-content-center mr-3 shadow-sm" style="width: 45px; height: 45px; background-color: #f0fdf4; color: #15803d;">
            <i class="fas fa-calendar-alt"></i>
        </div>
        <div>
            <label class="text-muted d-block small mb-1" style="font-weight: 700; text-transform: uppercase;">Academic Year</label>
            <p class="font-weight-bold mb-0 text-dark" style="font-size: 1.1rem;">{{ optional($classSection->academicYear)->name ?? 'N/A' }}</p>
        </div>
    </div>
</div>

<!-- Class Field -->
<div class="col-sm-6 mb-4">
    <div class="d-flex align-items-center">
        <div class="rounded-circle d-flex align-items-center justify-content-center mr-3 shadow-sm" style="width: 45px; height: 45px; background-color: #eff6ff; color: #1d4ed8;">
            <i class="fas fa-school"></i>
        </div>
        <div>
            <label class="text-muted d-block small mb-1" style="font-weight: 700; text-transform: uppercase;">Class</label>
            <p class="font-weight-bold mb-0 text-dark" style="font-size: 1.1rem;">{{ optional($classSection->schoolClass)->name ?? 'N/A' }}</p>
        </div>
    </div>
</div>

<!-- Section Field -->
<div class="col-sm-6 mb-4">
    <div class="d-flex align-items-center">
        <div class="rounded-circle d-flex align-items-center justify-content-center mr-3 shadow-sm" style="width: 45px; height: 45px; background-color: #fff7ed; color: #c2410c;">
            <i class="fas fa-layer-group"></i>
        </div>
        <div>
            <label class="text-muted d-block small mb-1" style="font-weight: 700; text-transform: uppercase;">Section</label>
            <p class="font-weight-bold mb-0 text-dark" style="font-size: 1.1rem;">{{ optional($classSection->section)->name ?? 'N/A' }}</p>
        </div>
    </div>
</div>

<!-- Classroom Field -->
<div class="col-sm-6 mb-4">
    <div class="d-flex align-items-center">
        <div class="rounded-circle d-flex align-items-center justify-content-center mr-3 shadow-sm" style="width: 45px; height: 45px; background-color: #fef2f2; color: #b91c1c;">
            <i class="fas fa-door-open"></i>
        </div>
        <div>
            <label class="text-muted d-block small mb-1" style="font-weight: 700; text-transform: uppercase;">Classroom / Room</label>
            <p class="font-weight-bold mb-0 text-dark" style="font-size: 1.1rem;">{{ optional($classSection->classroom)->room_number ?? 'N/A' }}</p>
        </div>
    </div>
</div>

<!-- Class Teacher Field -->
<div class="col-sm-6 mb-4">
    <div class="d-flex align-items-center">
        <div class="rounded-circle d-flex align-items-center justify-content-center mr-3 shadow-sm" style="width: 45px; height: 45px; background-color: #fdf4ff; color: #a21caf;">
            <i class="fas fa-user-tie"></i>
        </div>
        <div>
            <label class="text-muted d-block small mb-1" style="font-weight: 700; text-transform: uppercase;">Class Teacher</label>
            <p class="font-weight-bold mb-0 text-dark" style="font-size: 1.1rem;">{{ $classSection->classTeacher ? $classSection->classTeacher->first_name . ' ' . $classSection->classTeacher->last_name : 'N/A' }}</p>
        </div>
    </div>
</div>
