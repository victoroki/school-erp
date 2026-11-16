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
{{-- One calm tint for every initials avatar: a random colour per student
     read as noise in a long list. --}}
<div class="rounded-circle overflow-hidden flex-shrink-0 d-inline-flex align-items-center justify-content-center"
     style="width: {{ $px }}px; height: {{ $px }}px; {{ $hasPhoto ? '' : 'background-color: #eef3fa;' }}">
    @if($hasPhoto)
        <img src="{{ $student->avatar_url }}" alt="{{ $student->full_name }}" style="width: 100%; height: 100%; object-fit: cover;">
    @else
        <span class="font-weight-bold" style="color: #1d3f7a; font-size: {{ round($px * 0.38) }}px;">{{ $student->initials }}</span>
    @endif
</div>
