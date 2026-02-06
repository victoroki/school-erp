<div class="row">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0 font-weight-bold text-danger"><i class="fas fa-gavel mr-2"></i> Disciplinary History</h6>
                <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#logDisciplinaryModal">
                    <i class="fas fa-plus mr-1"></i> Log Disciplinary Action
                </button>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="bg-light x-small text-uppercase">
                        <tr>
                            <th>Incident Date</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Action Taken</th>
                            <th>Status</th>
                            <th>Reported By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($student->disciplinaryRecords as $record)
                        <tr>
                            <td class="align-middle">{{ $record->incident_date->format('d/m/Y') }}</td>
                            <td class="align-middle"><span class="badge badge-outline-danger">{{ $record->incident_type }}</span></td>
                            <td class="align-middle" style="max-width: 250px;">
                                <div class="text-truncate" title="{{ $record->description }}">{{ $record->description }}</div>
                            </td>
                            <td class="align-middle text-muted">{{ $record->action_taken ?? 'Pending' }}</td>
                            <td class="align-middle">
                                @php 
                                    $map = ['closed' => 'success', 'open' => 'danger', 'under_investigation' => 'warning'];
                                    $cls = $map[$record->status] ?? 'secondary';
                                @endphp
                                <span class="badge badge-{{ $cls }}">{{ ucfirst(str_replace('_', ' ', $record->status)) }}</span>
                            </td>
                            <td class="align-middle small">
                                {{ $record->reporter->name ?? 'System' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="fas fa-shield-alt fa-3x text-light-gray mb-3"></i>
                                <p class="text-muted">No disciplinary records found for this student. Keep it up!</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="logDisciplinaryModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Log Disciplinary Action</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('disciplinary-records.store') }}" method="POST">
                @csrf
                <input type="hidden" name="student_id" value="{{ $student->student_id }}">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Incident Date</label>
                        <input type="date" name="incident_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Incident Type</label>
                        <select name="incident_type" class="form-control" required>
                            <option value="Bullying">Bullying</option>
                            <option value="Tardiness">Tardiness</option>
                            <option value="Dress Code">Dress Code</option>
                            <option value="Cheating">Cheating</option>
                            <option value="Disruptive Behavior">Disruptive Behavior</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Describe the incident..." required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Action Taken (Initial)</label>
                        <textarea name="action_taken" class="form-control" rows="2" placeholder="Any immediate action taken?"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control" required>
                            <option value="open">Open</option>
                            <option value="investigating">Under Investigation</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Log Incident</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .badge-outline-danger { border: 1px solid #dc3545; color: #dc3545; }
    .text-light-gray { color: #e9ecef; }
</style>
