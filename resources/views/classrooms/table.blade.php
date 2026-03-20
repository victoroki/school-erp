@if($classrooms->isEmpty())
    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
        <div class="card-body text-center p-5 text-muted">
            <i class="fas fa-door-closed fa-3x mb-3 text-light"></i>
            <p class="mb-0">No classrooms found. Create one to get started.</p>
        </div>
    </div>
@else
    <div class="row">
        @foreach($classrooms as $classroom)
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm" style="border-radius: 12px; overflow: hidden; transition: transform 0.2s;">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="font-weight-bold text-dark mb-0" style="font-size: 1.2rem;">
                                Room {{ $classroom->room_number }}
                            </h5>
                            <span class="badge" style="background-color: #f1f5f9; color: #475569; padding: 0.5rem 0.75rem; border-radius: 6px;">
                                <i class="fas fa-users mr-1"></i> {{ $classroom->capacity }}
                            </span>
                        </div>
                        
                        <div class="mb-3">
                            <p class="text-muted small mb-1" style="font-weight: 600; text-transform: uppercase;">Building / Location</p>
                            <p class="mb-0 font-weight-bold"><i class="fas fa-building text-secondary mr-2"></i> {{ $classroom->building }} (Floor {{ $classroom->floor }})</p>
                        </div>
                        
                        <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                            <div class="d-flex" style="gap: 8px;">
                                @if($classroom->has_sockets)
                                    <span class="badge badge-pill shadow-sm" style="background-color: #fef9c3; color: #854d0e;" title="Power Sockets Available">
                                        <i class="fas fa-plug"></i>
                                    </span>
                                @endif
                                @if($classroom->has_whiteboard)
                                    <span class="badge badge-pill shadow-sm" style="background-color: #f0fdf4; color: #166534;" title="Whiteboard Available">
                                        <i class="fas fa-chalkboard"></i>
                                    </span>
                                @endif
                            </div>
                            
                            <div class="btn-group shadow-sm">
                                <a href="{{ route('classrooms.show', $classroom->classroom_id) }}" class="btn btn-light btn-sm px-3" style="background-color: #ffffff; border-color: #f1f5f9; color: #334155;">
                                    <i class="fas fa-eye text-primary"></i>
                                </a>
                                <a href="{{ route('classrooms.edit', $classroom->classroom_id) }}" class="btn btn-light btn-sm px-3" style="background-color: #ffffff; border-color: #f1f5f9; color: #334155;">
                                    <i class="fas fa-edit text-info"></i>
                                </a>
                                {!! Form::open(['route' => ['classrooms.destroy', $classroom->classroom_id], 'method' => 'delete', 'class' => 'd-inline']) !!}
                                    {!! Form::button('<i class="fas fa-trash-alt text-danger"></i>', [
                                        'type' => 'submit',
                                        'class' => 'btn btn-light btn-sm px-3',
                                        'style' => 'background-color: #ffffff; border-color: #f1f5f9;',
                                        'onclick' => "return confirm('Are you sure you want to delete this room?')"
                                    ]) !!}
                                {!! Form::close() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-3 px-3 d-flex justify-content-end">
        @include('adminlte-templates::common.paginate', ['records' => $classrooms])
    </div>
@endif
