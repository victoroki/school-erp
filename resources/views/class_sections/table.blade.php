@if($classSections->isEmpty())
    <div class="dash-panel p-5 text-center">
        <div class="icon-wrap bg-slate-light text-slate mx-auto mb-3" style="width: 56px; height: 56px; border-radius: 16px;">
            <i class="fas fa-ghost"></i>
        </div>
        <p class="text-muted fw-500 small">No class sections found. Begin your architecture by adding a new section.</p>
    </div>
@else
    <div class="row g-5" id="class-sections-grid">
        @foreach($classSections as $className => $sectionsGroup)
            <div class="col-xl-4 col-lg-6 class-group mb-4" data-group-name="{{ strtolower($className) }}">
                <div class="class-card">
                    {{-- Card Header --}}
                    <div class="class-card-header">
                        <div>
                            <h3 class="class-title text-indigo">{{ $className }}</h3>
                            <p class="class-meta">{{ $sectionsGroup->count() }} Defined Sections</p>
                        </div>
                    </div>

                    {{-- Section List --}}
                    <div class="class-card-body">
                        <div class="section-stack">
                            @foreach($sectionsGroup as $classSection)
                                <div class="section-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="section-info">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <div class="status-dot bg-indigo"></div>
                                                <span class="section-name">Section {{ $classSection->section->name }}</span>
                                            </div>
                                            <div class="section-details d-flex gap-3">
                                                <span title="Room Number">
                                                    <i class="fas fa-door-open me-1 opacity-50"></i> {{ optional($classSection->classroom)->room_number ?: 'TBD' }}
                                                </span>
                                                <span class="text-truncate" style="max-width: 140px;" title="Teacher: {{ $classSection->classTeacher ? $classSection->classTeacher->first_name . ' ' . $classSection->classTeacher->last_name : 'Unassigned' }}">
                                                    <i class="fas fa-user-tie me-1 opacity-50"></i> {{ $classSection->classTeacher ? $classSection->classTeacher->first_name : 'Staff TBD' }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="section-actions">
                                            <div class="action-cluster">
                                                <a href="{{ route('class-sections.show', [$classSection->class_section_id]) }}" class="micro-btn" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('class-sections.edit', [$classSection->class_section_id]) }}" class="micro-btn" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                {!! Form::open(['route' => ['class-sections.destroy', $classSection->class_section_id], 'method' => 'delete', 'class' => 'm-0 d-inline']) !!}
                                                    {!! Form::button('<i class="fas fa-trash-alt"></i>', [
                                                        'type' => 'submit',
                                                        'class' => 'micro-btn btn-del',
                                                        'onclick' => "return confirm('Are you sure?')"
                                                    ]) !!}
                                                {!! Form::close() !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

<style>
/* ── Class Card Architecture ── */
.class-card { background: #fff; border: 1px solid var(--border); border-radius: 16px; transition: all 300ms var(--ease-out); height: 100%; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 1px 2px rgba(0,0,0,0.02); }
.class-card:hover { transform: translateY(-3px); box-shadow: 0 8px 16px rgba(0,0,0,0.04); border-color: #cbd5e1; }

.class-card-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid #f8fafc; background: linear-gradient(to bottom, #fff, #fdfdfd); }
.class-title { font-size: 1.063rem; font-weight: 800; margin: 0; letter-spacing: -0.02em; }
.class-meta { font-size: 0.65rem; color: var(--muted); font-weight: 700; margin: 2px 0 0; text-transform: uppercase; letter-spacing: 0.08em; }

.class-card-body { padding: 0; flex: 1; overflow-y: auto; max-height: 350px; }

/* Section List */
.section-stack { display: flex; flex-direction: column; }
.section-item { padding: 1rem 1.5rem; border-bottom: 1px solid #f8fafc; transition: background 150ms ease; }
.section-item:last-child { border-bottom: 0; }
.section-item:hover { background-color: #fbfbfc; }

.section-name { font-size: 0.875rem; font-weight: 700; color: var(--text); }
.status-dot { width: 5px; height: 5px; border-radius: 50%; opacity: 0.7; }
.section-details { font-size: 0.7rem; color: var(--muted); font-weight: 600; letter-spacing: 0.01em; }

/* Action Cluster */
.action-cluster { background: #f1f5f9; padding: 3px; border-radius: 8px; display: flex; gap: 2px; }
.micro-btn { width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; color: var(--slate); font-size: 0.7rem; transition: all 150ms var(--ease-out); background: transparent; border: none; text-decoration: none !important; cursor: pointer; }
.micro-btn:hover { background: #fff; color: var(--indigo); box-shadow: 0 2px 4px rgba(0,0,0,0.04); }
.btn-del:hover { color: var(--rose) !important; }

.gap-3 { gap: 0.625rem; }
.fw-500 { font-weight: 500; }
.text-indigo { color: var(--indigo) !important; }
</style>
