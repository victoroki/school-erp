<div class="col-12">
    <div class="row">
        <!-- Staff Field -->
        <div class="col-md-6">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-info"><i class="fas fa-user-tie"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Staff</span>
                    <span class="info-box-number mb-0">
                        {{ optional($staffDocument->staff)->full_name ?? optional($staffDocument->staff)->first_name }}
                    </span>
                    @if(optional($staffDocument->staff)->employee_number)
                        <span class="badge badge-light border mt-1">{{ $staffDocument->staff->employee_number }}</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Document Type Field -->
        <div class="col-md-6">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-secondary"><i class="fas fa-tag"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Document Type</span>
                    <span class="info-box-number mb-0">{{ $staffDocument->document_type }}</span>
                </div>
            </div>
        </div>

        <!-- Document Name Field -->
        <div class="col-md-6">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-primary"><i class="fas fa-file-alt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Document Name</span>
                    <span class="info-box-number mb-0">{{ $staffDocument->document_name }}</span>
                </div>
            </div>
        </div>

        <!-- Uploaded At Field -->
        <div class="col-md-6">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-success"><i class="fas fa-calendar-check"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Uploaded At</span>
                    <span class="info-box-number mb-0">{{ optional($staffDocument->uploaded_at)->format('M d, Y h:i A') }}</span>
                </div>
            </div>
        </div>

        <!-- File Field -->
        <div class="col-12">
            <div class="card card-outline card-primary shadow-none mb-0">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-file-download mr-1"></i> Attached File
                    </h3>
                    @if($staffDocument->file_path)
                        <div class="card-tools">
                            <a href="{{ route('staffDocuments.download', [$staffDocument->document_id]) }}"
                               class="btn btn-primary btn-sm">
                                <i class="fas fa-download mr-1"></i> Download
                            </a>
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    @if($staffDocument->file_path)
                        <p class="mb-0">
                            <i class="fas fa-file text-primary mr-1"></i>
                            <span class="font-weight-bold">{{ basename($staffDocument->file_path) }}</span>
                        </p>
                        <small class="text-muted">{{ $staffDocument->file_path }}</small>
                    @else
                        <p class="text-muted mb-0">No file attached to this document.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>