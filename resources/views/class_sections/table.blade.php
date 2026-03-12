@if($classSections->isEmpty())
    <div class="alert alert-info text-center">
        <i class="fas fa-info-circle mr-2"></i> No class sections found. Create some class sections to get started.
    </div>
@else
    <div class="row" id="class-sections-grid">
        @foreach($classSections as $className => $sectionsGroup)
            <div class="col-lg-4 col-md-6 col-sm-12 class-group" data-group-name="{{ strtolower($className) }}">
                <div class="card card-primary card-outline mb-4 h-100">
                    <div class="card-header">
                        <h3 class="card-title text-bold text-capitalize">
                           <i class="fas fa-chalkboard text-primary mr-2"></i> {{ $className }}
                        </h3>
                        <div class="card-tools">
                             <span class="badge badge-primary mr-2">{{ $sectionsGroup->count() }} Sections</span>
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0" style="max-height: 350px; overflow-y: auto;">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0 table-sm">
                                <thead class="bg-light sticky-top">
                                <tr>
                                    <th style="width: 20%">Section</th>
                                    <th style="width: 25%">Room</th>
                                    <th style="width: 35%">Teacher</th>
                                    <th style="width: 20%" class="text-center">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($sectionsGroup as $classSection)
                                    <tr class="section-row" data-section-name="{{ strtolower($classSection->section->name) }}">
                                        <td class="align-middle font-weight-bold">
                                            {{ $classSection->section->name }}
                                        </td>
                                        <td class="align-middle">
                                            <small class="text-muted"><i class="fas fa-door-open mr-1"></i> {{ optional($classSection->classroom)->room_number ?: 'N/A' }}</small>
                                        </td>
                                        <td class="align-middle">
                                             <small class="d-block text-truncate" style="max-width: 120px;" title="{{ $classSection->classTeacher ? $classSection->classTeacher->first_name . ' ' . $classSection->classTeacher->last_name : 'No Teacher' }}">
                                                <i class="fas fa-user-tie mr-1"></i> {{ $classSection->classTeacher ? $classSection->classTeacher->first_name . ' ' . $classSection->classTeacher->last_name : 'N/A' }}
                                             </small>
                                        </td>
                                        <td class="text-center align-middle">
                                            {!! Form::open(['route' => ['class-sections.destroy', $classSection->class_section_id], 'method' => 'delete']) !!}
                                            <div class='btn-group'>
                                                <a href="{{ route('class-sections.show', [$classSection->class_section_id]) }}"
                                                   class='btn btn-xs btn-default text-info shadow-sm' title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('class-sections.edit', [$classSection->class_section_id]) }}"
                                                   class='btn btn-xs btn-default text-primary shadow-sm' title="Edit">
                                                    <i class="fas fa-pencil-alt"></i>
                                                </a>
                                                {!! Form::button('<i class="fas fa-trash text-danger"></i>', [
                                                    'type' => 'submit',
                                                    'class' => 'btn btn-xs btn-default shadow-sm',
                                                    'onclick' => "return confirm('Are you sure you want to delete this?')",
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
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
