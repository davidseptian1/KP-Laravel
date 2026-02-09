<!-- [ Sidebar Menu ] start -->
<nav class="pc-sidebar">
    <div class="navbar-wrapper">
        <div class="m-header">
            <a href="{{ route('dashboard') }}" class="b-brand text-primary">
                <!-- ========   Logo   ============ -->
                <div class="logo-icon">
                    <i class="ti ti-activity"></i>
                </div>
                <span class="b-title">e-SMT</span>
            </a>
        </div>
        <div class="navbar-content">
            <ul class="pc-navbar">

                <!-- Dashboard -->
                <li class="pc-item {{ $menuDashboard ?? '' }}">
                    <a href="{{ route('dashboard') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-dashboard"></i></span>
                        <span class="pc-mtext">Dashboard</span>
                    </a>
                </li>

                @if (auth()->user()->jabatan=='Admin')

                <li class="pc-item pc-caption">
                    <label>Data Management</label>
                </li>

                <!-- Data Minusan -->
                <li class="pc-item {{ $menuAdminMinusan ?? '' }}">
                    <a href="{{ route('minusan') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-database"></i></span>
                        <span class="pc-mtext">Data Minusan</span>
                    </a>
                </li>

                @endif

                @if (auth()->user()->jabatan=='Admin' || auth()->user()->jabatan=='HRD')

                <li class="pc-item pc-caption">
                    <label>Reports</label>
                </li>

                <!-- Rekap Bulanan -->
                <li class="pc-item {{ $menuAdminRekap ?? '' }}">
                    <a href="{{ route('admin.rekap.index') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-calendar"></i></span>
                        <span class="pc-mtext">Rekap Bulanan</span>
                    </a>
                </li>

                <!-- Rekap Tilangan -->
                <li class="pc-item {{ $menuAdminTilangan ?? '' }}">
                    <a href="{{ route('admin.rekap.tilangan') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-file-text"></i></span>
                        <span class="pc-mtext">Rekap Tilangan</span>
                    </a>
                </li>

                <!-- ⭐ Report Khusus -->
                <li class="pc-item {{ $menuAdminReportKhusus ?? '' }}">
                    <a href="{{ route('admin.report.khusus.index') }}" class="pc-link">
                        <span class="pc-micon">
                            <i class="ti ti-alert-circle"></i>
                        </span>
                        <span class="pc-mtext">Report Khusus</span>
                    </a>
                </li>

                @endif

                @if (auth()->user()->jabatan=='Admin')

                <!-- Analisis Transaksi -->
                <li class="pc-item {{ $menuTransaksi ?? '' }}">
                    <a href="{{ route('transaksi.analisis') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-chart-bar"></i></span>
                        <span class="pc-mtext">Analisis Transaksi</span>
                    </a>
                </li>

                @endif


                @if (auth()->user()->jabatan=='Admin')

                <li class="pc-item pc-caption">
                    <label>System</label>
                </li>

                <!-- User Management -->
                <li class="pc-item {{ $menuAdminUser ?? '' }}">
                    <a href="{{ route('user') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-users"></i></span>
                        <span class="pc-mtext">User Management</span>
                    </a>
                </li>

                @endif

                @if (auth()->user()->jabatan=='Staff')

                <li class="pc-item pc-caption">
                    <label>Data</label>
                </li>

                <!-- Data Minusan (Staff) -->
                <li class="pc-item {{ $menuAdminMinusan ?? '' }}">
                    <a href="{{ route('minusan') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-database"></i></span>
                        <span class="pc-mtext">Data Minusan</span>
                    </a>
                </li>

                @endif

            </ul>

            <!-- Company Info Card -->
            <div class="card pc-user-card">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="ti ti-building text-primary" style="font-size: 40px;"></i>
                    </div>
                    <h6 class="mb-1">PT. CHIKA MULYA</h6>
                    <p class="text-muted text-sm mb-0">MULTIMEDIA</p>
                </div>
            </div>
        </div>
    </div>
</nav>
<!-- [ Sidebar Menu ] end -->