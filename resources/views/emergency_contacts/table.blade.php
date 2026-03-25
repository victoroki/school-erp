@push('page_css')
<style>
    .emergency-table-wrapper {
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #f1f5f9;
        background: #fff;
    }
    #emergency-contacts-table th {
        font-weight: 600;
        color: #64748b;
        background-color: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        padding: 16px;
    }
    #emergency-contacts-table td {
        vertical-align: middle;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
        padding: 16px;
        font-size: 0.9rem;
    }
    #emergency-contacts-table tr:last-child td {
        border-bottom: none;
    }
    .modern-badge {
        padding: 0.4em 0.8em;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.75rem;
        letter-spacing: 0.3px;
        text-transform: uppercase;
    }
    .btn-action {
        border-radius: 8px;
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        border: none;
        margin-right: 4px;
    }
    .btn-action-view { background: #e0f2fe; color: #0284c7; }
    .btn-action-view:hover { background: #bae6fd; color: #0369a1; transform: translateY(-1px); }
    .btn-action-edit { background: #fef3c7; color: #d97706; }
    .btn-action-edit:hover { background: #fde68a; color: #b45309; transform: translateY(-1px); }
    .btn-action-delete { background: #fee2e2; color: #dc2626; }
    .btn-action-delete:hover { background: #fecaca; color: #b91c1c; transform: translateY(-1px); }
</style>
@endpush

<div class="card-body p-0 emergency-table-wrapper shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0" id="emergency-contacts-table">
            <thead>
            <tr>
                <th>Student</th>
                <th>Name / Relationship</th>
                <th>Contact Details</th>
                <th>Address</th>
                <th>Status</th>
                <th class="text-center" style="width: 150px;">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($emergencyContacts as $emergencyContact)
                <tr>
                    <td class="font-weight-bold"><i class="fas fa-user-graduate text-info mr-2"></i> {{ optional($emergencyContact->student)->first_name }} {{ optional($emergencyContact->student)->last_name }}</td>
                    <td>
                        <div class="font-weight-bold">{{ $emergencyContact->name }}</div>
                        <div class="small text-muted text-uppercase">{{ $emergencyContact->relationship }}</div>
                    </td>
                    <td>
                        <div><i class="fas fa-phone-alt text-muted mr-1"></i> {{ $emergencyContact->phone }}</div>
                        @if($emergencyContact->email)
                            <div><i class="far fa-envelope text-muted mr-1"></i> {{ $emergencyContact->email }}</div>
                        @endif
                    </td>
                    <td><div style="max-width: 250px;" class="text-truncate" title="{{ $emergencyContact->address }}">{{ $emergencyContact->address ?: 'N/A' }}</div></td>
                    <td>
                        @if($emergencyContact->is_authorized_pickup)
                            <span class="badge badge-success modern-badge"><i class="fas fa-check-circle mr-1"></i> Pickup Auth</span>
                        @else
                            <span class="badge badge-secondary modern-badge">Emergency Only</span>
                        @endif
                    </td>
                    <td class="text-center">
                        {!! Form::open(['route' => ['emergencyContacts.destroy', $emergencyContact->emergency_contact_id], 'method' => 'delete', 'class' => 'd-inline']) !!}
                        <div class='d-flex justify-content-center'>
                            <a href="{{ route('emergencyContacts.show', [$emergencyContact->emergency_contact_id]) }}"
                               class='btn-action btn-action-view' title="View">
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('emergencyContacts.edit', [$emergencyContact->emergency_contact_id]) }}"
                               class='btn-action btn-action-edit' title="Edit">
                                <i class="far fa-edit"></i>
                            </a>
                            {!! Form::button('<i class="far fa-trash-alt"></i>', ['type' => 'submit', 'class' => 'btn-action btn-action-delete', 'title' => 'Delete', 'onclick' => "return confirm('Are you sure you want to delete this emergency contact?')"]) !!}
                        </div>
                        {!! Form::close() !!}
                    </td>
                </tr>
            @endforeach
            @if($emergencyContacts->isEmpty())
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">No emergency contacts found.</td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>

    @if($emergencyContacts->hasPages())
    <div class="card-footer bg-white border-top">
        <div class="float-right">
            @include('adminlte-templates::common.paginate', ['records' => $emergencyContacts])
        </div>
    </div>
    @endif
</div>
