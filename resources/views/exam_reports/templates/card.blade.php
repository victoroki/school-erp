{{-- One learner's progress report card (A4). Used by the browser print page
     and by the bulk PDF generator — table-based so DomPDF renders it well. --}}
@php
    $school = $data['school'];
    $student = $data['student'];
    $exam = $data['exam'];
    $classSection = $data['classSection'];
    $isCbe = $data['is_cbe'];
    $className = trim(($classSection?->schoolClass?->name ?? '') . ' ' . ($classSection?->section?->name ?? '')) ?: '—';
@endphp

<div class="report-card">
    {{-- ── School header ── --}}
    <table class="w-100 header-table">
        <tr>
            <td class="text-center" style="border-bottom: 3px double #1a3c6e; padding-bottom: 8px;">
                <div class="school-name">{{ strtoupper($school->name ?? config('app.name', 'School')) }}</div>
                <div class="school-sub">
                    P.O. Box {{ $data['school_meta']['po_box'] }}, Kenya
                    @if($data['school_meta']['phone']) · Tel: {{ $data['school_meta']['phone'] }} @endif
                </div>
                @if($data['school_meta']['motto'])
                    <div class="school-motto">&ldquo;{{ $data['school_meta']['motto'] }}&rdquo;</div>
                @endif
                <div class="report-title">{{ strtoupper($exam->name) }} — LEARNER'S PROGRESS REPORT</div>
            </td>
        </tr>
    </table>

    {{-- ── Learner demographics ── --}}
    <table class="w-100 info-table">
        <tr>
            <td class="lbl">Learner's Name:</td>
            <td class="val"><b>{{ strtoupper($student->full_name) }}</b></td>
            <td class="lbl">Admission No:</td>
            <td class="val"><b>{{ $student->admission_no }}</b></td>
        </tr>
        <tr>
            <td class="lbl">UPI / NEMIS:</td>
            <td class="val">{{ $student->upi_number ?: ($student->nemis_number ?? '—') }}</td>
            <td class="lbl">Gender:</td>
            <td class="val">{{ ucfirst($student->gender ?? '—') }}</td>
        </tr>
        <tr>
            <td class="lbl">Date of Birth:</td>
            <td class="val">{{ $student->kenyan_dob }}</td>
            <td class="lbl">Class / Stream:</td>
            <td class="val">{{ $className }}</td>
        </tr>
        <tr>
            <td class="lbl">Academic Year:</td>
            <td class="val">{{ optional($classSection?->academicYear)->name ?? date('Y') }}</td>
            <td class="lbl">Position in Class:</td>
            <td class="val"><b>{{ $data['position'] ? $data['position'] . ' of ' . $data['class_size'] : '—' }}</b></td>
        </tr>
    </table>

    {{-- ── Results ── --}}
    <table class="w-100 results-table">
        <thead>
            <tr>
                <th style="width: 26px;">#</th>
                <th style="text-align: left;">Learning Area</th>
                <th style="width: 52px;">Marks</th>
                <th style="width: 46px;">Out of</th>
                <th style="width: 44px;">%</th>
                @if($isCbe)
                    <th style="width: 56px;">Level</th>
                    <th style="width: 40px;">Pts</th>
                @else
                    <th style="width: 56px;">Grade</th>
                @endif
                <th style="width: 150px;">Teacher's Remark</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['rows'] as $i => $row)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td><b>{{ $row['subject'] }}</b></td>
                    <td class="text-center">{{ number_format($row['marks'], $row['marks'] == round($row['marks']) ? 0 : 1) }}</td>
                    <td class="text-center">{{ number_format($row['max'], 0) }}</td>
                    <td class="text-center">{{ number_format($row['percent'], 1) }}</td>
                    @if($isCbe)
                        <td class="text-center level-cell" style="color: {{ $row['level']['color'] }};">
                            <b>{{ $row['level']['code'] }}</b>
                            <span class="level-desc d-block">{{ $row['level']['band'] }}</span>
                        </td>
                        <td class="text-center">{{ $row['level']['points'] }}</td>
                    @else
                        <td class="text-center"><b>{{ $row['grade'] ?? '—' }}</b></td>
                    @endif
                    <td class="remark-cell">{{ $row['remarks'] ?: ($row['level']['descriptor'] ?? '') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $isCbe ? 7 : 6 }}" class="text-center empty-row">
                        No marks recorded for this assessment yet.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ── Performance summary ── --}}
    <table class="w-100 summary-table">
        <tr>
            <td>
                <span class="sum-lbl">TOTAL MARKS</span>
                <span class="sum-val">{{ number_format($data['total'], 0) }} / {{ number_format($data['out_of'], 0) }}</span>
            </td>
            <td>
                <span class="sum-lbl">MEAN SCORE</span>
                <span class="sum-val">{{ number_format($data['mean_pct'], 1) }}%</span>
            </td>
            <td colspan="2">
                <span class="sum-lbl">{{ $isCbe ? 'OVERALL PERFORMANCE' : 'MEAN GRADE' }}</span>
                <span class="sum-val" style="color: {{ $isCbe ? $data['overall']['color'] : '#1a3c6e' }};">
                    @if($isCbe)
                        {{ $data['overall']['code'] }} — {{ $data['overall']['descriptor'] }}
                        <small>({{ $data['overall']['points'] }}/8 pts)</small>
                    @else
                        {{ $data['rows']->pluck('grade')->filter()->mode()[0] ?? '—' }}
                    @endif
                </span>
            </td>
        </tr>
        @if($data['attendance'] || $data['fee'])
            <tr>
                @if($data['attendance'])
                    <td colspan="2">
                        <span class="sum-lbl">ATTENDANCE</span>
                        <span class="sum-val">{{ $data['attendance']['present'] }} of {{ $data['attendance']['open'] }} days</span>
                    </td>
                @endif
                @if($data['fee'])
                    <td colspan="2">
                        <span class="sum-lbl">FEE BALANCE</span>
                        <span class="sum-val">KES {{ number_format($data['fee']['balance'], 0) }}</span>
                    </td>
                @endif
            </tr>
        @endif
    </table>

    {{-- ── CBE performance-level key ── --}}
    @if($isCbe)
        <div class="level-key">
            <b>Performance levels:</b>
            EE — Exceeding Expectation &nbsp;·&nbsp;
            ME — Meeting Expectation &nbsp;·&nbsp;
            AE — Approaching Expectation &nbsp;·&nbsp;
            BE — Below Expectation
        </div>
    @endif

    {{-- ── Remarks ── --}}
    <table class="w-100 remarks-table">
        <tr>
            <td class="rem-lbl">Class Teacher's Remarks:</td>
            <td class="rem-val">{{ $data['teacher_remark'] }}</td>
        </tr>
        <tr>
            <td class="rem-lbl">Principal's Remarks:</td>
            <td class="rem-val">{{ $data['principal_remark'] }}</td>
        </tr>
    </table>

    {{-- ── Signatures ── --}}
    <table class="w-100 sign-table">
        <tr>
            <td class="sign-line"></td>
            <td class="sign-line"></td>
            <td class="sign-line"></td>
        </tr>
        <tr class="sign-labels">
            <td>{{ $classSection?->classTeacher?->first_name ? $classSection->classTeacher->first_name . ' ' . $classSection->classTeacher->last_name : 'Class Teacher' }}<br><span>Class Teacher</span></td>
            <td><br><span>School Stamp</span></td>
            <td><br><span>Principal / Head Teacher</span></td>
        </tr>
    </table>

    <div class="card-footer-note">
        Generated on {{ $data['generated_at']->format('d/m/Y H:i') }} · This document is official only with the Principal's signature and school stamp.
    </div>
</div>
