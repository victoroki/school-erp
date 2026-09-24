<!-- Student Id Field -->
<div class="col-sm-12">
    {!! Form::label('student_id', 'Student:') !!}
    <p>{{ $studentClassEnrollment->student->full_name ?? 'N/A' }}{{ isset($studentClassEnrollment->student->admission_no) ? ' ('.$studentClassEnrollment->student->admission_no.')' : '' }}</p>
</div>

<!-- Class Section Id Field -->
<div class="col-sm-12">
    {!! Form::label('class_section_id', 'Class / Stream:') !!}
    <p>{{ optional($studentClassEnrollment->classSection->schoolClass)->name ?? 'N/A' }}{{ optional($studentClassEnrollment->classSection->section)->name ? ' — '.$studentClassEnrollment->classSection->section->name : '' }}</p>
</div>

<!-- Roll Number Field -->
<div class="col-sm-12">
    {!! Form::label('roll_number', 'Roll Number:') !!}
    <p>{{ $studentClassEnrollment->roll_number }}</p>
</div>

<!-- Academic Year Id Field -->
<div class="col-sm-12">
    {!! Form::label('academic_year_id', 'Academic Year:') !!}
    <p>{{ $studentClassEnrollment->academicYear->name ?? 'N/A' }}</p>
</div>

<!-- Enrollment Date Field -->
<div class="col-sm-12">
    {!! Form::label('enrollment_date', 'Enrollment Date:') !!}
    <p>{{ $studentClassEnrollment->enrollment_date ? \Carbon\Carbon::parse($studentClassEnrollment->enrollment_date)->format('d/m/Y') : '—' }}</p>
</div>

<!-- Status Field -->
<div class="col-sm-12">
    {!! Form::label('status', 'Status:') !!}
    <p>{{ ucfirst($studentClassEnrollment->status) }}</p>
</div>

