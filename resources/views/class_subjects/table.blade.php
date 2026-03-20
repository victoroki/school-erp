@if($classSubjects->isEmpty())
    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
        <div class="card-body text-center p-5 text-muted">
            <i class="fas fa-book-reader fa-3x mb-3 text-light"></i>
            <p class="mb-0">No class subjects found. Assign some subjects to classes to get started.</p>
        </div>
    </div>
@else
    <div class="row" id="class-subjects-grid">
        @foreach($classSubjects as $className => $subjectsGroup)
            <div class="col-lg-4 col-md-6 col-sm-12 class-group mb-4" data-group-name="{{ strtolower($className) }}">
                <div class="card h-100 shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
                    <!-- Sleek Header -->
                    <div class="card-header border-bottom-0 d-flex justify-content-between align-items-center" style="background-color: #f8fafc; padding: 1rem 1.25rem;">
                        <div class="d-flex align-items-center">
                            <div style="background-color: #e0f2fe; color: #0284c7; width: 32px; height: 32px; border-radius: 8px; display: flex; justify-content: center; align-items: center; margin-right: 12px;">
                                <i class="fas fa-book-reader"></i>
                            </div>
                            <h3 class="card-title text-dark font-weight-bold mb-0" style="font-size: 1.1rem; line-height: 1;">
                                {{ $className }}
                            </h3>
                        </div>
                        <div class="d-flex align-items-center">
                             <span class="badge" style="background-color: #fee2e2; color: #ef4444; font-size: 0.75rem; padding: 5px 8px; border-radius: 6px; margin-right: 10px;">
                                 {{ $subjectsGroup->count() }} Subjects
                             </span>
                            <button type="button" class="btn btn-sm btn-tool text-secondary p-0" data-card-widget="collapse" style="box-shadow: none;">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Clean List Body -->
                    <div class="card-body p-0" style="max-height: 350px; overflow-y: auto; background-color: #ffffff;">
                        <ul class="list-group list-group-flush">
                            @foreach($subjectsGroup as $classSubject)
                                <li class="list-group-item subject-row d-flex justify-content-between align-items-center text-dark" 
                                    data-subject-name="{{ strtolower($classSubject->subject->name) }}"
                                    style="padding: 0.85rem 1.25rem; border-color: #f1f5f9;">
                                    
                                    <div class="pr-2">
                                        <p class="mb-0 font-weight-bold" style="font-size: 0.95rem;">{{ $classSubject->subject->name }}</p>
                                        <div class="d-flex mt-1">
                                            <span class="badge" style="background-color: #f1f5f9; color: #475569; font-size: 0.7rem; font-weight: 600;">{{ $classSubject->academicYear->name }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex" style="gap: 5px;">
                                        {!! Form::open(['route' => ['class-subjects.destroy', $classSubject->class_subject_id], 'method' => 'delete', 'class' => 'm-0']) !!}
                                        <div class="btn-group btn-group-sm rounded shadow-xs" style="overflow: hidden;">
                                            <a href="{{ route('class-subjects.show', [$classSubject->class_subject_id]) }}" class="btn btn-light text-info" title="View" style="background-color: #f8fafc; border-color: #f1f5f9;">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('class-subjects.edit', [$classSubject->class_subject_id]) }}" class="btn btn-light text-primary" title="Edit" style="background-color: #f8fafc; border-color: #f1f5f9;">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            {!! Form::button('<i class="fas fa-trash-alt"></i>', [
                                                'type' => 'submit',
                                                'class' => 'btn btn-light text-danger',
                                                'style' => 'background-color: #f8fafc; border-color: #f1f5f9;',
                                                'onclick' => "return confirm('Are you sure you want to delete this?')"
                                            ]) !!}
                                        </div>
                                        {!! Form::close() !!}
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
