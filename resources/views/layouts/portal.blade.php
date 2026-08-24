<x-laravel-ui-adminlte::adminlte-layout>

    <body class="hold-transition sidebar-mini">
        <div class="wrapper">
            <!-- Main Header -->
            <nav class="main-header navbar navbar-expand navbar-white navbar-light">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                            <i class="fas fa-bars"></i>
                        </a>
                    </li>
                    <li class="nav-item d-none d-sm-inline-block">
                        <a href="{{ route('portal.profile') }}" class="nav-link">My Profile</a>
                    </li>
                    <li class="nav-item d-none d-sm-inline-block">
                        <a href="{{ route('portal.fees') }}" class="nav-link">Fees</a>
                    </li>
                    <li class="nav-item d-none d-sm-inline-block">
                        <a href="{{ route('portal.report-cards') }}" class="nav-link">Report Cards</a>
                    </li>
                    <li class="nav-item d-none d-sm-inline-block">
                        <a href="{{ route('portal.attendance') }}" class="nav-link">Attendance</a>
                    </li>
                </ul>

                <ul class="navbar-nav ml-auto">
                    <li class="nav-item dropdown user-menu">
                        <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                            <img src="{{ asset('garikon-black.png') }}"
                                class="user-image img-circle elevation-2" alt="User">
                            @auth
                            <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                            @endauth
                        </a>
                        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                            <li class="user-header bg-primary">
                                <img src="{{ asset('garikon-white.png') }}"
                                    class="img-circle elevation-2" alt="User">
                                <p>
                                    @auth
                                    {{ Auth::user()->name }}
                                    <small>Member since {{ Auth::user()->created_at->format('M. Y') }}</small>
                                    @endauth
                                </p>
                            </li>
                            <li class="user-footer">
                                <a href="{{ route('portal.profile') }}" class="btn btn-default btn-flat">Profile</a>
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

            <!-- No admin sidebar for portal — keep sidebar-mini for AdminLTE styling -->
            <aside class="main-sidebar elevation-2" style="display:none;"></aside>

            <!-- Content Wrapper -->
            <div class="content-wrapper">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible" style="margin:15px;">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible" style="margin:15px;">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>

            <!-- Main Footer -->
            <footer class="main-footer">
                <span class="footer-copy">&copy; 2025&ndash;2027 <a href="#">{{ config('app.name') }}</a>. All rights reserved.</span>
                <span class="footer-version"><b>Version</b> 1.0.0</span>
            </footer>
        </div>

        @push('page_scripts')
        <script>
            function initializeAdminLTE() {
                if (typeof window.jQuery !== 'undefined' && typeof window.jQuery.fn.PushMenu !== 'undefined') {
                    window.jQuery('[data-widget="pushmenu"]').PushMenu();
                } else {
                    setTimeout(initializeAdminLTE, 50);
                }
            }
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(initializeAdminLTE, 100);
            });
        </script>
        @endpush
    </body>
</x-laravel-ui-adminlte::adminlte-layout>
