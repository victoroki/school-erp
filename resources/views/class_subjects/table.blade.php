@if($classSubjects->isEmpty())
    <div class="alert alert-info text-center">
        <i class="fas fa-info-circle mr-2"></i> No class subjects found. Assign some subjects to classes to get started.
    </div>
@else
    <div class="row" id="class-subjects-grid">
        @foreach($classSubjects as $className => $subjectsGroup)
            <div class="col-lg-4 col-md-6 col-sm-12 class-group" data-group-name="{{ strtolower($className) }}">
                <div class="card card-primary card-outline mb-4 h-100">
                    <div class="card-header">
                        <h3 class="card-title text-bold text-capitalize">
                           <i class="fas fa-book-reader text-primary mr-2"></i> {{ $className }}
                        </h3>
                        <div class="card-tools">
                             <span class="badge badge-primary mr-2">{{ $subjectsGroup->count() }} Subjects</span>
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
                                    <th style="width: 40%">Subject</th>
                                    <th style="width: 30%">Academic Year</th>
                                    <th style="width: 30%" class="text-center">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($subjectsGroup as $classSubject)
                                    <tr class="subject-row" data-subject-name="{{ strtolower($classSubject->subject->name) }}">
                                        <td class="align-middle font-weight-bold">
                                            {{ $classSubject->subject->name }}
                                        </td>
                                        <td class="align-middle">
                                            <span class="badge badge-light border">{{ $classSubject->academicYear->name }}</span>
                                        </td>
                                        <td class="text-center align-middle">
                                            {!! Form::open(['route' => ['class-subjects.destroy', $classSubject->class_subject_id], 'method' => 'delete']) !!}
                                            <div class='btn-group'>
                                                <a href="{{ route('class-subjects.show', [$classSubject->class_subject_id]) }}"
                                                   class='btn btn-xs btn-default text-info shadow-sm' title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('class-subjects.edit', [$classSubject->class_subject_id]) }}"
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
