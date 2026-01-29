<div class="row">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0 font-weight-bold"><i class="fas fa-folder-open mr-2 text-warning"></i> Student Documents</h6>
                <a href="{{ route('student-documents.create') }}?student_id={{ $student->student_id }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-upload mr-1"></i> Upload Document
                </a>
            </div>
            <div class="card-body">
                @if($student->studentDocuments->count() > 0)
                    @php
                        $groupedDocs = $student->studentDocuments->groupBy('document_category');
                    @endphp

                    @foreach(['academic', 'medical', 'identification', 'financial', 'legal', 'certificates', 'other'] as $category)
                        @if(isset($groupedDocs[$category]) && $groupedDocs[$category]->count() > 0)
                            <h6 class="font-weight-bold text-capitalize mt-3 mb-2">
                                <i class="fas fa-folder mr-2 text-warning"></i> {{ str_replace('_', ' ', $category) }} Documents
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-hover table-sm">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Document Name</th>
                                            <th>Type</th>
                                            <th>Status</th>
                                            <th>Uploaded</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($groupedDocs[$category] as $doc)
                                            <tr>
                                                <td class="font-weight-bold">
                                                    <i class="fas fa-file-alt mr-2 text-muted"></i>
                                                    {{ $doc->document_name }}
                                                    @if($doc->is_mandatory)
                                                        <span class="badge badge-danger badge-sm ml-1">Required</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge badge-secondary document-category-badge">
                                                        {{ $doc->document_type }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($doc->is_verified)
                                                        <span class="badge badge-success">
                                                            <i class="fas fa-check-circle"></i> Verified
                                                        </span>
                                                    @else
                                                        <span class="badge badge-warning">
                                                            <i class="fas fa-clock"></i> Pending
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="small text-muted">
                                                    {{ optional($doc->created_at ?: $doc->uploaded_at)->format('d M, Y') ?? 'N/A' }}
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        @if($doc->file_path)
                                                            <a href="{{ route('student-documents.download', $doc->document_id) }}" 
                                                               class="btn btn-outline-primary btn-sm" 
                                                               title="Download">
                                                                <i class="fas fa-download"></i>
                                                            </a>
                                                        @endif
                                                        <a href="{{ route('student-documents.edit', $doc->document_id) }}" 
                                                           class="btn btn-outline-secondary btn-sm"
                                                           title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    @endforeach
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No documents uploaded yet.</p>
                        <a href="{{ route('student-documents.create') }}?student_id={{ $student->student_id }}" class="btn btn-primary">
                            <i class="fas fa-upload mr-1"></i> Upload First Document
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
