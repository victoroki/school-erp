{{-- Student avatar: shows the photo when one is on file, otherwise a colored
     circle with the student's initials (no external service, no broken image).

     Usage:
       @include('students._avatar', ['student' => $student])
       @include('students._avatar', ['student' => $student, 'size' => 38])
     The photo (when present) fills the same circle so both states align.
--}}
@php
    $px = $size ?? 38;
    $hasPhoto = $student->has_photo && $student->avatar_url;
@endphp
<div class="rounded-circle overflow-hidden border shadow-sm flex-shrink-0 d-inline-flex align-items-center justify-content-center"
     style="width: {{ $px }}px; height: {{ $px }}px; {{ $hasPhoto ? '' : 'background-color: ' . $student->avatar_color . ';' }}">
    @if($hasPhoto)
        <img src="{{ $student->avatar_url }}" alt="{{ $student->full_name }}" style="width: 100%; height: 100%; object-fit: cover;">
    @else
        <span class="font-weight-bold text-white" style="font-size: {{ round($px * 0.42) }}px;">{{ $student->initials }}</span>
    @endif
</div>
