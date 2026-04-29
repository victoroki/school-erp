@extends('layouts.app')

@section('content')
<div class="dash-wrap">
    {{-- ① HEADER --}}
    <div class="row align-items-center mb-4">
        <div class="col-md-7">
            <h1 class="dash-heading">Academic Management</h1>
            <p class="dash-sub">Overview of classes, subjects, schedules, and operations</p>
        </div>
        <div class="col-md-5 text-md-end mt-2 mt-md-0">
            <ol class="breadcrumb float-sm-right m-0 bg-transparent p-0" style="font-size: .813rem; font-weight: 600;">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-muted text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active text-dark">Academic Dashboard</li>
            </ol>
        </div>
    </div>

    {{-- ② QUICK STATS --}}
    <div class="row mb-4">
        <div class="col-12 col-sm-6 col-md-3 mb-3 mb-md-0">
            <div class="stat-card">
                <div class="stat-icon bg-blue-light text-blue"><i class="fas fa-chalkboard"></i></div>
                <div class="stat-info">
                    <span class="stat-label">Total Classes</span>
                    <span class="stat-value">{{ $stats['total_classes'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3 mb-3 mb-md-0">
            <div class="stat-card">
                <div class="stat-icon bg-emerald-light text-emerald"><i class="fas fa-book-open"></i></div>
                <div class="stat-info">
                    <span class="stat-label">Subjects</span>
                    <span class="stat-value">{{ $stats['total_subjects'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3 mb-3 mb-md-0">
            <div class="stat-card">
                <div class="stat-icon bg-amber-light text-amber"><i class="fas fa-user-tie"></i></div>
                <div class="stat-info">
                    <span class="stat-label">Teachers</span>
                    <span class="stat-value">{{ $stats['total_teachers'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-rose-light text-rose"><i class="fas fa-user-graduate"></i></div>
                <div class="stat-info">
                    <span class="stat-label">Students</span>
                    <span class="stat-value">{{ $stats['total_students'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Today's Lessons -->
        <div class="col-lg-8 mb-4 mb-lg-0">
            <div class="dash-panel h-100">
                <div class="dash-panel-header">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-calendar-day text-indigo"></i>
                        <h3 class="dash-panel-title">Today's Live Schedule ({{ ucfirst(now()->format('l')) }})</h3>
                    </div>
                    <span class="badge-count">{{ $todayLessons->count() }} Lessons</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Subject</th>
                                <th>Class</th>
                                <th>Teacher</th>
                                <th class="text-right">Room</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($todayLessons->sortBy('period.start_time') as $lesson)
                                <tr>
                                    <td class="font-weight-bold text-indigo" style="font-size: .813rem;">{{ \Carbon\Carbon::parse($lesson->period->start_time)->format('H:i') }}</td>
                                    <td><span class="font-weight-bold text-dark" style="font-size: .875rem;">{{ $lesson->subject->name }}</span></td>
                                    <td><span class="badge-soft">{{ $lesson->classSection->class->name ?? '' }} {{ $lesson->classSection->section->name ?? '' }}</span></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="{{ $lesson->teacher->photo_url ?? asset('garikon-black.png') }}" alt="Teacher" class="avatar-sm">
                                            <span style="font-size: .813rem; font-weight: 600;">{{ $lesson->teacher->full_name ?? 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td class="text-right"><span class="text-muted" style="font-size: .813rem;"><i class="fas fa-door-open mr-1"></i> {{ $lesson->classroom->room_number }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="fas fa-mug-hot fa-2x mb-2" style="color: #cbd5e1;"></i>
                                        <p class="mb-0" style="font-size: .875rem;">No lessons scheduled for today.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="dash-panel-footer text-center">
                    <a href="{{ route('timetables.index') }}" class="text-indigo text-decoration-none" style="font-size: .813rem; font-weight: 600;">View Full Timetable <i class="fas fa-arrow-right ml-1"></i></a>
                </div>
            </div>
        </div>

        <!-- Side Widgets -->
        <div class="col-lg-4">
            <!-- Classroom Utilization -->
            <div class="dash-panel mb-4">
                <div class="dash-panel-header">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-th text-emerald"></i>
                        <h3 class="dash-panel-title">Venue Utilization</h3>
                    </div>
                </div>
                <div class="dash-panel-body">
                    @forelse($utilization as $item)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: .75rem; font-weight: 600;">
                                <span>Room {{ $item['room'] }}</span>
                                <span class="text-dark">{{ $item['rate'] }}%</span>
                            </div>
                            <div class="progress" style="height: 6px; border-radius: 3px; background-color: #f1f5f9;">
                                <div class="progress-bar bg-emerald" style="width: {{ $item['rate'] }}%; border-radius: 3px;"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-muted mb-0 py-3" style="font-size: .813rem;">No room data for today.</p>
                    @endforelse
                </div>
            </div>

            <!-- Recent Changes -->
            <div class="dash-panel mb-4">
                <div class="dash-panel-header">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-history text-amber"></i>
                        <h3 class="dash-panel-title">Recent Changes</h3>
                    </div>
                </div>
                <div class="dash-panel-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($recentChanges as $change)
                            <li class="list-group-item d-flex justify-content-between align-items-start border-light py-3">
                                <div>
                                    <div class="font-weight-bold text-dark" style="font-size: .813rem;">{{ $change->subject->name }}</div>
                                    <div class="text-muted mt-1" style="font-size: .75rem;">Schedule updated for {{ $change->classSection->class->name ?? 'Unknown' }}</div>
                                </div>
                                <span class="text-muted" style="font-size: .688rem; font-weight: 600;">{{ $change->updated_at->diffForHumans(null, true, true) }}</span>
                            </li>
                        @empty
                            <li class="list-group-item text-center py-4 text-muted border-0" style="font-size: .813rem;">No recent changes.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="dash-panel">
                <div class="dash-panel-header">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-bolt text-blue"></i>
                        <h3 class="dash-panel-title">Quick Actions</h3>
                    </div>
                </div>
                <div class="dash-panel-body p-2">
                    <div class="row m-0">
                        <div class="col-6 p-1">
                            <a href="{{ route('timetables.create') }}" class="qa-btn">
                                <i class="fas fa-plus mb-1"></i> Add Lesson
                            </a>
                        </div>
                        <div class="col-6 p-1">
                            <a href="{{ route('subjects.create') }}" class="qa-btn">
                                <i class="fas fa-book mb-1"></i> New Subject
                            </a>
                        </div>
                        <div class="col-6 p-1">
                            <a href="{{ route('school-classes.index') }}" class="qa-btn">
                                <i class="fas fa-chalkboard mb-1"></i> Manage Classes
                            </a>
                        </div>
                        <div class="col-6 p-1">
                            <a href="{{ route('academic-calendar.index') }}" class="qa-btn">
                                <i class="fas fa-calendar-plus mb-1"></i> Add Event
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* ── Emil Kowalski Utility Suite ── */
:root {
    --blue: #3b82f6; --blue-light: #eff6ff;
    --indigo: #4f46e5; --indigo-light: #eef2ff;
    --emerald: #10b981; --emerald-light: #ecfdf5;
    --amber: #f59e0b; --amber-light: #fffbeb;
    --rose: #f43f5e; --rose-light: #fff1f2;
    --slate: #64748b;
    --text: #0f172a;
    --muted: #64748b;
    --border: #e2e8f0;
    --ease-out: cubic-bezier(0.16, 1, 0.3, 1);
}

.bg-blue-light { background: var(--blue-light); } .text-blue { color: var(--blue); }
.bg-indigo-light { background: var(--indigo-light); } .text-indigo { color: var(--indigo); }
.bg-emerald-light { background: var(--emerald-light); } .text-emerald { color: var(--emerald); }
.bg-amber-light { background: var(--amber-light); } .text-amber { color: var(--amber); }
.bg-rose-light { background: var(--rose-light); } .text-rose { color: var(--rose); }

.dash-wrap { padding: 1rem; }
.dash-heading { font-size: 1.375rem; font-weight: 800; color: var(--text); letter-spacing: -0.02em; margin-bottom: 0.125rem; }
.dash-sub { font-size: 0.813rem; color: var(--muted); font-weight: 500; margin-bottom: 0; }

.dash-panel { background: #fff; border: 1px solid var(--border); border-radius: 14px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); overflow: hidden; display: flex; flex-direction: column; }
.dash-panel-header { padding: 1rem 1.25rem; background: #fff; border-bottom: 1px solid #f8fafc; display: flex; align-items: center; justify-content: space-between; }
.dash-panel-title { font-size: .875rem; font-weight: 800; color: var(--text); margin: 0; }
.dash-panel-body { padding: 1.25rem; flex: 1; }
.dash-panel-footer { padding: 1rem; background: #f8fafc; border-top: 1px solid var(--border); }

/* Quick Stats */
.stat-card { background: #fff; border: 1px solid var(--border); border-radius: 12px; padding: 1.25rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.02); transition: all 200ms var(--ease-out); }
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 12px rgba(0,0,0,0.05); border-color: #cbd5e1; }
.stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
.stat-info { display: flex; flex-direction: column; }
.stat-label { font-size: .75rem; color: var(--muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; }
.stat-value { font-size: 1.5rem; font-weight: 800; color: var(--text); line-height: 1.1; margin-top: .25rem; }

/* Table Styling */
.table { margin-bottom: 0; }
.table thead th { background: #f8fafc; border-bottom: 1px solid var(--border); font-size: .688rem; font-weight: 800; text-transform: uppercase; color: var(--slate); letter-spacing: 0.05em; padding: .625rem 1.25rem; }
.table tbody td { padding: .75rem 1.25rem; vertical-align: middle; border-bottom: 1px solid #f8fafc; border-top: 0; }
.table tbody tr:last-child td { border-bottom: 0; }
.table-hover tbody tr:hover { background-color: #f8fafc; }

.badge-count { background: var(--indigo-light); color: var(--indigo); font-size: .688rem; font-weight: 800; padding: .25rem .5rem; border-radius: 6px; }
.badge-soft { background: #f1f5f9; color: #475569; font-size: .688rem; font-weight: 700; padding: .2rem .5rem; border-radius: 6px; }

.avatar-sm { width: 28px; height: 28px; border-radius: 50%; object-fit: cover; border: 1px solid var(--border); }

/* Quick Actions */
.qa-btn { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 1rem .5rem; background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 10px; color: var(--slate); text-decoration: none !important; transition: all 150ms var(--ease-out); font-size: .75rem; font-weight: 600; text-align: center; }
.qa-btn i { font-size: 1.125rem; color: var(--text); transition: all 150ms ease; }
.qa-btn:hover { background: #fff; border-color: var(--blue); color: var(--blue); box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1); }
.qa-btn:hover i { color: var(--blue); transform: translateY(-2px); }
</style>
@endsection
