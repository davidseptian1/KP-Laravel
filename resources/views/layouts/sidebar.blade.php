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

                @if (auth()->check() && auth()->user()->jabatan=='Admin')

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

                <!-- Reimburse (Admin) -->
                <li class="pc-item {{ $menuAdminReimburse ?? '' }}">
                    <a href="{{ route('admin.reimburse.index') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-receipt"></i></span>
                        <span class="pc-mtext">Reimburse Monitoring</span>
                    </a>
                </li>

                <!-- Reimburse Form (Admin) -->
                <li class="pc-item {{ $menuAdminReimburseForm ?? '' }}">
                    <a href="{{ route('admin.reimburse.forms') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-link"></i></span>
                        <span class="pc-mtext">Reimburse Form</span>
                    </a>
                </li>

                <!-- Pengajuan Data Monitoring (Admin) -->
                <li class="pc-item {{ $menuAdminDataRequest ?? '' }}">
                    <a href="{{ route('admin.data-request.index') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-file-text"></i></span>
                        <span class="pc-mtext">Monitoring<br>Pengajuan Data</span>
                    </a>
                </li>

                <!-- Pengajuan Data Form (Admin) -->
                <li class="pc-item {{ $menuAdminDataRequestForm ?? '' }}">
                    <a href="{{ route('admin.data-request.forms') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-link"></i></span>
                        <span class="pc-mtext">Form Pengajuan Data</span>
                    </a>
                </li>

                <!-- Peminjaman Barang Monitoring (Admin) -->
                <li class="pc-item {{ $menuAdminLoanRequest ?? '' }}">
                    <a href="{{ route('admin.loan-request.index') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-file-text"></i></span>
                        <span class="pc-mtext">Monitoring<br>Peminjaman Barang</span>
                    </a>
                </li>

                <!-- Peminjaman Barang Form (Admin) -->
                <li class="pc-item {{ $menuAdminLoanRequestForm ?? '' }}">
                    <a href="{{ route('admin.loan-request.forms') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-link"></i></span>
                        <span class="pc-mtext">Form Peminjaman<br>Barang</span>
                    </a>
                </li>

                <!-- Deposit Form (Admin) -->
                <li class="pc-item {{ $menuAdminDepositForm ?? '' }}">
                    <a href="{{ route('admin.deposit.forms') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-clipboard"></i></span>
                        <span class="pc-mtext">Form Deposit</span>
                    </a>
                </li>

                <!-- Deposit Monitoring (Admin) -->
                <li class="pc-item {{ $menuAdminDepositMonitoring ?? '' }}">
                    <a href="{{ route('admin.deposit.monitoring') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-list-check"></i></span>
                        <span class="pc-mtext">Monitoring Deposit</span>
                    </a>
                </li>

                @endif

                @if (auth()->check() && (auth()->user()->jabatan=='Admin' || auth()->user()->jabatan=='HRD'))

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

                <!-- Analisis Deposit -->
                <li class="pc-item {{ $menuDepositAnalysis ?? '' }}">
                    <a href="{{ route('admin.deposit.analysis') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-chart-bar"></i></span>
                        <span class="pc-mtext">Analisis Deposit</span>
                    </a>
                </li>

                @endif

                @if (auth()->check() && auth()->user()->jabatan=='Admin')

                <!-- Analisis Transaksi -->
                <li class="pc-item {{ $menuTransaksi ?? '' }}">
                    <a href="{{ route('transaksi.analisis') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-chart-bar"></i></span>
                        <span class="pc-mtext">Analisis Transaksi</span>
                    </a>
                </li>

                @endif


                @if (auth()->check() && auth()->user()->jabatan=='Admin')

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

                <!-- Supplier Management -->
                <li class="pc-item {{ $menuAdminSupplier ?? '' }}">
                    <a href="{{ route('admin.supplier.index') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-building-store"></i></span>
                        <span class="pc-mtext">Supplier Manajemen</span>
                    </a>
                </li>

                <!-- Server Management -->
                <li class="pc-item {{ $menuAdminServer ?? '' }}">
                    <a href="{{ route('admin.server.index') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-server"></i></span>
                        <span class="pc-mtext">Server Manajemen</span>
                    </a>
                </li>

                @endif

                @if (auth()->check() && auth()->user()->jabatan=='Staff')

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

                <!-- Reimburse (Staff) -->
                <li class="pc-item {{ $menuReimburse ?? '' }}">
                    <a href="{{ route('reimburse.index') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-receipt"></i></span>
                        <span class="pc-mtext">Reimburse</span>
                    </a>
                </li>

                <!-- Request Deposit (Staff) -->
                <li class="pc-item {{ $menuDepositRequest ?? '' }}">
                    <a href="{{ route('deposit.request.index') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-wallet"></i></span>
                        <span class="pc-mtext">Request Deposit</span>
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