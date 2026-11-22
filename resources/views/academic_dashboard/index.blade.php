@extends('layouts.app')

@include('partials.minimal-ui')

@section('content')
@php
    $lessons = $todayLessons->sortBy(fn ($l) => $l->period->start_time ?? '99:99');
    $dayName = now()->format('l');
@endphp

<div class="ba ad">

    <header class="ba-head">
        <div>
            <h1 class="ba-title">Academics</h1>
            <p class="ba-sub">
                @if($isManager)
                    Classes, subjects and today's timetable across the school.
                @else
                    Your lessons today and the tools you use in class.
                @endif
            </p>
        </div>
        @if($currentYear)
            <span class="ba-tag">{{ $currentYear->year_name ?? $currentYear->name ?? 'Current year' }}</span>
        @endif
    </header>

    @if($isManager)
        <dl class="ba-summary ad-summary">
            <div class="ba-stat">
                <dt>Classes</dt>
                <dd class="ba-num">{{ number_format($stats['total_classes']) }}</dd>
            </div>
            <div class="ba-stat">
                <dt>Subjects</dt>
                <dd class="ba-num">{{ number_format($stats['total_subjects']) }}</dd>
            </div>
            <div class="ba-stat">
                <dt>Teachers</dt>
                <dd class="ba-num">{{ number_format($stats['total_teachers']) }}</dd>
            </div>
            <div class="ba-stat">
                <dt>Active students</dt>
                <dd class="ba-num">{{ number_format($stats['total_students']) }}</dd>
            </div>
        </dl>
    @endif

    <div class="ad-grid">

        {{-- Today's timetable --}}
        <section class="ad-main" aria-labelledby="ad-today">
            <div class="ad-section-head">
                <h2 id="ad-today" class="ad-h2">Today, {{ $dayName }}</h2>
                <span class="ad-count">{{ $lessons->count() }} {{ \Illuminate\Support\Str::plural('lesson', $lessons->count()) }}</span>
            </div>

            <div class="ba-list" role="table" aria-label="Today's lessons">
                <div class="ba-row ad-row ba-row-head" role="row">
                    <span role="columnheader">Time</span>
                    <span role="columnheader">Subject</span>
                    <span role="columnheader">Class</span>
                    <span role="columnheader">Teacher</span>
                    <span role="columnheader" class="ba-right">Room</span>
                </div>

                @forelse($lessons as $lesson)
                    <div class="ba-row ad-row" role="row">
                        <span class="ba-date" role="cell">
                            {{ $lesson->period?->start_time ? \Carbon\Carbon::parse($lesson->period->start_time)->format('H:i') : '--:--' }}
                        </span>
                        <span class="ba-strong" role="cell">{{ $lesson->subject->name ?? 'Unassigned subject' }}</span>
                        <span role="cell">
                            <span class="ba-tag">{{ trim(($lesson->classSection?->class?->name ?? '').' '.($lesson->classSection?->section?->name ?? '')) ?: 'No class' }}</span>
                        </span>
                        <span class="ba-small ad-teacher" role="cell">{{ $lesson->teacher->full_name ?? 'No teacher' }}</span>
                        <span class="ba-right ba-date" role="cell">{{ $lesson->classroom->room_number ?? '—' }}</span>
                    </div>
                @empty
                    <div class="ba-empty-row">
                        {{ $isManager ? 'No lessons are scheduled for today.' : 'You have no lessons scheduled today.' }}
                    </div>
                @endforelse
            </div>

            <a href="{{ route('timetables.index') }}" class="ba-link ad-more">Full timetable</a>
        </section>

        {{-- Side column --}}
        <aside class="ad-side">
            @if($isManager)
                <section aria-labelledby="ad-rooms">
                    <div class="ad-section-head">
                        <h2 id="ad-rooms" class="ad-h2">Room use today</h2>
                    </div>
                    @forelse($utilization as $item)
                        <div class="ad-meter">
                            <div class="ba-min-line">
                                <span class="ba-small" style="color: var(--ba-ink-2)">Room {{ $item['room'] }}</span>
                                <span class="ba-date">{{ $item['rate'] }}%</span>
                            </div>
                            <div class="ba-track" aria-hidden="true"><span class="ba-fill ad-fill" style="width: {{ min(100, $item['rate']) }}%"></span></div>
                        </div>
                    @empty
                        <p class="ad-note">No rooms are booked today.</p>
                    @endforelse
                </section>

                <section aria-labelledby="ad-changes">
                    <div class="ad-section-head">
                        <h2 id="ad-changes" class="ad-h2">Recent timetable changes</h2>
                    </div>
                    <ul class="ad-list">
                        @forelse($recentChanges as $change)
                            <li>
                                <div class="ba-cell">
                                    <span class="ba-strong">{{ $change->subject->name ?? 'Unassigned subject' }}</span>
                                    <span class="ba-small">{{ $change->classSection?->class?->name ?? 'Unknown class' }}</span>
                                </div>
                                <time class="ba-small" datetime="{{ $change->updated_at?->toIso8601String() }}">{{ $change->updated_at?->diffForHumans(null, true, true) }}</time>
                            </li>
                        @empty
                            <li class="ad-note">No recent changes.</li>
                        @endforelse
                    </ul>
                </section>
            @endif

            <section aria-labelledby="ad-actions">
                <div class="ad-section-head">
                    <h2 id="ad-actions" class="ad-h2">Shortcuts</h2>
                </div>
                <ul class="ad-list ad-links">
                    @if($isManager)
                        <li><a href="{{ route('timetables.create') }}">Add a lesson</a></li>
                        <li><a href="{{ route('subjects.create') }}">New subject</a></li>
                        <li><a href="{{ route('school-classes.index') }}">Manage classes</a></li>
                        <li><a href="{{ route('academic-calendar.index') }}">Academic calendar</a></li>
                    @else
                        <li><a href="{{ route('timetables.teacher') }}">My timetable</a></li>
                        <li><a href="{{ route('exam-results.bulk') }}">Enter marks</a></li>
                        <li><a href="{{ route('student-attendance.index') }}">Take attendance</a></li>
                        <li><a href="{{ route('grade-book.index') }}">Grade book</a></li>
                    @endif
                </ul>
            </section>
        </aside>
    </div>
</div>

<style>
    .ad-summary { grid-template-columns: repeat(4, 1fr); }
    .ad-summary .ba-stat dd { font-size: 22px; }

    .ad-grid {
        display: grid;
        grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);
        gap: 48px;
        align-items: start;
    }
    .ad-side { display: flex; flex-direction: column; gap: 36px; }

    .ad-section-head { display: flex; align-items: baseline; justify-content: space-between; gap: 12px; margin-bottom: 12px; }
    .ad-h2 { margin: 0; font-size: 15px; font-weight: 500; color: var(--ba-ink); letter-spacing: -0.005em; }
    .ad-count { font-size: 13px; color: var(--ba-muted); }

    .ad-row { grid-template-columns: 64px minmax(0, 1.4fr) minmax(0, 1fr) minmax(0, 1.3fr) 64px; padding-top: 13px; padding-bottom: 13px; }
    .ad-row.ba-row-head { padding-top: 8px; padding-bottom: 8px; }
    .ad-teacher { color: var(--ba-ink-2); }
    .ad-more { display: inline-block; margin-top: 12px; padding-left: 0; }

    .ad-meter { padding: 10px 0; border-bottom: 1px solid var(--ba-line); }
    .ad-meter:first-of-type { border-top: 1px solid var(--ba-line); }
    .ad-fill { background: var(--ba-ink-2); }
    .ad-note { margin: 0; padding: 12px 0; font-size: 13px; color: var(--ba-muted); border-top: 1px solid var(--ba-line); border-bottom: 1px solid var(--ba-line); }

    .ad-list { list-style: none; margin: 0; padding: 0; border-top: 1px solid var(--ba-line); }
    .ad-list > li { display: flex; align-items: baseline; justify-content: space-between; gap: 12px; padding: 11px 0; border-bottom: 1px solid var(--ba-line); }
    .ad-list > li.ad-note { border-top: 0; }
    .ad-list time { flex-shrink: 0; }

    .ad-links > li { padding: 0; }
    .ad-links a {
        display: flex; justify-content: space-between; align-items: center;
        width: 100%; padding: 11px 0;
        color: var(--ba-ink); text-decoration: none; font-size: 14px;
        transition: color 120ms ease;
    }
    .ad-links a::after { content: "\2192"; color: var(--ba-faint); transition: color 120ms ease, transform 160ms cubic-bezier(0.23, 1, 0.32, 1); }
    @media (hover: hover) and (pointer: fine) {
        .ad-links a:hover { color: var(--ba-ink); }
        .ad-links a:hover::after { color: var(--ba-ink); transform: translateX(2px); }
    }
    .ad-links a:focus-visible { outline: 2px solid var(--ba-ink); outline-offset: 2px; }

    @media (prefers-reduced-motion: reduce) {
        .ad-links a::after { transition: none; }
    }

    @media (max-width: 1100px) {
        .ad-grid { grid-template-columns: 1fr; gap: 40px; }
    }

    @media (max-width: 860px) {
        .ad-summary { grid-template-columns: 1fr 1fr; }
        .ad-summary .ba-stat:nth-child(3) { border-left: 0; }
        .ad-summary .ba-stat:nth-child(n+3) { border-top: 1px solid var(--ba-line); grid-column: auto; }
        .ad-row {
            grid-template-columns: 56px minmax(0, 1fr) auto;
            grid-template-areas: "time subj room" "time cls teacher";
            gap: 4px 12px;
        }
        .ad-row > :nth-child(1) { grid-area: time; }
        .ad-row > :nth-child(2) { grid-area: subj; }
        .ad-row > :nth-child(3) { grid-area: cls; }
        .ad-row > :nth-child(4) { grid-area: teacher; justify-self: end; }
        .ad-row > :nth-child(5) { grid-area: room; }
    }
</style>
@endsection
