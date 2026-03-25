<style>
    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px; }
    .card-detail {
        background: #fff; border-radius: 16px; padding: 24px; border: none;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); height: 100%;
    }
    .card-header-icon {
        width: 40px; height: 40px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center;
        margin-right: 12px; color: white; margin-bottom: 20px;
    }
    .card-header-icon.blue { background: #0369a1; }
    .card-header-icon.green { background: #15803d; }
    .card-title-text { font-size: 1.1rem; font-weight: 700; color: var(--text-color); margin: 0; display: inline-block; vertical-align: super; }
    
    .detail-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px; }
    .detail-item { display: flex; flex-direction: column; }
    .detail-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; color: var(--muted-color); margin-bottom: 4px; }
    .detail-value { font-size: 0.95rem; font-weight: 600; color: var(--text-color); }

    .snapshot-card {            
        background: #fff; border-radius: 16px; padding: 24px; border: none;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); position: relative; overflow: hidden;
    }
    .snapshot-title { font-size: 1.1rem; font-weight: 700; color: #0369a1; margin-bottom: 12px; }
    .snapshot-text { color: var(--text-color); font-size: 0.95rem; line-height: 1.6; max-width: 70%; margin-bottom: 16px; }
    .snapshot-badge { display: inline-flex; align-items: center; padding: 6px 14px; background: #fff; border: 1px solid #e2e8f0; border-radius: 50px; font-weight: 700; font-size: 0.85rem; color: var(--text-color); margin-right: 10px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .snapshot-icon-bg {
        position: absolute; right: 24px; top: 50%; transform: translateY(-50%);
        width: 180px; height: 100px; background: #e0f2fe; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
    }
    .snapshot-icon-bg i { font-size: 40px; color: #7dd3fc; }

    @media (max-width: 991px) {
        .info-grid { grid-template-columns: 1fr; }
        .snapshot-text { max-width: 100%; }
        .snapshot-icon-bg { display: none; }
    }
</style>

<div class="info-grid">
    <!-- Personal Information -->
    <div class="card-detail">
        <div>
            <div class="card-header-icon blue"><i class="fas fa-id-card-alt"></i></div>
            <h3 class="card-title-text">Personal Information</h3>
        </div>
        <div class="detail-row">
            <div class="detail-item">
                <span class="detail-label">FULL NAME</span>
                <span class="detail-value">{{ $student->full_name }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">DATE OF BIRTH</span>
                <span class="detail-value">{{ $student->kenyan_dob }}</span>
            </div>
        </div>
        <div class="detail-row">
            <div class="detail-item">
                <span class="detail-label">GENDER</span>
                <span class="detail-value text-capitalize">{{ $student->gender }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">BLOOD GROUP</span>
                <span class="detail-value">{{ $student->blood_group ?? 'Unknown' }}</span>
            </div>
        </div>
        <div class="detail-row">
            <div class="detail-item" style="grid-column: span 2;">
                <span class="detail-label">NATIONALITY</span>
                <span class="detail-value">{{ $student->nationality ?? 'Kenyan' }}</span>
            </div>
        </div>
    </div>

    <!-- Contact Information -->
    <div class="card-detail">
        <div>
            <div class="card-header-icon green"><i class="fas fa-at"></i></div>
            <h3 class="card-title-text">Contact Information</h3>
        </div>
        <div class="detail-row">
            <div class="detail-item">
                <span class="detail-label">PHONE NUMBER</span>
                <span class="detail-value">{{ $student->formatted_phone ?: 'N/A' }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">CITY</span>
                <span class="detail-value">{{ $student->city ?? 'N/A' }}</span>
            </div>
        </div>
        <div class="detail-row">
            <div class="detail-item">
                <span class="detail-label">COUNTRY</span>
                <span class="detail-value">{{ $student->country ?? 'Kenya' }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">POSTAL ADDRESS</span>
                <span class="detail-value">{{ $student->address ?? 'N/A' }}{{ $student->postal_code ? ' - ' . $student->postal_code : '' }}</span>
            </div>
        </div>
        <div class="detail-row">
            <div class="detail-item" style="grid-column: span 2;">
                <span class="detail-label">GUARDIAN PRIMARY CONTACT</span>
                <span class="detail-value">
                    @if($student->parents && $student->parents->count() > 0)
                        {{ $student->parents->first()->email ?? $student->parents->first()->phone ?? $student->emergency_contact }}
                    @else
                        {{ $student->emergency_contact ?? 'N/A' }}
                    @endif
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Academic Results Snapshot -->
<div class="snapshot-card pb-2">
    <h3 class="snapshot-title"><i class="fas fa-chart-line mr-2"></i> Recent Academic Results</h3>
    @php
        $recentResults = \App\Models\ExamResult::with(['subject', 'exam', 'grade'])
            ->where('student_id', $student->student_id)
            ->orderBy('created_at', 'desc')
            ->take(4)
            ->get();
    @endphp
    
    @if($recentResults->count() > 0)
        <div class="table-responsive" style="position: relative; z-index: 10;">
            <table class="table table-sm table-borderless mb-0">
                <thead>
                    <tr>
                        <th class="text-muted" style="font-size:0.75rem; letter-spacing:0.5px;">EXAM</th>
                        <th class="text-muted" style="font-size:0.75rem; letter-spacing:0.5px;">SUBJECT</th>
                        <th class="text-muted" style="font-size:0.75rem; letter-spacing:0.5px;">SCORE</th>
                        <th class="text-muted" style="font-size:0.75rem; letter-spacing:0.5px;">GRADE</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentResults as $result)
                    <tr>
                        <td class="font-weight-bold align-middle">{{ $result->exam->name ?? 'N/A' }}</td>
                        <td class="align-middle">{{ $result->subject->name ?? 'N/A' }}</td>
                        <td class="align-middle font-weight-bold" style="color: #0369a1;">{{ number_format((float)$result->marks_obtained, 1) }}%</td>
                        <td class="align-middle">
                            @if($result->grade)
                                <span class="badge badge-light border">{{ $result->grade->grade_name }}</span>
                            @else
                                <span class="badge badge-light border">N/A</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3" style="position: relative; z-index: 10;">
            <a href="#" class="btn btn-sm btn-outline-primary rounded-pill px-4 font-weight-bold" onclick="document.getElementById('active_tab_input').value='academic'; $('.nav-tabs a[href=\'#academic\']').tab('show'); return false;" style="font-family: var(--font-main);">
                View Full Record
            </a>
        </div>
    @else
        <div class="text-center py-4 relative z-10" style="position: relative; z-index: 10;">
            <div style="background: #f1f5f9; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                <i class="fas fa-file-alt text-muted" style="font-size: 24px;"></i>
            </div>
            <h6 class="font-weight-bold text-dark">No Results Found</h6>
            <p class="text-muted mb-0" style="font-size: 0.95rem;">This student does not have any recorded exam results yet.</p>
        </div>
    @endif
    
    <div class="snapshot-icon-bg" style="opacity: 0.3; z-index: 0;">
        <i class="fas fa-graduation-cap"></i>
    </div>
</div>
