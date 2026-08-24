<!-- Class Name Field -->
<div class="col-sm-6 mb-4">
    <div class="d-flex align-items-center">
        <div class="rounded-circle d-flex align-items-center justify-content-center mr-3 shadow-sm text-primary" style="width: 45px; height: 45px; background-color: #eff6ff;">
            <i class="fas fa-school"></i>
        </div>
        <div>
            <label class="text-muted d-block small mb-1" style="font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">School Class</label>
            <p class="font-weight-bold mb-0 text-dark" style="font-size: 1.1rem;">{{ optional($classSubject->class)->name ?? 'N/A' }}</p>
        </div>
    </div>
</div>

<!-- Subject Title Field -->
<div class="col-sm-6 mb-4">
    <div class="d-flex align-items-center">
        <div class="rounded-circle d-flex align-items-center justify-content-center mr-3 shadow-sm text-info" style="width: 45px; height: 45px; background-color: #f0f9ff;">
            <i class="fas fa-book"></i>
        </div>
        <div>
            <label class="text-muted d-block small mb-1" style="font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Subject Assigned</label>
            <div class="d-flex flex-column">
                <p class="font-weight-bold mb-0 text-dark" style="font-size: 1.1rem;">{{ optional($classSubject->subject)->name ?? 'N/A' }}</p>
                <small class="text-muted">Code: {{ optional($classSubject->subject)->subject_code ?? 'N/A' }}</small>
            </div>
        </div>
    </div>
</div>

<!-- Academic Year Field -->
<div class="col-sm-6 mb-4">
    <div class="d-flex align-items-center">
        <div class="rounded-circle d-flex align-items-center justify-content-center mr-3 shadow-sm text-success" style="width: 45px; height: 45px; background-color: #f0fdf4;">
            <i class="fas fa-calendar-alt"></i>
        </div>
        <div>
            <label class="text-muted d-block small mb-1" style="font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Academic Session</label>
            <p class="font-weight-bold mb-0 text-dark" style="font-size: 1.1rem;">{{ optional($classSubject->academicYear)->name ?? 'N/A' }}</p>
        </div>
    </div>
</div>

<!-- Periods per Week Field -->
<div class="col-sm-6 mb-4">
    <div class="d-flex align-items-center">
        <div class="rounded-circle d-flex align-items-center justify-content-center mr-3 shadow-sm text-amber" style="width: 45px; height: 45px; background-color: #fffbeb;">
            <i class="fas fa-clock"></i>
        </div>
        <div>
            <label class="text-muted d-block small mb-1" style="font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Periods per Week</label>
            <p class="font-weight-bold mb-0 text-dark" style="font-size: 1.1rem;">{{ $classSubject->periods_per_week ?? 1 }}</p>
        </div>
    </div>
</div>

<!-- Extra Badge Information -->
<div class="col-sm-12 mt-3">
    <div class="p-3 rounded border bg-light d-flex align-items-center" style="border-radius: 12px !important;">
        <i class="fas fa-info-circle text-info mr-3 fa-lg"></i>
        <p class="mb-0 text-muted small">
            This subject is currently assigned to the <strong>{{ optional($classSubject->class)->name }}</strong> curriculum for the <strong>{{ optional($classSubject->academicYear)->name }}</strong> academic period.
        </p>
    </div>
</div>
