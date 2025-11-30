@extends('layouts.app')

@section('content')
    <script>
        window.location.href = "{{ route('dashboard') }}";
    </script>
@endsection
