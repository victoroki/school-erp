@if($sections->isEmpty())
    <div class="alert alert-info text-center">
        <i class="fas fa-info-circle mr-2"></i> No sections found. Create some sections to get started.
    </div>
@else
    <div class="row" id="sections-grid">
        @foreach($sections as $className => $sectionsGroup)
            <div class="col-lg-4 col-md-6 col-sm-12 section-group" data-group-name="{{ strtolower($className) }}">
                <div class="card card-primary card-outline mb-4 h-100">
                    <div class="card-header">
                        <h3 class="card-title text-bold text-capitalize">
                           <i class="fas fa-chalkboard-teacher text-primary mr-2"></i> {{ $className }}
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
                                    <th style="width: 40%">Name</th>
                                    <th style="width: 30%">Capacity</th>
                                    <th style="width: 30%" class="text-center">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($sectionsGroup as $section)
                                    <tr class="section-row" data-section-name="{{ strtolower($section->name) }}">
                                        <td class="align-middle font-weight-bold">
                                            {{ $section->name }}
                                        </td>
                                        <td class="align-middle">
                                            <span class="badge badge-info">{{ $section->capacity ?? 'N/A' }}</span>
                                        </td>
                                        <td class="text-center align-middle">
                                            {!! Form::open(['route' => ['sections.destroy', $section->section_id], 'method' => 'delete']) !!}
                                            <div class='btn-group'>
                                                <a href="{{ route('sections.show', [$section->section_id]) }}"
                                                   class='btn btn-xs btn-default text-info shadow-sm' title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('sections.edit', [$section->section_id]) }}"
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
