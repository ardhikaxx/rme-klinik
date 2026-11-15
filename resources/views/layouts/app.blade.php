<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - RME KLINIK Terpadu</title>

    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <!-- DataTables Bootstrap 5 CSS CDN -->
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Select2 & Bootstrap 5 theme CDN -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <!-- Custom Healthcare Design System CSS -->
    <link href="{{ asset('css/rme-custom.css') }}" rel="stylesheet">

    @stack('styles')
</head>
<body>
    <div id="wrapper" class="d-flex">
        {{-- Left Sidebar --}}
        @include('layouts.sidebar')

        {{-- Main Page Content Wrapper --}}
        <div id="page-content-wrapper" class="w-100">
            {{-- Topbar --}}
            @include('layouts.topbar')

            {{-- Main Content Container --}}
            <main class="container-fluid py-4 px-3 px-md-4 flex-grow-1">
                {{-- Breadcrumbs & Page Header --}}
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2">
                    <div>
                        <h4 class="fw-bold mb-1 text-dark">@yield('page_title', 'Halaman')</h4>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 small">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none" style="color: var(--rme-info);">Home</a></li>
                                @yield('breadcrumb')
                            </ol>
                        </nav>
                    </div>
                    <div>
                        @yield('page_actions')
                    </div>
                </div>

                {{-- Page Main Content --}}
                @yield('content')
            </main>

            {{-- Floating Footer --}}
            <footer class="floating-footer text-center text-md-start d-flex flex-wrap justify-content-between align-items-center text-muted small no-print">
                <div>
                    &copy; {{ date('Y') }} <strong>Klinik Pratama Sehat Mandiri</strong> &bull; RME Terpadu Berstandar Permenkes No. 24/2022
                </div>
                <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
                    <span class="badge bg-light text-secondary border">
                        <i class="fa-solid fa-server me-1 text-success"></i> MySQL Database
                    </span>
                    <span>Waktu: <strong class="text-dark">{{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WIB</strong></span>
                </div>
            </footer>
        </div>
    </div>

    <!-- jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap 5 Bundle JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS CDN -->
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <!-- Select2 JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom Application JS -->
    <script src="{{ asset('js/rme-custom.js') }}"></script>

    {{-- SweetAlert2 Flash Notifications --}}
    <script>
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: {!! json_encode(session('success')) !!},
                confirmButtonColor: '#2EACF0',
                timer: 3500
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan!',
                text: {!! json_encode(session('error')) !!},
                confirmButtonColor: '#dc3545'
            });
        @endif

        @if(session('warning'))
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian!',
                text: {!! json_encode(session('warning')) !!},
                confirmButtonColor: '#ffc107'
            });
        @endif

        @if($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal!',
                html: '<ul class="text-start mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>',
                confirmButtonColor: '#dc3545'
            });
        @endif
    </script>

    @stack('scripts')
</body>
</html>
