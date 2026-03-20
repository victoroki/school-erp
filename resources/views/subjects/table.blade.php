@if($subjects->isEmpty())
    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
        <div class="card-body text-center p-5 text-muted">
            <i class="fas fa-book-open fa-3x mb-3 text-light"></i>
            <p class="mb-0">No subjects found. Create your curriculum to get started.</p>
        </div>
    </div>
@else
    <div class="row">
        @foreach($subjects as $subject)
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm" style="border-radius: 12px; overflow: hidden; transition: transform 0.2s;">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge" style="background-color: #f1f5f9; color: #475569; padding: 0.4rem 0.6rem; border-radius: 6px; font-weight: 700;">
                                {{ $subject->subject_code }}
                            </span>
                            @if($subject->is_elective)
                                <span class="badge badge-pill shadow-xs" style="background-color: #fdf4ff; color: #a21caf; font-size: 0.7rem; font-weight: 700;">
                                    ELECTIVE
                                </span>
                            @else
                                <span class="badge badge-pill shadow-xs" style="background-color: #f0fdf4; color: #166534; font-size: 0.7rem; font-weight: 700;">
                                    CORE
                                </span>
                            @endif
                        </div>
                        
                        <h5 class="font-weight-bold text-dark mb-2" style="font-size: 1.15rem;">
                            {{ $subject->name }}
                        </h5>
                        
                        <p class="text-muted small mb-4" style="line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; height: 3em;">
                            {{ $subject->description ?: 'No description provided for this subject.' }}
                        </p>
                        
                        <div class="mt-auto pt-3 border-top d-flex justify-content-end">
                            <div class="btn-group shadow-sm">
                                <a href="{{ route('subjects.show', $subject->subject_id) }}" class="btn btn-light btn-sm px-3" style="background-color: #ffffff; border-color: #f1f5f9; color: #334155;">
                                    <i class="fas fa-eye text-primary"></i>
                                </a>
                                <a href="{{ route('subjects.edit', $subject->subject_id) }}" class="btn btn-light btn-sm px-3" style="background-color: #ffffff; border-color: #f1f5f9; color: #334155;">
                                    <i class="fas fa-edit text-info"></i>
                                </a>
                                {!! Form::open(['route' => ['subjects.destroy', $subject->subject_id], 'method' => 'delete', 'class' => 'd-inline']) !!}
                                    {!! Form::button('<i class="fas fa-trash-alt text-danger"></i>', [
                                        'type' => 'submit',
                                        'class' => 'btn btn-light btn-sm px-3',
                                        'style' => 'background-color: #ffffff; border-color: #f1f5f9;',
                                        'onclick' => "return confirm('Are you sure you want to delete this subject?')"
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
        @include('adminlte-templates::common.paginate', ['records' => $subjects])
    </div>
@endif
