@if($classSubjects->isEmpty())
    <div class="dash-alert">
        <div class="da-icon">
            <i class="fas fa-book-reader"></i>
        </div>
        <div class="da-body">
            <h4 class="da-title">No class subjects found</h4>
            <p class="da-desc">Assign some subjects to classes to get started and they will appear here.</p>
        </div>
    </div>
@else
    <div class="row" id="class-subjects-grid">
        @foreach($classSubjects as $className => $subjectsGroup)
            <div class="col-lg-4 col-md-6 col-sm-12 class-group mb-4" data-group-name="{{ strtolower($className) }}">
                <div class="cs-card">
                    <div class="cs-header">
                        <div class="cs-title">
                            <i class="fas fa-layer-group"></i>
                            {{ $className }}
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge-count">{{ $subjectsGroup->count() }} Subjects</span>
                            <a href="#" class="cs-toggle text-muted"><i class="fas fa-minus"></i></a>
                        </div>
                    </div>
                    
                    <div class="cs-body">
                        @foreach($subjectsGroup as $classSubject)
                            <div class="cs-item" data-subject-name="{{ strtolower($classSubject->subject->name) }}">
                                <div>
                                    <span class="cs-name">{{ $classSubject->subject->name }}</span>
                                    <span class="cs-desc">Year: {{ $classSubject->academicYear->name }}</span>
                                </div>
                                <div class="d-flex gap-1">
                                    {!! Form::open(['route' => ['class-subjects.destroy', $classSubject->class_subject_id], 'method' => 'delete', 'class' => 'm-0 d-flex justify-content-end gap-1']) !!}
                                    <a href="{{ route('class-subjects.show', [$classSubject->class_subject_id]) }}" class="action-btn" title="View Details">
                                        <i class="far fa-eye"></i>
                                    </a>
                                    <a href="{{ route('class-subjects.edit', [$classSubject->class_subject_id]) }}" class="action-btn" title="Edit Subject Assignment">
                                        <i class="far fa-edit"></i>
                                    </a>
                                    {!! Form::button('<i class="far fa-trash-alt"></i>', [
                                        'type' => 'submit', 
                                        'class' => 'action-btn btn-delete', 
                                        'title' => 'Remove Subject',
                                        'onclick' => "return confirm('Are you sure you want to remove this subject from the class?')"
                                    ]) !!}
                                    {!! Form::close() !!}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
