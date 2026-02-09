@include('layouts/header')

<body data-pc-preset="preset-1" data-pc-direction="ltr" data-pc-theme="light">

    <!-- SVG Icon Definitions -->
    <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
        <symbol id="custom-clipboard" viewBox="0 0 24 24">
            <path fill="currentColor" d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm7 16H5V5h2v3h10V5h2v14z" />
        </symbol>
    </svg>

    <!-- [ Pre-loader ] start -->
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>
    <!-- [ Pre-loader ] End -->

    @include('layouts/sidebar')

    <!-- [ Header Topbar ] start -->
    <header class="pc-header">
        <div class="header-wrapper">
            <!-- [Mobile Media Block] start -->
            <div class="me-auto pc-mob-drp">
                <ul class="list-unstyled">
                    <!-- ======= Menu collapse Icon ===== -->
                    <li class="pc-h-item pc-sidebar-collapse">
                        <a href="#" class="pc-head-link ms-0" id="sidebar-hide">
                            <i class="ti ti-menu-2"></i>
                        </a>
                    </li>
                    <li class="pc-h-item pc-sidebar-popup">
                        <a href="#" class="pc-head-link ms-0" id="mobile-collapse">
                            <i class="ti ti-menu-2"></i>
                        </a>
                    </li>
                </ul>
            </div>
            <!-- [Mobile Media Block end] -->

            <div class="ms-auto">
                <ul class="list-unstyled">
                    <li class="dropdown pc-h-item header-user-profile">
                        <a class="pc-head-link dropdown-toggle arrow-none me-0" href="#" id="dropdownProfile"
                            role="button" aria-haspopup="true" aria-expanded="false">
                            <img src="{{ asset('sbadmin2/img/undraw_profile.svg') }}" alt="user-image" class="user-avtar">
                            <span>
                                <span class="user-name">{{ auth()->user()->nama }}</span>
                                <span class="user-desc">{{ auth()->user()->jabatan }}</span>
                            </span>
                        </a>
                        <div class="dropdown-menu dropdown-user-profile dropdown-menu-end" id="dropdownProfileMenu">
                            <div class="dropdown-header d-flex align-items-center justify-content-between">
                                <h5 class="m-0">Profile</h5>
                            </div>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="ti ti-power"></i>
                                <span>Logout</span>
                            </a>
                        </div>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>
    <!-- [ Header ] end -->

    <!-- [ Main Content ] start -->
    <div class="pc-container">
        <div class="pc-content">
            @yield('content')
        </div>
        <!-- /.pc-content -->
    </div>
    <!-- End of pc-container -->

    <!-- Footer -->
    <footer class="pc-footer">
        <div class="footer-wrapper container-fluid">
            <div class="row">
                <div class="col my-1">
                    <p class="m-0">Copyright &copy; {{ date('Y') }} e-Sistem Monitoring Transaksi - <a href="#" class="link-primary">PT. CHIKA MULYA MULTIMEDIA</a></p>
                </div>
            </div>
        </div>
    </footer>
    <!-- End of Footer -->

    <!-- Scroll to Top Button-->
    <a href="#" class="btn btn-icon btn-primary btn-to-top"><i class="ti ti-chevron-up"></i></a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logoutModalLabel">Konfirmasi Logout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Apakah Anda yakin ingin keluar dari sistem?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <a class="btn btn-primary" href="{{ route('logout') }}">Logout</a>
                </div>
            </div>
        </div>
    </div>


    {{-- ================= GLOBAL NOTIFICATION ================= --}}

    @if(session('login_success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Welcome 👋',
            text: @json(session('login_success')),
            confirmButtonText: 'Masuk Dashboard',
            confirmButtonColor: '#2563EB'
        });
    </script>
    @endif

    @if(session('error'))
    <script>
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: @json(session('error')),
            showConfirmButton: false,
            timer: 3500,
            timerProgressBar: true
        });
    </script>
    @endif

    @if(session('warning'))
    <script>
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'warning',
            title: @json(session('warning')),
            showConfirmButton: false,
            timer: 3500
        });
    </script>
    @endif

    @if(session('success'))
    <script>
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: @json(session('success')),
            showConfirmButton: false,
            timer: 3000
        });
    </script>
    @endif


    @include('layouts/footer')