{{--
    Override of the infyomlabs <x-laravel-ui-adminlte::adminlte-layout>
    shell, still used by the parent/student portal and the password screens.

    The package version loads @vite('resources/js/app.js'), an entry that no
    longer exists: jQuery, Bootstrap and AdminLTE are served as classic
    scripts from build/libs so inline page scripts can use them (see
    vite.config.js and layouts/app.blade.php). This keeps those pages on the
    same assets and theme as the staff app.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>

    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/css/all.min.css"
          integrity="sha512-1PKOgIY59xJ8Co8+NE6FZ+LOAZKjy+KY8iq0G4B3CyeY6wYHN3yt9PW0XpSriVlkMXe40PTKnXrLnZ9+fkDaog=="
          crossorigin="anonymous">

    @vite('resources/sass/app.scss')
    <link rel="stylesheet" href="{{ asset('css/app-theme.css') }}?v={{ filemtime(public_path('css/app-theme.css')) }}">
    @stack('third_party_stylesheets')
    @stack('page_css')

    <script src="{{ asset('build/libs/jquery.min.js') }}?v=3.7.1"></script>
</head>

{{ $slot }}

<script src="{{ asset('build/libs/bootstrap.bundle.min.js') }}?v=4.6.2"></script>
<script src="{{ asset('build/libs/adminlte.min.js') }}?v=3.1.0"></script>
@stack('third_party_scripts')
@stack('page_scripts')

</html>
