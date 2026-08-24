@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-7">
                    <h1 class="font-weight-bold text-danger">
                        <i class="fas fa-clipboard-check mr-2"></i> Approve: {{ $exam->name }}
                    </h1>
                    <h6 class="text-muted font-weight-normal">
                        {{ $classSection->schoolClass->name ?? '' }} - {{ $classSection->section->name ?? '' }}
                        · {{ $learners->count() }} learner(s) with pending entries
                    </h6>
                </div>
                <div class="col-sm-5 text-right">
                    <a href="{{ route('marks-approval.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> All Batches
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <form action="{{ route('marks-approval.approve') }}" method="POST" id="batch-form">
            @csrf
            <input type="hidden" name="exam_id" value="{{ $exam->exam_id }}">
            <input type="hidden" name="class_section_id" value="{{ $classSection->class_section_id }}">

            <div class="card card-outline card-danger elevation-2 border-0">
                <div class="card-header bg-white d-flex align-items-center">
                    <h3 class="card-title font-weight-bold">Learners Awaiting Approval</h3>
                    <div class="card-tools ml-auto">
                        <button type="button" class="btn btn-outline-primary btn-sm mr-2" id="select-all">Select All Learners</button>
                        <button type="submit" class="btn btn-success px-4 elevation-1"
                                onclick="return confirm('Approve all marks for the selected learners?')">
                            <i class="fas fa-check mr-1"></i> Approve Selected
                        </button>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                            <tr>
                                <th style="width: 40px" class="text-center"></th>
                                <th style="width: 130px">Admission No</th>
                                <th>Learner</th>
                                <th style="width: 70px" class="text-center">Sex</th>
                                <th>Pending Entries</th>
                                <th style="width: 110px" class="text-center">Select</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($learners as $learner)
                                @php $student = $learner->student; @endphp
                                <tr class="learner-row">
                                    <td></td>
                                    <td><span class="badge badge-light border">{{ $student->admission_no ?? '—' }}</span></td>
                                    <td>
                                        <span class="font-weight-bold text-dark">{{ $student->full_name }}</span>
                                        @if($student->nemis_number || $student->upi_number)
                                            <br><small class="text-muted">UPI: {{ $student->upi_number ?: $student->nemis_number }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-pill {{ strtolower($learner->girls_boys) === 'f' ? 'badge-info' : 'badge-primary' }}">
                                            {{ $learner->girls_boys }}
                                        </span>
                                    </td>
                                    <td>
                                        @foreach($learner->entries as $entry)
                                            <span class="badge badge-light border mr-1 mb-1 font-weight-normal"
                                                  title="Entered {{ $entry->created_at?->format('d/m/Y H:i') }} by {{ $entry->createdBy?->staff_id ? 'Staff #' . $entry->createdBy->staff_id : 'System' }}">
                                                {{ $entry->subject?->name ?? 'Subject #' . $entry->subject_id }}:
                                                <b>{{ $entry->marks_obtained }}</b>
                                            </span>
                                        @endforeach
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" name="student_ids[]" value="{{ $student->student_id }}"
                                               class="learner-checkbox" data-name="{{ $student->full_name }}">
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fas fa-thumbs-up fa-2x text-success d-block mb-2"></i>
                                        Every entry in this batch is already approved.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-white text-right">
                    <button type="submit" class="btn btn-success px-4"
                            onclick="return confirm('Approve all marks for the selected learners?')">
                        <i class="fas fa-check mr-1"></i> Approve Selected Learners
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('select-all').addEventListener('click', function() {
            const boxes = document.querySelectorAll('.learner-checkbox');
            const allChecked = Array.from(boxes).every(c => c.checked);
            boxes.forEach(c => c.checked = !allChecked);
            this.textContent = allChecked ? 'Select All Learners' : 'Deselect All';
        });
    </script>
@endsection
