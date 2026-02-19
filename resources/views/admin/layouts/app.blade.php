<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel')</title>

    <!-- Local AdminLTE CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/adminlte/css/adminlte.min.css') }}">
    <!-- Local SweetAlert2 CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/sweetalert2/sweetalert2.min.css') }}">
    <!-- FontAwesome bundled with AdminLTE -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/adminlte/plugins/fontawesome-free/css/all.min.css') }}">

    <style>
        .card {
            border-radius: 12px !important;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05) !important;
        }
        .card-body {
            padding: 16px !important;
        }
        .small-box {
            border-radius: 12px !important;
            overflow: hidden;
        }
        .small-box-footer {
            border-bottom-left-radius: 12px;
            border-bottom-right-radius: 12px;
        }
    </style>
    @stack('styles')
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    @include('admin.layouts.partials.navbar')
    @include('admin.layouts.partials.sidebar')

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Content Header -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-3">
                    <div class="col-12 text-left">
                        <h1 class="m-0 font-weight-bold">@yield('page-title', 'Dashboard')</h1>
                        <nav aria-label="breadcrumb" class="mt-2">
                            <ol class="breadcrumb" style="background: transparent; padding: 0; margin: 0;">
                                @yield('breadcrumbs')
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="content">
            <div class="container-fluid">
                @yield('content')
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
        <strong>Admin Panel</strong> &copy; {{ date('Y') }}
        <div class="float-right d-none d-sm-inline-block">
            <b>Version</b> 1.0
        </div>
    </footer>

</div>

<!-- Local AdminLTE plugins: jQuery -->
<script src="{{ asset('assets/vendor/adminlte/plugins/jquery/jquery.min.js') }}"></script>
<!-- Bootstrap 4 bundled with AdminLTE -->
<script src="{{ asset('assets/vendor/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<!-- AdminLTE App JS -->
<script src="{{ asset('assets/vendor/adminlte/js/adminlte.min.js') }}"></script>
<!-- SweetAlert2 -->
<script src="{{ asset('assets/vendor/sweetalert2/sweetalert2.min.js') }}"></script>

<!-- Global Flash Message Handler -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if(session('success'))
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: '{{ session('success') }}',
                showConfirmButton: false,
                timer: 3500,
                timerProgressBar: true,
            });
        @endif

        @if(session('error'))
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: '{{ session('error') }}',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
            });
        @endif
    });
</script>

@stack('scripts')

</body>
</html>
