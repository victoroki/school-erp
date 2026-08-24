<!-- Name Field -->
<div class="col-sm-4 mb-4 text-center">
    <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center mb-3 shadow-sm text-primary" style="width: 60px; height: 60px; background-color: #eff6ff;">
        <i class="fas fa-list-ol fa-lg"></i>
    </div>
    <label class="text-muted d-block small mb-1" style="font-weight: 700; text-transform: uppercase;">Period Name</label>
    <p class="font-weight-bold mb-0 text-dark" style="font-size: 1.25rem;">{{ $period->name }}</p>
</div>

<!-- Type Field -->
<div class="col-sm-4 mb-4 text-center">
    <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center mb-3 shadow-sm {{ ($period->type ?? 'period') === 'break' ? 'text-warning' : 'text-info' }}" style="width: 60px; height: 60px; {{ ($period->type ?? 'period') === 'break' ? 'background-color: #fff7ed;' : 'background-color: #eff6ff;' }}">
        <i class="{{ ($period->type ?? 'period') === 'break' ? 'fas fa-mug-hot' : 'fas fa-book' }} fa-lg"></i>
    </div>
    <label class="text-muted d-block small mb-1" style="font-weight: 700; text-transform: uppercase;">Slot Type</label>
    <p class="font-weight-bold mb-0 text-dark" style="font-size: 1.25rem;">{{ ucfirst($period->type ?? 'period') }}</p>
</div>

<!-- Start Time Field -->
<div class="col-sm-4 mb-4 text-center">
    <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center mb-3 shadow-sm text-success" style="width: 60px; height: 60px; background-color: #f0fdf4;">
        <i class="far fa-play-circle fa-lg"></i>
    </div>
    <label class="text-muted d-block small mb-1" style="font-weight: 700; text-transform: uppercase;">Start Time</label>
    <p class="font-weight-bold mb-0 text-dark" style="font-size: 1.25rem;">{{ \Carbon\Carbon::parse($period->start_time)->format('h:i A') }}</p>
</div>

<!-- End Time Field -->
<div class="col-sm-4 mb-4 text-center">
    <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center mb-3 shadow-sm text-danger" style="width: 60px; height: 60px; background-color: #fef2f2;">
        <i class="far fa-stop-circle fa-lg"></i>
    </div>
    <label class="text-muted d-block small mb-1" style="font-weight: 700; text-transform: uppercase;">End Time</label>
    <p class="font-weight-bold mb-0 text-dark" style="font-size: 1.25rem;">{{ \Carbon\Carbon::parse($period->end_time)->format('h:i A') }}</p>
</div>

<!-- Duration Visualizer -->
<div class="col-sm-12 mt-4 text-center">
    <div class="p-3 border rounded-pill bg-light d-inline-flex align-items-center px-5 shadow-xs" style="border-style: dashed !important;">
        <i class="fas fa-history text-muted mr-3"></i>
        <span class="text-dark font-weight-bold">{{ \Carbon\Carbon::parse($period->start_time)->format('h:i A') }}</span>
        <div class="mx-4 position-relative" style="width: 100px; height: 2px; background-color: #cbd5e1;">
            <div class="position-absolute" style="top: -4px; right: -2px; width: 10px; height: 10px; border-radius: 50%; background-color: #2563eb;"></div>
        </div>
        <span class="text-dark font-weight-bold">{{ \Carbon\Carbon::parse($period->end_time)->format('h:i A') }}</span>
    </div>
</div>
