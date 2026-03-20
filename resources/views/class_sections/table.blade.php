@if($classSections->isEmpty())
    <div class="alert alert-info text-center">
        <i class="fas fa-info-circle mr-2"></i> No class sections found. Create some class sections to get started.
    </div>
@else
    <div class="row" id="class-sections-grid">
        @foreach($classSections as $className => $sectionsGroup)
            <div class="col-lg-4 col-md-6 col-sm-12 class-group mb-4" data-group-name="{{ strtolower($className) }}">
                <div class="card h-100 shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
                    <!-- Sleek Header -->
                    <div class="card-header border-bottom-0 d-flex justify-content-between align-items-center" style="background-color: #f8fafc; padding: 1rem 1.25rem;">
                        <div class="d-flex align-items-center">
                            <div style="background-color: #e0f2fe; color: #0284c7; width: 32px; height: 32px; border-radius: 8px; display: flex; justify-content: center; align-items: center; margin-right: 12px;">
                                <i class="fas fa-chalkboard"></i>
                            </div>
                            <h3 class="card-title text-dark font-weight-bold mb-0" style="font-size: 1.1rem; line-height: 1;">
                                {{ $className }}
                            </h3>
                        </div>
                        <div class="d-flex align-items-center">
                             <span class="badge" style="background-color: #fee2e2; color: #ef4444; font-size: 0.75rem; padding: 5px 8px; border-radius: 6px; margin-right: 10px;">
                                 {{ $sectionsGroup->count() }}
                             </span>
                            <button type="button" class="btn btn-sm btn-tool text-secondary p-0" data-card-widget="collapse" style="box-shadow: none;">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Clean List Body -->
                    <div class="card-body p-0" style="max-height: 350px; overflow-y: auto; background-color: #ffffff;">
                        <ul class="list-group list-group-flush">
                            @foreach($sectionsGroup as $classSection)
                                <li class="list-group-item section-row d-flex justify-content-between align-items-center" 
                                    data-section-name="{{ strtolower($classSection->section->name) }}"
                                    style="padding: 0.85rem 1.25rem; border-color: #f1f5f9;">
                                    
                                    <div class="pr-2">
                                        <p class="mb-0 font-weight-bold text-dark" style="font-size: 0.95rem;">Section {{ $classSection->section->name }}</p>
                                        <div class="d-flex mt-1" style="gap: 12px;">
                                            <p class="text-muted mb-0 d-flex align-items-center" style="font-size: 0.75rem;">
                                                <i class="fas fa-door-open mr-1 text-secondary"></i> {{ optional($classSection->classroom)->room_number ?: 'N/A' }}
                                            </p>
                                            <p class="text-muted mb-0 d-flex align-items-center text-truncate" style="font-size: 0.75rem; max-width: 140px;" title="{{ $classSection->classTeacher ? $classSection->classTeacher->first_name . ' ' . $classSection->classTeacher->last_name : 'No Teacher' }}">
                                                <i class="fas fa-user-tie mr-1 text-secondary"></i> {{ $classSection->classTeacher ? $classSection->classTeacher->first_name . ' ' . $classSection->classTeacher->last_name : 'N/A' }}
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        {!! Form::open(['route' => ['class-sections.destroy', $classSection->class_section_id], 'method' => 'delete', 'class' => 'm-0']) !!}
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('class-sections.show', [$classSection->class_section_id]) }}" class="btn btn-light text-info rounded-left" title="View" style="background-color: #f8fafc; border-color: #e2e8f0;">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('class-sections.edit', [$classSection->class_section_id]) }}" class="btn btn-light text-primary" title="Edit" style="background-color: #f8fafc; border-color: #e2e8f0;">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            {!! Form::button('<i class="fas fa-trash-alt"></i>', [
                                                'type' => 'submit',
                                                'class' => 'btn btn-light text-danger rounded-right',
                                                'style' => 'background-color: #f8fafc; border-color: #e2e8f0;',
                                                'onclick' => "return confirm('Are you sure you want to delete this?')",
                                                'title' => 'Delete'
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
