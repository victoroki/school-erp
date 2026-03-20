<div class="card-body p-0 border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
    <div class="table-responsive">
        <table class="table table-hover mb-0" id="departments-table">
            <thead style="background-color: #f8fafc; border-bottom: 2px solid #f1f5f9;">
            <tr>
                <th class="px-4 py-3 text-muted small font-weight-bold text-uppercase" style="letter-spacing: 0.5px;">Department Name</th>
                <th class="py-3 text-muted small font-weight-bold text-uppercase" style="letter-spacing: 0.5px;">Summary Description</th>
                <th class="py-3 text-muted small font-weight-bold text-uppercase" style="letter-spacing: 0.5px;">Head of Department (HOD)</th>
                <th class="px-4 py-3 text-muted small font-weight-bold text-uppercase text-right" style="letter-spacing: 0.5px;">Actions</th>
            </tr>
            </thead>
            <tbody class="text-dark">
            @foreach($departments as $department)
                <tr style="transition: background 0.2s;">
                    <td class="px-4 py-3 align-middle font-weight-bold" style="font-size: 0.95rem;">
                        <i class="fas fa-folder text-primary mr-2 small"></i> {{ $department->name }}
                    </td>
                    <td class="py-3 align-middle text-muted small" style="max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        {{ $department->description ?: 'No description provided.' }}
                    </td>
                    <td class="py-3 align-middle">
                        @if($department->hod)
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle mr-2 d-flex align-items-center justify-content-center bg-light text-primary font-weight-bold" style="width: 30px; height: 30px; font-size: 0.75rem; border: 1px solid #e2e8f0;">
                                    {{ strtoupper(substr($department->hod->first_name, 0, 1)) }}
                                </div>
                                <span class="font-weight-bold small text-dark">{{ $department->hod->full_name }}</span>
                            </div>
                        @else
                            <span class="badge badge-light text-muted" style="font-weight: 500;">Not Assigned</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 align-middle text-right" style="width: 150px">
                        {!! Form::open(['route' => ['departments.destroy', $department->department_id], 'method' => 'delete', 'class' => 'm-0']) !!}
                        <div class='btn-group shadow-xs rounded' style="overflow: hidden;">
                            <a href="{{ route('departments.show', [$department->department_id]) }}"
                               class='btn btn-light btn-sm text-info' title="View Details" style="background-color: #ffffff; border-color: #f1f5f9;">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('departments.edit', [$department->department_id]) }}"
                               class='btn btn-light btn-sm text-primary' title="Edit" style="background-color: #ffffff; border-color: #f1f5f9;">
                                <i class="fas fa-edit"></i>
                            </a>
                            {!! Form::button('<i class="fas fa-trash-alt"></i>', [
                                'type' => 'submit', 
                                'class' => 'btn btn-light btn-sm text-danger', 
                                'style' => 'background-color: #ffffff; border-color: #f1f5f9;',
                                'onclick' => "return confirm('Are you sure you want to delete this department?')",
                                'title' => 'Delete'
                            ]) !!}
                        </div>
                        {!! Form::close() !!}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="card-footer bg-white border-top shadow-xs clearfix py-3 px-4">
        <div class="float-right m-0">
            @include('adminlte-templates::common.paginate', ['records' => $departments])
        </div>
    </div>
</div>
