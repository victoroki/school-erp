<x-laravel-ui-adminlte::adminlte-layout>
@push('page_css')
    <link rel="stylesheet" href="{{ asset('css/sidebar-fixed-final.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
@endpush

    <body class="hold-transition sidebar-mini layout-fixed">
        <div class="wrapper">
            <!-- Main Header -->
            <nav class="main-header navbar navbar-expand navbar-white navbar-light">
                <!-- Left navbar links -->
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                            <i class="fas fa-bars"></i>
                        </a>
                    </li>
                </ul>

                <ul class="navbar-nav ml-auto">
                    <li class="nav-item dropdown user-menu">
                        <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                            <img src="{{ asset('garikon-black.png') }}"
                                class="user-image img-circle elevation-2" alt="Garikon User">
                            @auth
                            <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                            @endauth
                        </a>
                        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                            <!-- User image -->
                            <li class="user-header bg-primary">
                                <img src="{{ asset('garikon-white.png') }}"
                                    class="img-circle elevation-2" alt="Garikon User">
                                <p>
                                    @auth
                                    {{ Auth::user()->name }}
                                    <small>Member since {{ Auth::user()->created_at->format('M. Y') }}</small>
                                    @endauth
                                </p>
                            </li>
                            <!-- Menu Footer-->
                            <li class="user-footer">
                                <a href="#" class="btn btn-default btn-flat">Profile</a>
                                <a href="#" class="btn btn-default btn-flat float-right"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    Sign out
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </nav>

            <!-- Left side column. contains the logo and sidebar -->
            @include('layouts.sidebar')

            <!-- Content Wrapper. Contains page content -->
            <div class="content-wrapper">
                @yield('content')
            </div>

            <!-- Main Footer -->
            <footer class="main-footer">
                <div class="float-right d-none d-sm-block">
                    <b>Version</b> 1.0.0
                </div>
                <strong>Copyright &copy; 2025-2027 <a href="#">{{ config('app.name') }}</a>.</strong> All rights
                reserved.
            </footer>
        </div>

        <!-- AdminLTE and Sidebar JavaScript - MUST be loaded after jQuery -->
        @push('page_scripts')
        {{-- Load Select2 JS with defer so it waits for jQuery in app.js --}}
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js" defer></script>

        {{-- AdminLTE initialization with polling strategy to ensure jQuery is ready --}}
        <script>
            // Wait for jQuery and AdminLTE to be fully loaded before initializing widgets
            function initializeAdminLTE() {
                if (typeof window.jQuery !== 'undefined' && typeof window.jQuery.fn.PushMenu !== 'undefined') {
                    // Initialize AdminLTE widgets including pushmenu (sidebar collapse)
                    window.jQuery('[data-widget="pushmenu"]').PushMenu();
                    window.jQuery('[data-widget="treeview"]').Treeview();
                } else {
                    // Poll every 50ms until dependencies are ready
                    setTimeout(initializeAdminLTE, 50);
                }
            }

            // Start initialization after DOM is ready
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(initializeAdminLTE, 100);
            });
        </script>
        @endpush
    </body>
</x-laravel-ui-adminlte::adminlte-layout>